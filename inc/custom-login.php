<?php
/**
 * Custom login endpoint rendered at /panel/admin/.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force WordPress-generated lost password links to use /panel/admin/.
 * This also fixes links inserted by core inside login error messages.
 *
 * @param string $lostpassword_url Original URL.
 * @param string $redirect         Optional redirect URL.
 *
 * @return string
 */
function hello_elementor_child_filter_lostpassword_url( $lostpassword_url, $redirect ) {
	return hello_elementor_child_custom_lostpassword_url( $redirect );
}
add_filter( 'lostpassword_url', 'hello_elementor_child_filter_lostpassword_url', 10, 2 );

/**
 * Force WordPress-generated login URLs to use /panel/admin/ without redirecting /wp-admin manually.
 *
 * @param string $login_url    Original URL.
 * @param string $redirect     Optional redirect URL.
 * @param bool   $force_reauth Whether to force reauth.
 *
 * @return string
 */
function hello_elementor_child_filter_login_url( $login_url, $redirect, $force_reauth ) {
	$url = hello_elementor_child_custom_login_url();

	if ( ! empty( $redirect ) ) {
		$url = add_query_arg( 'redirect_to', $redirect, $url );
	}

	if ( $force_reauth ) {
		$url = add_query_arg( 'reauth', '1', $url );
	}

	return $url;
}
add_filter( 'login_url', 'hello_elementor_child_filter_login_url', 10, 3 );

/**
 * Replace any wp-login.php links that WordPress core may inject into error messages.
 *
 * @param string $message Error/message HTML.
 *
 * @return string
 */
function hello_elementor_child_rewrite_login_message_links( $message ) {
	if ( empty( $message ) ) {
		return $message;
	}

	$lostpassword_url = esc_url( hello_elementor_child_custom_lostpassword_url() );
	$login_url        = esc_url( hello_elementor_child_custom_login_url() );

	$message = preg_replace(
		'#href=(["\'])[^"\']*wp-login\.php\?action=lostpassword[^"\']*\1#i',
		'href="' . $lostpassword_url . '"',
		$message
	);

	$message = preg_replace(
		'#href=(["\'])[^"\']*wp-login\.php[^"\']*\1#i',
		'href="' . $login_url . '"',
		$message
	);

	$message = str_replace(
		[
			'/wp-login.php?action=lostpassword',
			'wp-login.php?action=lostpassword',
		],
		$lostpassword_url,
		$message
	);

	return $message;
}

/**
 * Replace logout URLs with the custom panel route so users do not land on wp-login.php after logout.
 *
 * @param string $logout_url Original logout URL.
 * @param string $redirect   Optional redirect URL.
 *
 * @return string
 */
function hello_elementor_child_custom_logout_url( $logout_url, $redirect ) {
	$target = add_query_arg( 'action', 'logout', hello_elementor_child_custom_login_url() );

	if ( ! empty( $redirect ) ) {
		$target = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $target );
	}

	return wp_nonce_url( $target, 'log-out' );
}
add_filter( 'logout_url', 'hello_elementor_child_custom_logout_url', 10, 2 );

/**
 * Register only the exact custom login rewrite rule.
 *
 * @return void
 */
function hello_elementor_child_add_login_rewrite_rule() {
	add_rewrite_rule(
		'^' . trim( HELLO_ELEMENTOR_CHILD_LOGIN_SLUG, '/' ) . '/?$',
		'index.php?hello_child_custom_login=1',
		'top'
	);
}
add_action( 'init', 'hello_elementor_child_add_login_rewrite_rule' );

/**
 * Register custom query var for the login endpoint.
 *
 * @param array $vars Public query vars.
 *
 * @return array
 */
function hello_elementor_child_login_query_vars( $vars ) {
	$vars[] = 'hello_child_custom_login';
	return $vars;
}
add_filter( 'query_vars', 'hello_elementor_child_login_query_vars' );

/**
 * Flush rewrite rules when the theme is activated.
 *
 * @return void
 */
function hello_elementor_child_flush_login_rewrites() {
	hello_elementor_child_add_login_rewrite_rule();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'hello_elementor_child_flush_login_rewrites' );

/**
 * Flush rewrite rules once after this customization is installed.
 * Useful when replacing the theme files without reactivating the theme.
 *
 * @return void
 */
function hello_elementor_child_maybe_flush_login_rewrites() {
	if ( get_option( 'hello_elementor_child_rewrite_version' ) !== HELLO_ELEMENTOR_CHILD_REWRITE_VERSION ) {
		hello_elementor_child_add_login_rewrite_rule();
		flush_rewrite_rules();
		update_option( 'hello_elementor_child_rewrite_version', HELLO_ELEMENTOR_CHILD_REWRITE_VERSION, false );
	}
}
add_action( 'init', 'hello_elementor_child_maybe_flush_login_rewrites', 99 );

/**
 * Send a password reset email using the custom login endpoint instead of wp-login.php.
 *
 * @param WP_User $user User object.
 *
 * @return true|WP_Error
 */
function hello_elementor_child_send_password_reset_email( $user ) {
	$key = get_password_reset_key( $user );

	if ( is_wp_error( $key ) ) {
		return $key;
	}

	$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$reset_url = add_query_arg(
		[
			'action' => 'rp',
			'key'    => $key,
			'login'  => $user->user_login,
		],
		hello_elementor_child_custom_login_url()
	);

	$subject = sprintf(
		/* translators: %s: Site name. */
		__( '[%s] Reset password', 'hello-elementor-child' ),
		$site_name
	);

	$message  = sprintf(
		/* translators: %s: Site name. */
		__( "Hello,\n\nWe received a request to reset the password for your account on %s.", 'hello-elementor-child' ),
		$site_name
	);
	$message .= "\n\n" . __( 'To create a new password, open this link:', 'hello-elementor-child' );
	$message .= "\n\n" . $reset_url;
	$message .= "\n\n" . __( 'If you did not request this change, you can ignore this email.', 'hello-elementor-child' );

	$sent = wp_mail( $user->user_email, wp_specialchars_decode( $subject ), $message );

	if ( ! $sent ) {
		return new WP_Error( 'email_not_sent', __( 'The recovery email could not be sent. Please try again later.', 'hello-elementor-child' ) );
	}

	return true;
}

/**
 * Render a standalone login page only when /panel/admin/ is requested directly.
 *
 * @return void
 */
function hello_elementor_child_render_custom_login_page() {
	if ( '1' !== (string) get_query_var( 'hello_child_custom_login' ) ) {
		return;
	}

	$action          = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$allowed_actions = [ 'login', 'logout', 'lostpassword', 'rp', 'resetpass' ];

	if ( ! in_array( $action, $allowed_actions, true ) ) {
		$action = 'login';
	}

	if ( is_user_logged_in() && 'logout' === $action ) {
		check_admin_referer( 'log-out' );
		wp_logout();
		wp_safe_redirect( add_query_arg( 'loggedout', 'true', hello_elementor_child_custom_login_url() ) );
		exit;
	}

	if ( is_user_logged_in() ) {
		wp_safe_redirect( admin_url() );
		exit;
	}

	$login_error     = '';
	$login_message   = '';
	$user_login      = '';
	$reset_login     = isset( $_REQUEST['login'] ) ? sanitize_user( wp_unslash( $_REQUEST['login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$reset_key       = isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$reset_user      = null;
	$show_reset_form = false;

	if ( ! empty( $_GET['loggedout'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$login_message = __( 'You have been logged out successfully.', 'hello-elementor-child' );
	}

	$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : admin_url(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $redirect_to ) ) {
		$redirect_to = admin_url();
	}

	if ( 'POST' === strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET' ) ) {
		if ( 'lostpassword' === $action ) {
			$nonce = isset( $_POST['hello_child_lostpassword_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hello_child_lostpassword_nonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'hello_child_custom_lostpassword' ) ) {
				$login_error = __( 'The session expired. Reload the page and try again.', 'hello-elementor-child' );
			} else {
				$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';

				if ( '' === $user_login ) {
					$login_error = __( 'Enter your username or email address.', 'hello-elementor-child' );
				} else {
					$user = false;

					if ( is_email( $user_login ) ) {
						$user = get_user_by( 'email', $user_login );
					}

					if ( ! $user ) {
						$user = get_user_by( 'login', $user_login );
					}

					if ( $user instanceof WP_User ) {
						$sent = hello_elementor_child_send_password_reset_email( $user );

						if ( is_wp_error( $sent ) ) {
							$login_error = $sent->get_error_message();
						} else {
							$login_message = __( 'If the user exists, you will receive an email with instructions to reset the password.', 'hello-elementor-child' );
							$action        = 'login';
						}
					} else {
						$login_message = __( 'If the user exists, you will receive an email with instructions to reset the password.', 'hello-elementor-child' );
						$action        = 'login';
					}
				}
			}
		} elseif ( 'resetpass' === $action ) {
			$nonce       = isset( $_POST['hello_child_resetpass_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hello_child_resetpass_nonce'] ) ) : '';
			$reset_login = isset( $_POST['login'] ) ? sanitize_user( wp_unslash( $_POST['login'] ) ) : '';
			$reset_key   = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
			$pass1       = isset( $_POST['pass1'] ) ? (string) wp_unslash( $_POST['pass1'] ) : '';
			$pass2       = isset( $_POST['pass2'] ) ? (string) wp_unslash( $_POST['pass2'] ) : '';

			if ( ! wp_verify_nonce( $nonce, 'hello_child_custom_resetpass' ) ) {
				$login_error = __( 'The session expired. Request a new password reset.', 'hello-elementor-child' );
				$action      = 'lostpassword';
			} elseif ( '' === $pass1 || '' === $pass2 ) {
				$login_error     = __( 'Enter and confirm your new password.', 'hello-elementor-child' );
				$show_reset_form = true;
				$action          = 'rp';
			} elseif ( $pass1 !== $pass2 ) {
				$login_error     = __( 'The passwords do not match.', 'hello-elementor-child' );
				$show_reset_form = true;
				$action          = 'rp';
			} else {
				$reset_user = check_password_reset_key( $reset_key, $reset_login );

				if ( is_wp_error( $reset_user ) ) {
					$login_error = __( 'The recovery link is invalid or has expired.', 'hello-elementor-child' );
					$action      = 'lostpassword';
				} else {
					reset_password( $reset_user, $pass1 );
					$login_message = __( 'Your password has been updated successfully. You can now sign in.', 'hello-elementor-child' );
					$action        = 'login';
				}
			}
		} else {
			$action = 'login';
			$nonce  = isset( $_POST['hello_child_login_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['hello_child_login_nonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'hello_child_custom_login' ) ) {
				$login_error = __( 'The session expired. Reload the page and try again.', 'hello-elementor-child' );
			} else {
				$user_login    = isset( $_POST['log'] ) ? sanitize_user( wp_unslash( $_POST['log'] ) ) : '';
				$user_password = isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : '';
				$remember      = ! empty( $_POST['rememberme'] );

				if ( '' === $user_login || '' === $user_password ) {
					$login_error = __( 'Enter your username and password.', 'hello-elementor-child' );
				} else {
					$user = wp_signon(
						[
							'user_login'    => $user_login,
							'user_password' => $user_password,
							'remember'      => $remember,
						],
						is_ssl()
					);

					if ( is_wp_error( $user ) ) {
						$login_error = hello_elementor_child_rewrite_login_message_links( $user->get_error_message() );
					} else {
						wp_safe_redirect( $redirect_to );
						exit;
					}
				}
			}
		}
	}

	if ( 'rp' === $action ) {
		$reset_user = check_password_reset_key( $reset_key, $reset_login );

		if ( is_wp_error( $reset_user ) ) {
			$login_error = __( 'The recovery link is invalid or has expired.', 'hello-elementor-child' );
			$action      = 'lostpassword';
		} else {
			$show_reset_form = true;
		}
	}

	status_header( 200 );
	nocache_headers();

	$assets_ver      = rawurlencode( HELLO_ELEMENTOR_CHILD_VERSION );
	$login_css       = esc_url( HELLO_ELEMENTOR_CHILD_URI . 'assets/css/login.css?ver=' . $assets_ver );
	$login_js        = esc_url( HELLO_ELEMENTOR_CHILD_URI . 'assets/js/login.js?ver=' . $assets_ver );
	$back_url        = esc_url( home_url( '/' ) );
	$login_url       = esc_url( hello_elementor_child_custom_login_url() );
	$site_name       = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$login_title     = hello_elementor_child_format_option_text( hello_elementor_child_get_translated_option( 'login_title', __( 'Admin panel', 'hello-elementor-child' ) ) );
	$login_subtitle  = hello_elementor_child_format_option_text( hello_elementor_child_get_translated_option( 'login_subtitle', __( 'Private and secure access', 'hello-elementor-child' ) ) );
	$login_back_text = hello_elementor_child_format_option_text( hello_elementor_child_get_translated_option( 'login_back_text', __( 'Back to website', 'hello-elementor-child' ) ) );

	?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $login_title ); ?></title>
	<link rel="stylesheet" href="<?php echo $login_css; ?>">
</head>
<body class="login custom-panel-login wp-core-ui">
	<main id="login" class="custom-login-card" aria-label="<?php echo esc_attr( $login_title ); ?>">
		<h1 class="custom-login-logo">
			<a href="<?php echo $back_url; ?>" aria-label="<?php echo esc_attr( $login_back_text ); ?>">
				<span class="custom-login-mark" aria-hidden="true"></span>
			</a>
		</h1>

		<div class="custom-login-heading">
			<strong><?php echo esc_html( $login_title ); ?></strong>
			<span><?php echo esc_html( $login_subtitle ); ?></span>
		</div>

		<?php if ( ! empty( $login_message ) ) : ?>
			<div class="message" role="status"><?php echo esc_html( $login_message ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $login_error ) ) : ?>
			<div id="login_error" role="alert"><?php echo wp_kses_post( hello_elementor_child_rewrite_login_message_links( $login_error ) ); ?></div>
		<?php endif; ?>

		<?php if ( 'lostpassword' === $action ) : ?>
			<form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url( hello_elementor_child_custom_lostpassword_url() ); ?>" method="post" novalidate>
				<p class="custom-login-help"><?php echo esc_html__( 'Enter your username or email address and we will send you a link to reset your password.', 'hello-elementor-child' ); ?></p>
				<p>
					<label for="user_login"><?php echo esc_html__( 'Username or email address', 'hello-elementor-child' ); ?></label>
					<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr( $user_login ); ?>" size="20" autocomplete="username" required>
				</p>

				<?php wp_nonce_field( 'hello_child_custom_lostpassword', 'hello_child_lostpassword_nonce' ); ?>
				<input type="hidden" name="action" value="lostpassword">

				<p class="submit">
					<button type="submit" class="button button-primary button-large">
						<?php echo esc_html__( 'Send recovery link', 'hello-elementor-child' ); ?>
					</button>
				</p>
			</form>

			<p class="custom-login-secondary"><a href="<?php echo $login_url; ?>"><?php echo esc_html__( 'Back to login', 'hello-elementor-child' ); ?></a></p>
		<?php elseif ( $show_reset_form ) : ?>
			<form name="resetpassform" id="resetpassform" action="<?php echo esc_url( add_query_arg( 'action', 'resetpass', hello_elementor_child_custom_login_url() ) ); ?>" method="post" novalidate>
				<p class="custom-login-help"><?php echo esc_html__( 'Create a new password for your account.', 'hello-elementor-child' ); ?></p>
				<p>
					<label for="pass1"><?php echo esc_html__( 'New password', 'hello-elementor-child' ); ?></label>
					<input type="password" name="pass1" id="pass1" class="input password-input" value="" size="20" autocomplete="new-password" required>
				</p>

				<p>
					<label for="pass2"><?php echo esc_html__( 'Confirm password', 'hello-elementor-child' ); ?></label>
					<input type="password" name="pass2" id="pass2" class="input password-input" value="" size="20" autocomplete="new-password" required>
				</p>

				<?php wp_nonce_field( 'hello_child_custom_resetpass', 'hello_child_resetpass_nonce' ); ?>
				<input type="hidden" name="action" value="resetpass">
				<input type="hidden" name="key" value="<?php echo esc_attr( $reset_key ); ?>">
				<input type="hidden" name="login" value="<?php echo esc_attr( $reset_login ); ?>">

				<p class="submit">
					<button type="submit" class="button button-primary button-large">
						<?php echo esc_html__( 'Save new password', 'hello-elementor-child' ); ?>
					</button>
				</p>
			</form>
		<?php else : ?>
			<form name="loginform" id="loginform" action="<?php echo esc_url( hello_elementor_child_custom_login_url() ); ?>" method="post" novalidate>
				<p>
					<label for="user_login"><?php echo esc_html__( 'Username or email address', 'hello-elementor-child' ); ?></label>
					<input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr( $user_login ); ?>" size="20" autocomplete="username" required>
				</p>

				<p>
					<label for="user_pass"><?php echo esc_html__( 'Password', 'hello-elementor-child' ); ?></label>
					<input type="password" name="pwd" id="user_pass" class="input password-input" value="" size="20" autocomplete="current-password" spellcheck="false" required>
				</p>

				<div class="custom-login-row">
					<label class="rememberme" for="rememberme">
						<input name="rememberme" type="checkbox" id="rememberme" value="forever" <?php checked( ! empty( $_POST['rememberme'] ) ); ?>>
						<span><?php echo esc_html__( 'Remember me', 'hello-elementor-child' ); ?></span>
					</label>

					<a class="custom-login-link" href="<?php echo esc_url( hello_elementor_child_custom_lostpassword_url() ); ?>">
						<?php echo esc_html__( 'Forgot password', 'hello-elementor-child' ); ?>
					</a>
				</div>

				<?php wp_nonce_field( 'hello_child_custom_login', 'hello_child_login_nonce' ); ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

				<p class="submit">
					<button type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large">
						<?php echo esc_html__( 'Sign in to panel', 'hello-elementor-child' ); ?>
					</button>
				</p>
			</form>
		<?php endif; ?>

		<p id="backtoblog"><a href="<?php echo $back_url; ?>"><?php echo esc_html( $login_back_text ); ?></a></p>
	</main>

	<script>
		window.HelloChildLogin = <?php echo wp_json_encode(
			[
				'siteName' => $site_name,
				'i18n'     => [
					'headingTitle'    => $login_title,
					'headingSubtitle' => $login_subtitle,
				],
			]
		); ?>;
	</script>
	<script src="<?php echo $login_js; ?>" defer></script>
</body>
</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'hello_elementor_child_render_custom_login_page', 0 );
