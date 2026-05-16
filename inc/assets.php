<?php
/**
 * Front-end assets and body classes.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Build inline CSS variables for configurable floating buttons.
 *
 * @return string
 */
function hello_elementor_child_get_floating_buttons_inline_css() {
	$back_to_top_size = hello_elementor_child_sanitize_button_size( hello_elementor_child_get_option( 'back_to_top_size', '52' ), 52 );
	$whatsapp_size    = hello_elementor_child_sanitize_button_size( hello_elementor_child_get_option( 'whatsapp_size', '52' ), 52 );

	$back_to_top_bg_color       = hello_elementor_child_sanitize_color_option( hello_elementor_child_get_option( 'back_to_top_bg_color', '#1c1c1e' ), '#1c1c1e' );
	$back_to_top_text_color     = hello_elementor_child_sanitize_color_option( hello_elementor_child_get_option( 'back_to_top_text_color', '#ffffff' ), '#ffffff' );
	$back_to_top_border_color   = hello_elementor_child_sanitize_color_option( hello_elementor_child_get_option( 'back_to_top_border_color', '#ffffff' ), '#ffffff' );
	$back_to_top_hover_bg_color = hello_elementor_child_sanitize_color_option( hello_elementor_child_get_option( 'back_to_top_hover_bg_color', '#000000' ), '#000000' );

	$back_to_top_icon_size = max( 16, round( absint( $back_to_top_size ) * 0.46 ) );

	return sprintf(
		':root{--hello-child-back-to-top-size:%1$dpx;--hello-child-back-to-top-icon-size:%2$dpx;--hello-child-back-to-top-bg-color:%3$s;--hello-child-back-to-top-text-color:%4$s;--hello-child-back-to-top-border-color:%5$s;--hello-child-back-to-top-hover-bg-color:%6$s;--hello-child-whatsapp-size:%7$dpx;--hello-child-floating-gap:12px;}',
		absint( $back_to_top_size ),
		absint( $back_to_top_icon_size ),
		$back_to_top_bg_color,
		$back_to_top_text_color,
		$back_to_top_border_color,
		$back_to_top_hover_bg_color,
		absint( $whatsapp_size )
	);
}

/**
 * Load child theme scripts and styles.
 *
 * @return void
 */
function hello_elementor_child_enqueue_assets() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		HELLO_ELEMENTOR_CHILD_URI . 'style.css',
		[ 'hello-elementor-theme-style' ],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_enqueue_style(
		'hello-elementor-child-shortcodes',
		HELLO_ELEMENTOR_CHILD_URI . 'assets/css/shortcodes.css',
		[],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	wp_enqueue_script(
		'hello-elementor-child-custom',
		HELLO_ELEMENTOR_CHILD_URI . 'assets/js/custom.js',
		[],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true
	);

	wp_script_add_data( 'hello-elementor-child-custom', 'defer', true );

	if ( hello_elementor_child_is_back_to_top_enabled() || hello_elementor_child_is_whatsapp_enabled() ) {
		wp_enqueue_style(
			'hello-elementor-child-back-to-top',
			HELLO_ELEMENTOR_CHILD_URI . 'assets/css/back-to-top.css',
			[],
			HELLO_ELEMENTOR_CHILD_VERSION
		);

		wp_add_inline_style(
			'hello-elementor-child-back-to-top',
			hello_elementor_child_get_floating_buttons_inline_css()
		);
	}

	if ( hello_elementor_child_is_back_to_top_enabled() ) {
		wp_enqueue_script(
			'hello-elementor-child-back-to-top',
			HELLO_ELEMENTOR_CHILD_URI . 'assets/js/back-to-top.js',
			[],
			HELLO_ELEMENTOR_CHILD_VERSION,
			false
		);

		wp_localize_script(
			'hello-elementor-child-back-to-top',
			'HelloChildBackToTop',
			[
				'ariaLabel' => hello_elementor_child_get_translated_option( 'back_to_top_label', __( 'Back to top', 'hello-elementor-child' ) ),
				'icon'      => hello_elementor_child_get_option( 'back_to_top_icon', '↑' ),
			]
		);

		wp_script_add_data( 'hello-elementor-child-back-to-top', 'defer', true );
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_assets', 20 );

/**
 * Add a body class for users that can manage the site.
 * This allows child JS to bypass blocking cookie/consent overlays only for admins.
 *
 * @param array $classes Body classes.
 *
 * @return array
 */
function hello_elementor_child_admin_body_class( $classes ) {
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		$classes[] = 'user-can-manage-options';
	}

	return $classes;
}
add_filter( 'body_class', 'hello_elementor_child_admin_body_class' );
