<?php
/**
 * Access rules for default WordPress login/admin URLs and 404 handling.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirect blocked/default WordPress access URLs to the contact page.
 *
 * @return void
 */
function hello_elementor_child_redirect_to_contact() {
	wp_safe_redirect( hello_elementor_child_contact_url(), 302 );
	exit;
}

/**
 * Redirect normal WordPress 404 responses to /contacto/.
 * Avoids a loop if /contacto/ itself is missing.
 *
 * @return void
 */
function hello_elementor_child_redirect_404_to_contact() {
	if ( ! is_404() ) {
		return;
	}

	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );
	$request_path = is_string( $request_path ) ? '/' . trim( $request_path, '/' ) : '';

	if ( '/contacto' === $request_path || '/contacto/' === $request_path ) {
		return;
	}

	hello_elementor_child_redirect_to_contact();
}
add_action( 'template_redirect', 'hello_elementor_child_redirect_404_to_contact', 1 );

/**
 * Disable the default WordPress login/admin entry points only for anonymous users.
 * /panel/admin/ remains the only visible login entry.
 *
 * @return void
 */
function hello_elementor_child_block_default_login_routes() {
	if ( is_user_logged_in() ) {
		return;
	}

	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );
	$request_path = is_string( $request_path ) ? $request_path : '';
	$request_path = '/' . ltrim( $request_path, '/' );

	$script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( (string) wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : '';

	$is_wp_login = 'wp-login.php' === $script_name || (bool) preg_match( '#/wp-login\.php/?$#', $request_path );
	$is_wp_admin = (bool) preg_match( '#/wp-admin(?:/.*)?$#', $request_path );

	// Keep common public WordPress endpoints available for front-end AJAX/forms.
	$is_allowed_admin_endpoint = (bool) preg_match( '#/wp-admin/(admin-ajax|admin-post)\.php$#', $request_path );

	if ( $is_wp_login || ( $is_wp_admin && ! $is_allowed_admin_endpoint ) ) {
		hello_elementor_child_redirect_to_contact();
	}
}
add_action( 'init', 'hello_elementor_child_block_default_login_routes', 0 );
