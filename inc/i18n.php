<?php
/**
 * Internationalization helpers for the child theme.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load child theme translations from /languages.
 *
 * The source strings are intentionally written in English. Spanish and other
 * languages should be handled through .po/.mo files using the text domain
 * hello-elementor-child. Within a child theme, the files MUST be named
 * after the locale alone (for example es_GT.mo, not hello-elementor-child-es_GT.mo).
 *
 * @return void
 */
function hello_elementor_child_load_textdomain() {
	load_child_theme_textdomain(
		'hello-elementor-child',
		HELLO_ELEMENTOR_CHILD_PATH . 'languages'
	);
}
add_action( 'after_setup_theme', 'hello_elementor_child_load_textdomain' );
