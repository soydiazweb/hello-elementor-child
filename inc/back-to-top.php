<?php
/**
 * Global Back to Top button.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the global back-to-top button on front-end pages.
 * Hooked in wp_body_open and wp_footer as a fallback; static flag avoids duplicates.
 *
 * @return void
 */
function hello_elementor_child_print_back_to_top_button() {
	static $printed = false;

	if ( $printed || is_admin() || wp_doing_ajax() || ! hello_elementor_child_is_back_to_top_enabled() ) {
		return;
	}

	$printed = true;
	$label   = hello_elementor_child_get_translated_option( 'back_to_top_label', __( 'Back to top', 'hello-elementor-child' ) );
	$icon    = hello_elementor_child_get_option( 'back_to_top_icon', '↑' );
	?>
	<button type="button" class="hello-child-back-to-top" aria-label="<?php echo esc_attr( $label ); ?>">
		<span class="hello-child-back-to-top__icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
	</button>
	<?php
}
add_action( 'wp_body_open', 'hello_elementor_child_print_back_to_top_button', 20 );
add_action( 'wp_footer', 'hello_elementor_child_print_back_to_top_button', 30 );
