<?php
/**
 * Server-side antispam validation for forms.
 *
 * Migrates the old no_url_field plugin features into the child theme:
 * - spam keyword blocking,
 * - URL blocking outside URL/link fields,
 * - suspicious URL fragment blocking,
 * - manual and automatic IP blocking.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check whether form security is enabled.
 *
 * @return bool
 */
function hello_elementor_child_form_security_enabled() {
	return '1' === (string) hello_elementor_child_get_option( 'form_security_enabled', '1' );
}

/**
 * Convert comma/newline separated text into clean terms.
 *
 * @param string $value Raw textarea content.
 *
 * @return array
 */
function hello_elementor_child_form_security_parse_terms( $value ) {
	$items = preg_split( '/[\r\n,]+/', (string) $value );
	$items = is_array( $items ) ? $items : [];
	$terms = [];

	foreach ( $items as $item ) {
		$item = trim( (string) $item );

		if ( '' === $item ) {
			continue;
		}

		$terms[ strtolower( $item ) ] = $item;
	}

	return array_values( $terms );
}

/**
 * Convert textarea content into IP lines.
 *
 * @param string $value Raw textarea content.
 *
 * @return array
 */
function hello_elementor_child_form_security_parse_ips( $value ) {
	$items = preg_split( '/[\r\n,]+/', (string) $value );
	$items = is_array( $items ) ? $items : [];
	$ips   = [];

	foreach ( $items as $item ) {
		$item = trim( preg_replace( '/\s+#.*$/', '', (string) $item ) );

		if ( '' === $item || 0 === strpos( $item, '#' ) ) {
			continue;
		}

		if ( hello_elementor_child_form_security_is_valid_ip_rule( $item ) ) {
			$ips[] = $item;
		}
	}

	return array_values( array_unique( $ips ) );
}

/**
 * Check if a value is a valid IP or CIDR rule.
 *
 * @param string $rule IP or CIDR value.
 *
 * @return bool
 */
function hello_elementor_child_form_security_is_valid_ip_rule( $rule ) {
	$rule = trim( (string) $rule );

	if ( filter_var( $rule, FILTER_VALIDATE_IP ) ) {
		return true;
	}

	if ( false === strpos( $rule, '/' ) ) {
		return false;
	}

	$parts = explode( '/', $rule, 2 );
	$ip    = trim( $parts[0] ?? '' );
	$bits  = trim( $parts[1] ?? '' );

	if ( '' === $bits || ! ctype_digit( $bits ) || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return false;
	}

	$max_bits = false !== strpos( $ip, ':' ) ? 128 : 32;
	$bits     = (int) $bits;

	return $bits >= 0 && $bits <= $max_bits;
}

/**
 * Check whether an IP matches a CIDR range.
 *
 * @param string $ip   Visitor IP.
 * @param string $cidr CIDR rule.
 *
 * @return bool
 */
function hello_elementor_child_form_security_ip_in_cidr( $ip, $cidr ) {
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) || false === strpos( $cidr, '/' ) ) {
		return false;
	}

	$parts  = explode( '/', $cidr, 2 );
	$subnet = trim( $parts[0] ?? '' );
	$bits   = trim( $parts[1] ?? '' );

	if ( ! hello_elementor_child_form_security_is_valid_ip_rule( $cidr ) ) {
		return false;
	}

	$ip_bin     = inet_pton( $ip );
	$subnet_bin = inet_pton( $subnet );

	if ( false === $ip_bin || false === $subnet_bin || strlen( $ip_bin ) !== strlen( $subnet_bin ) ) {
		return false;
	}

	$bits       = (int) $bits;
	$full_bytes = intdiv( $bits, 8 );
	$rem_bits   = $bits % 8;

	if ( $full_bytes > 0 && substr( $ip_bin, 0, $full_bytes ) !== substr( $subnet_bin, 0, $full_bytes ) ) {
		return false;
	}

	if ( 0 === $rem_bits ) {
		return true;
	}

	$mask = ( 0xff << ( 8 - $rem_bits ) ) & 0xff;

	return ( ord( $ip_bin[ $full_bytes ] ) & $mask ) === ( ord( $subnet_bin[ $full_bytes ] ) & $mask );
}

/**
 * Check whether a visitor IP matches one blocked IP/CIDR rule.
 *
 * @param string $ip   Visitor IP.
 * @param string $rule IP or CIDR rule.
 *
 * @return bool
 */
function hello_elementor_child_form_security_ip_matches_rule( $ip, $rule ) {
	$rule = trim( (string) $rule );

	if ( filter_var( $rule, FILTER_VALIDATE_IP ) ) {
		return $ip === $rule;
	}

	return hello_elementor_child_form_security_ip_in_cidr( $ip, $rule );
}

/**
 * Get the current visitor IP.
 *
 * @return string
 */
function hello_elementor_child_form_security_get_client_ip() {
	$server_keys = [
		'HTTP_CF_CONNECTING_IP',
		'HTTP_X_REAL_IP',
		'HTTP_X_FORWARDED_FOR',
		'REMOTE_ADDR',
	];

	foreach ( $server_keys as $key ) {
		if ( empty( $_SERVER[ $key ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			continue;
		}

		$value = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$parts = array_map( 'trim', explode( ',', $value ) );

		foreach ( $parts as $part ) {
			if ( filter_var( $part, FILTER_VALIDATE_IP ) ) {
				return $part;
			}
		}
	}

	return '';
}

/**
 * Check if a string contains another string using UTF-8 when available.
 *
 * @param string $haystack Text to search in.
 * @param string $needle   Text to find.
 *
 * @return bool
 */
function hello_elementor_child_form_security_contains( $haystack, $needle ) {
	$haystack = (string) $haystack;
	$needle   = (string) $needle;

	if ( '' === $needle ) {
		return false;
	}

	if ( function_exists( 'mb_stripos' ) ) {
		return false !== mb_stripos( $haystack, $needle, 0, 'UTF-8' );
	}

	return false !== stripos( $haystack, $needle );
}

/**
 * Flatten any submitted value into text for scanning.
 *
 * @param mixed $value Raw field value.
 *
 * @return string
 */
function hello_elementor_child_form_security_flatten_value( $value ) {
	if ( is_array( $value ) ) {
		$parts = array_map( 'hello_elementor_child_form_security_flatten_value', $value );

		return trim( implode( ' ', $parts ) );
	}

	if ( is_object( $value ) ) {
		return '';
	}

	return (string) $value;
}

/**
 * Normalize Elementor form fields into a common structure.
 *
 * @param object $record Elementor form record.
 *
 * @return array
 */
function hello_elementor_child_form_security_get_elementor_fields( $record ) {
	$fields      = [];
	$known_keys  = [];
	$raw_fields  = [];
	$formatted   = [];

	if ( is_object( $record ) && method_exists( $record, 'get' ) ) {
		$raw_fields = $record->get( 'fields' );
	}

	if ( is_object( $record ) && method_exists( $record, 'get_formatted_data' ) ) {
		$formatted = $record->get_formatted_data();
	}

	if ( is_array( $raw_fields ) ) {
		foreach ( $raw_fields as $field_id => $field ) {
			$field      = is_array( $field ) ? $field : [];
			$field_id   = (string) $field_id;
			$type       = isset( $field['type'] ) ? (string) $field['type'] : '';
			$title      = isset( $field['title'] ) ? (string) $field['title'] : $field_id;
			$value      = array_key_exists( 'value', $field ) ? $field['value'] : '';
			$raw_value  = hello_elementor_child_form_security_flatten_value( $value );
			$known_keys[] = $field_id;

			$fields[] = [
				'id'    => $field_id,
				'name'  => isset( $field['name'] ) ? (string) $field['name'] : $field_id,
				'title' => $title,
				'type'  => $type,
				'value' => $raw_value,
			];
		}
	}

	if ( is_array( $formatted ) ) {
		foreach ( $formatted as $field_id => $value ) {
			$field_id = (string) $field_id;

			if ( in_array( $field_id, $known_keys, true ) ) {
				continue;
			}

			$fields[] = [
				'id'    => $field_id,
				'name'  => $field_id,
				'title' => $field_id,
				'type'  => '',
				'value' => hello_elementor_child_form_security_flatten_value( $value ),
			];
		}
	}

	return $fields;
}

/**
 * Determine whether a field is allowed to receive links.
 *
 * @param array $field Normalized field data.
 *
 * @return bool
 */
function hello_elementor_child_form_security_is_url_field( $field ) {
	$type   = strtolower( trim( (string) ( $field['type'] ?? '' ) ) );
	$marker = strtolower(
		implode(
			' ',
			[
				$field['id'] ?? '',
				$field['name'] ?? '',
				$field['title'] ?? '',
			]
		)
	);

	if ( in_array( $type, [ 'url', 'link', 'website' ], true ) ) {
		return true;
	}

	return (bool) preg_match( '/\b(url|link|enlace|website|sitio\s*web|pagina\s*web|página\s*web)\b/u', $marker );
}

/**
 * Extract obvious links from submitted text.
 *
 * @param string $value Field value.
 *
 * @return array
 */
function hello_elementor_child_form_security_extract_urls( $value ) {
	$urls = [];

	if ( preg_match_all( '/\b(?:https?:\/\/|www\.)[^\s<>"\']+/iu', (string) $value, $matches ) ) {
		$urls = $matches[0];
	}

	return $urls;
}

/**
 * Check if a URL/value contains a dangerous scheme or blocked fragment.
 *
 * @param string $value URL or field value.
 *
 * @return bool
 */
function hello_elementor_child_form_security_has_malicious_url_fragment( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return false;
	}

	if ( preg_match( '/\b(?:javascript|data|vbscript|file):/i', $value ) ) {
		return true;
	}

	$fragments = hello_elementor_child_form_security_parse_terms( hello_elementor_child_get_option( 'form_security_url_blocklist', hello_elementor_child_default_url_blocklist() ) );

	foreach ( $fragments as $fragment ) {
		if ( hello_elementor_child_form_security_contains( $value, $fragment ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Add current visitor IP to the block list when spam is detected.
 *
 * @return void
 */
function hello_elementor_child_form_security_auto_block_current_ip() {
	if ( '1' !== (string) hello_elementor_child_get_option( 'form_security_auto_block_ips', '1' ) ) {
		return;
	}

	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return;
	}

	$ip = hello_elementor_child_form_security_get_client_ip();

	if ( '' === $ip ) {
		return;
	}

	$options = get_option( 'hello_elementor_child_options', [] );

	if ( ! is_array( $options ) ) {
		$options = [];
	}

	$current = isset( $options['form_security_blocked_ips'] ) ? (string) $options['form_security_blocked_ips'] : (string) hello_elementor_child_get_option( 'form_security_blocked_ips', '' );
	$ips     = hello_elementor_child_form_security_parse_ips( $current );

	if ( in_array( $ip, $ips, true ) ) {
		return;
	}

	$ips[] = $ip;
	$options['form_security_blocked_ips'] = implode( "\n", $ips );

	update_option( 'hello_elementor_child_options', $options );
}

/**
 * Register an antispam error with Elementor and optionally auto-block the IP.
 *
 * @param object $ajax_handler Elementor AJAX handler.
 * @param string $message      Error message.
 *
 * @return void
 */
function hello_elementor_child_form_security_add_error( $ajax_handler, $message ) {
	if ( is_object( $ajax_handler ) && method_exists( $ajax_handler, 'add_error' ) ) {
		$ajax_handler->add_error( 'form_error', $message );
	}

	hello_elementor_child_form_security_auto_block_current_ip();
}

/**
 * Validate Elementor Pro forms before sending.
 *
 * @param object $record       Elementor form record.
 * @param object $ajax_handler Elementor AJAX handler.
 *
 * @return void
 */
function hello_elementor_child_form_security_validate_elementor_form( $record, $ajax_handler ) {
	if ( ! hello_elementor_child_form_security_enabled() ) {
		return;
	}

	$fields        = hello_elementor_child_form_security_get_elementor_fields( $record );
	$keywords      = hello_elementor_child_form_security_parse_terms( hello_elementor_child_get_option( 'form_security_keywords', hello_elementor_child_default_spam_keywords() ) );
	$should_block_urls = '1' === (string) hello_elementor_child_get_option( 'form_security_block_urls', '1' );

	foreach ( $fields as $field ) {
		$value = (string) ( $field['value'] ?? '' );

		if ( '' === trim( $value ) ) {
			continue;
		}

		foreach ( $keywords as $keyword ) {
			if ( hello_elementor_child_form_security_contains( $value, $keyword ) ) {
				hello_elementor_child_form_security_add_error( $ajax_handler, __( 'Error: El formulario contiene palabras no permitidas.', 'hello-elementor-child' ) );
				return;
			}
		}

		if ( hello_elementor_child_form_security_has_malicious_url_fragment( $value ) ) {
			hello_elementor_child_form_security_add_error( $ajax_handler, __( 'Error: El formulario contiene una URL no permitida.', 'hello-elementor-child' ) );
			return;
		}

		if ( ! $should_block_urls ) {
			continue;
		}

		$urls = hello_elementor_child_form_security_extract_urls( $value );

		if ( ! empty( $urls ) && ! hello_elementor_child_form_security_is_url_field( $field ) ) {
			hello_elementor_child_form_security_add_error( $ajax_handler, __( 'Error: Solo se permiten enlaces en campos tipo URL o enlace.', 'hello-elementor-child' ) );
			return;
		}
	}
}
add_action( 'elementor_pro/forms/validation', 'hello_elementor_child_form_security_validate_elementor_form', 10, 2 );

/**
 * Block visitors whose IP is in the blocked list.
 *
 * @return void
 */
function hello_elementor_child_form_security_block_ip_request() {
	if ( ! hello_elementor_child_form_security_enabled() ) {
		return;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}

	if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
		return;
	}

	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return;
	}

	$ip = hello_elementor_child_form_security_get_client_ip();

	if ( '' === $ip ) {
		return;
	}

	$blocked_ips = hello_elementor_child_form_security_parse_ips( hello_elementor_child_get_option( 'form_security_blocked_ips', hello_elementor_child_default_blocked_ips() ) );
	$is_blocked  = false;

	foreach ( $blocked_ips as $rule ) {
		if ( hello_elementor_child_form_security_ip_matches_rule( $ip, $rule ) ) {
			$is_blocked = true;
			break;
		}
	}

	if ( ! $is_blocked ) {
		return;
	}

	status_header( 403 );
	wp_die(
		esc_html__( 'Acceso denegado.', 'hello-elementor-child' ),
		esc_html__( 'Acceso denegado', 'hello-elementor-child' ),
		[
			'response' => 403,
		]
	);
}
add_action( 'init', 'hello_elementor_child_form_security_block_ip_request', 1 );

/**
 * Migrate old plugin options into the theme options if they exist.
 *
 * @return void
 */
function hello_elementor_child_form_security_migrate_legacy_options() {
	$options = get_option( 'hello_elementor_child_options', [] );

	if ( ! is_array( $options ) ) {
		$options = [];
	}

	$changed = false;
	$legacy_keywords = get_option( 'custom_form_keywords', '' );
	$legacy_ips      = get_option( 'ips_bloqueadas', '' );

	if ( '' !== trim( (string) $legacy_keywords ) && empty( $options['form_security_keywords'] ) ) {
		$options['form_security_keywords'] = sanitize_textarea_field( $legacy_keywords );
		$changed = true;
	}

	if ( '' !== trim( (string) $legacy_ips ) && empty( $options['form_security_blocked_ips'] ) ) {
		$options['form_security_blocked_ips'] = sanitize_textarea_field( $legacy_ips );
		$changed = true;
	}

	if ( $changed ) {
		update_option( 'hello_elementor_child_options', $options );
	}
}
add_action( 'admin_init', 'hello_elementor_child_form_security_migrate_legacy_options' );

/**
 * Seed the editable IP/CIDR blocklist once when upgrading from a version that had it empty.
 *
 * This keeps the list editable: if the admin later clears it, it will not be
 * recreated automatically because the seed flag remains saved separately.
 *
 * @return void
 */
function hello_elementor_child_form_security_seed_default_blocked_ips() {
	$seeded = get_option( 'hello_elementor_child_form_security_seeded_default_ips', '0' );

	if ( '1' === (string) $seeded ) {
		return;
	}

	$options = get_option( 'hello_elementor_child_options', [] );

	if ( ! is_array( $options ) ) {
		$options = [];
	}

	$current = isset( $options['form_security_blocked_ips'] ) ? trim( (string) $options['form_security_blocked_ips'] ) : '';

	if ( '' === $current ) {
		$options['form_security_blocked_ips'] = hello_elementor_child_default_blocked_ips();
		update_option( 'hello_elementor_child_options', $options );
	}

	update_option( 'hello_elementor_child_form_security_seeded_default_ips', '1' );
}
add_action( 'admin_init', 'hello_elementor_child_form_security_seed_default_blocked_ips', 20 );
