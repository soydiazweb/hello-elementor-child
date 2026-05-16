<?php
/**
 * Shared helpers for the child theme.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the custom login URL.
 *
 * @return string
 */
function hello_elementor_child_custom_login_url() {
	return home_url( '/' . trim( HELLO_ELEMENTOR_CHILD_LOGIN_SLUG, '/' ) . '/' );
}

/**
 * Build the custom lost password URL.
 *
 * @param string $redirect Optional redirect URL.
 *
 * @return string
 */
function hello_elementor_child_custom_lostpassword_url( $redirect = '' ) {
	$url = add_query_arg( 'action', 'lostpassword', hello_elementor_child_custom_login_url() );

	if ( ! empty( $redirect ) ) {
		$url = add_query_arg( 'redirect_to', $redirect, $url );
	}

	return $url;
}

/**
 * Get the contact URL used as the fallback destination for blocked/404 URLs.
 *
 * The requested default is /contacto/. For multilingual sites, this helper tries
 * to resolve the translated page URL when Polylang or WPML is active. The final
 * URL is also filterable with `hello_elementor_child_contact_url`.
 *
 * @return string
 */
function hello_elementor_child_contact_url() {
	$configured_url = function_exists( 'hello_elementor_child_get_option' ) ? hello_elementor_child_get_option( 'contact_url', '/contacto/' ) : '/contacto/';
	$url            = home_url( '/contacto/' );

	if ( ! empty( $configured_url ) ) {
		$url = 0 === strpos( $configured_url, '/' ) ? home_url( $configured_url ) : $configured_url;
	}
	$should_resolve_contact_page = in_array( untrailingslashit( (string) $configured_url ), [ '', '/contacto' ], true );

	if ( $should_resolve_contact_page ) {
		$page = get_page_by_path( 'contacto' );

		if ( $page instanceof WP_Post ) {
			$page_id = (int) $page->ID;

			if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
				$translated_id = pll_get_post( $page_id, pll_current_language() );

				if ( ! empty( $translated_id ) ) {
					$page_id = (int) $translated_id;
				}
			}

			if ( has_filter( 'wpml_object_id' ) ) {
				$translated_id = apply_filters( 'wpml_object_id', $page_id, 'page', true );

				if ( ! empty( $translated_id ) ) {
					$page_id = (int) $translated_id;
				}
			}

			$permalink = get_permalink( $page_id );

			if ( $permalink ) {
				$url = $permalink;
			}
		}
	}

	/**
	 * Filters the contact URL used for blocked login/admin routes and 404 redirects.
	 *
	 * @param string $url Contact URL.
	 */
	return apply_filters( 'hello_elementor_child_contact_url', $url );
}
