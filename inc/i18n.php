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
 * hello-elementor-child.
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
