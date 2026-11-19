<?php
/**
 * Responsive, brand-aware email layout for WordPress plain-text notifications.
 * Existing HTML emails (including WooCommerce/plugin templates) are not altered.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a safe brand color from the active Elementor kit, then theme settings.
 * The Elementor primary global color represents the site's active design system.
 *
 * @return string
 */
function hello_elementor_child_email_brand_color() {
	$kit_id = absint( get_option( 'elementor_active_kit' ) );
	if ( $kit_id ) {
		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		if ( is_array( $settings ) ) {
			foreach ( [ 'system_colors', 'custom_colors' ] as $key ) {
				if ( empty( $settings[ $key ] ) || ! is_array( $settings[ $key ] ) ) {
					continue;
				}
				foreach ( $settings[ $key ] as $color ) {
					if ( ! is_array( $color ) || empty( $color['color'] ) ) {
						continue;
					}
					if ( 'primary' === ( isset( $color['_id'] ) ? $color['_id'] : '' ) || 'primary' === ( isset( $color['title'] ) ? strtolower( (string) $color['title'] ) : '' ) ) {
						$hex = sanitize_hex_color( $color['color'] );
						if ( $hex ) {
							return $hex;
						}
					}
				}
			}
			if ( ! empty( $settings['system_colors'][0]['color'] ) ) {
				$hex = sanitize_hex_color( $settings['system_colors'][0]['color'] );
				if ( $hex ) {
					return $hex;
				}
			}
		}
	}
	foreach ( [ 'accent_color', 'primary_color' ] as $mod ) {
		$hex = sanitize_hex_color( get_theme_mod( $mod, '' ) );
		if ( $hex ) {
			return $hex;
		}
	}
	return '#1d1d1f';
}

/** Ensure readable text on any brand color (including light colors). */
function hello_elementor_child_email_brand_text_color( $color ) {
	$hex = ltrim( $color, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return '#ffffff';
	}
	$rgb = [ hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) ];
	$luminance = 0;
	foreach ( [ 0.2126, 0.7152, 0.0722 ] as $i => $weight ) {
		$v = $rgb[ $i ] / 255;
		$luminance += $weight * ( $v <= 0.04045 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 ) );
	}
	return $luminance > 0.179 ? '#171717' : '#ffffff';
}

/**
 * Change only URLs pointing to this site's default WordPress login screen.
 * Preserve reset keys, usernames, and query parameters for WP core notifications.
 */
function hello_elementor_child_email_rewrite_core_login_url( $url ) {
	$original = wp_parse_url( $url );
	$site     = wp_parse_url( site_url() );
	if ( empty( $original['host'] ) || empty( $site['host'] ) || strtolower( $original['host'] ) !== strtolower( $site['host'] ) ) {
		return $url;
	}
	if ( empty( $original['path'] ) || 'wp-login.php' !== basename( $original['path'] ) ) {
		return $url;
	}
	$query = [];
	if ( ! empty( $original['query'] ) ) {
		wp_parse_str( $original['query'], $query );
	}
	// Keep special login operations handled by WP core unchanged.
	if ( isset( $query['action'] ) && ! in_array( $query['action'], [ 'login', 'rp', 'resetpass', 'lostpassword' ], true ) ) {
		return $url;
	}
	if ( isset( $query['action'] ) && 'lostpassword' === $query['action'] ) {
		return hello_elementor_child_custom_lostpassword_url();
	}
	if ( isset( $query['action'] ) && 'login' === $query['action'] ) {
		unset( $query['action'] );
	}
	if ( isset( $query['action'] ) && 'resetpass' === $query['action'] ) {
		$query['action'] = 'rp';
	}
	return empty( $query ) ? hello_elementor_child_custom_login_url() : add_query_arg( $query, hello_elementor_child_custom_login_url() );
}

/** Convert a plain-text paragraph to safe email HTML with clickable links. */
function hello_elementor_child_email_paragraph_html( $paragraph, $brand ) {
	$pattern = '~<https?://[^>\r\n]+>|https?://[^\s<>]+~i';
	$parts = preg_split( '~(<https?://[^>\r\n]+>|https?://[^\s<>]+)~i', $paragraph, -1, PREG_SPLIT_DELIM_CAPTURE );
	$html = '';
	foreach ( $parts as $part ) {
		if ( preg_match( $pattern, $part ) && preg_match( '~^(?:<https?://[^>\r\n]+>|https?://[^\s<>]+)$~i', $part ) ) {
			$url = trim( $part, '<>' );
			$tail = '';
			if ( '>' !== substr( $part, -1 ) ) {
				$clean = rtrim( $url, '.,!;' );
				$tail = substr( $url, strlen( $clean ) );
				$url = $clean;
			}
			$url = hello_elementor_child_email_rewrite_core_login_url( $url );
			$html .= '<a href="' . esc_url( $url ) . '" style="color:' . esc_attr( $brand ) . ';text-decoration:underline;overflow-wrap:anywhere;">' . esc_html( $url ) . '</a>' . esc_html( $tail );
		} else {
			$html .= nl2br( esc_html( $part ), false );
		}
	}
	return $html;
}

/** Determine whether any declared Content-Type header is already HTML. */
function hello_elementor_child_email_is_html( $headers ) {
	if ( is_string( $headers ) ) {
		$headers = preg_split( '/\r?\n/', $headers );
	}
	if ( ! is_array( $headers ) ) {
		return false;
	}
	foreach ( $headers as $key => $value ) {
		$header = is_string( $key ) ? $key . ': ' . $value : (string) $value;
		if ( preg_match( '/^Content-Type\s*:\s*text\/html/i', trim( $header ) ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Build a complete, email-client-friendly HTML document with inline styles.
 * Content must be already escaped HTML from email_paragraph_html.
 */
function hello_elementor_child_email_layout( $subject, $content, $brand ) {
	$site_name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$site_url   = home_url( '/' );
	$title      = trim( preg_replace( '/^\[[^]]+\]\s*/u', '', wp_strip_all_tags( (string) $subject ) ) );
	if ( '' === $title ) {
		$title = __( 'Notification', 'hello-elementor-child' );
	}
	return '<!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
		. '<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#23252b;">'
		. '<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f5f7;"><tr><td align="center" style="padding:38px 14px;">'
		. '<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:600px;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">'
		. '<tr><td style="height:7px;line-height:7px;font-size:0;background-color:' . esc_attr( $brand ) . ';">&nbsp;</td></tr>'
		. '<tr><td style="padding:30px 38px 14px;"><a href="' . esc_url( $site_url ) . '" style="color:' . esc_attr( $brand ) . ';font-size:19px;font-weight:700;text-decoration:none;">' . esc_html( $site_name ) . '</a></td></tr>'
		. '<tr><td style="padding:10px 38px 0;"><h1 style="margin:0;color:#202127;font-size:25px;line-height:1.3;font-weight:700;">' . esc_html( $title ) . '</h1></td></tr>'
		. '<tr><td style="padding:18px 38px 28px;font-size:15px;line-height:1.75;color:#40434b;">' . $content . '</td></tr>'
		. '<tr><td style="padding:20px 38px;background-color:#fafafa;border-top:1px solid #eceef0;font-size:12px;line-height:1.7;color:#70737b;">'
		. esc_html( sprintf( __( 'This email was sent by %s.', 'hello-elementor-child' ), $site_name ) )
		. '<br><a style="color:' . esc_attr( $brand ) . ';text-decoration:none;" href="' . esc_url( $site_url ) . '">' . esc_html( $site_url ) . '</a>'
		. '</td></tr></table></td></tr></table></body></html>';
}

/**
 * Apply the template to plain-text wp_mail messages, which includes the core
 * welcome, password reset and account-change notifications. Already-HTML mail
 * from plugins is intentionally excluded. Filter can be disabled by returning false.
 */
function hello_elementor_child_format_system_email( $args ) {
	if ( ! apply_filters( 'hello_elementor_child_enable_email_template', true, $args ) ) {
		return $args;
	}
	if ( ! isset( $args['message'] ) || ! is_string( $args['message'] ) || '' === trim( $args['message'] ) ) {
		return $args;
	}
	$headers = isset( $args['headers'] ) ? $args['headers'] : [];
	if ( hello_elementor_child_email_is_html( $headers ) || preg_match( '/^\s*<(?:!doctype\s+html|html\b|table\b|div\b|p\b)/i', $args['message'] ) ) {
		return $args;
	}
	$brand      = hello_elementor_child_email_brand_color();
	$paragraphs = preg_split( '/\r?\n\s*\r?\n/', trim( $args['message'] ) );
	$content    = '';
	$reset_link = '';
	foreach ( $paragraphs as $paragraph ) {
		if ( '' === $reset_link && preg_match( '~(?:<)?(https?://[^\s<>]+)(?:>)?~i', $paragraph, $match ) ) {
			$candidate = hello_elementor_child_email_rewrite_core_login_url( html_entity_decode( rtrim( $match[1], '.,!;' ), ENT_QUOTES, 'UTF-8' ) );
			$query = wp_parse_url( $candidate, PHP_URL_QUERY );
			$params = [];
			if ( $query ) {
				wp_parse_str( $query, $params );
			}
			$site_host = wp_parse_url( site_url(), PHP_URL_HOST );
			$link_host = wp_parse_url( $candidate, PHP_URL_HOST );
			if ( $site_host && $link_host && strtolower( $site_host ) === strtolower( $link_host ) && isset( $params['key'], $params['login'] ) && isset( $params['action'] ) && 'rp' === $params['action'] ) {
				$reset_link = $candidate;
			}
		}

		$content .= '<p style="margin:0 0 17px;">' . hello_elementor_child_email_paragraph_html( $paragraph, $brand ) . '</p>';
	}
	if ( '' !== $reset_link ) {
		$content .= '<p style="margin:22px 0 18px;"><a href="' . esc_url( $reset_link ) . '" style="display:inline-block;padding:13px 24px;border-radius:8px;background-color:' . esc_attr( $brand ) . ';color:' . esc_attr( hello_elementor_child_email_brand_text_color( $brand ) ) . ';font-size:14px;font-weight:700;text-decoration:none;">' . esc_html__( 'Set your password', 'hello-elementor-child' ) . '</a></p>';
	}
	$args['message'] = hello_elementor_child_email_layout( isset( $args['subject'] ) ? $args['subject'] : '', $content, $brand );
	if ( is_string( $headers ) ) {
		$headers = preg_split( '/\r?\n/', trim( $headers ) );
	}
	if ( ! is_array( $headers ) ) {
		$headers = [];
	}
	$clean_headers = [];
	foreach ( $headers as $key => $value ) {
		$header = is_string( $key ) ? $key . ': ' . $value : (string) $value;
		if ( preg_match( '/^\s*Content-Type\s*:/i', $header ) ) {
			continue;
		}
		if ( '' !== trim( $header ) ) {
			$clean_headers[] = $header;
		}
	}
	$clean_headers[] = 'Content-Type: text/html; charset=UTF-8';
	$args['headers'] = $clean_headers;
	return $args;
}
add_filter( 'wp_mail', 'hello_elementor_child_format_system_email', 20 );
