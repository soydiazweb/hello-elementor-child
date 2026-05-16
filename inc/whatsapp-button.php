<?php
/**
 * Global WhatsApp floating button.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the configured WhatsApp URL.
 *
 * @return string
 */
function hello_elementor_child_get_whatsapp_url() {
	$number = hello_elementor_child_sanitize_whatsapp_number( hello_elementor_child_get_option( 'whatsapp_number', '' ) );

	if ( '' === $number ) {
		return '';
	}

	$url     = 'https://wa.me/' . $number;
	$message = hello_elementor_child_get_translated_option( 'whatsapp_message', hello_elementor_child_get_option( 'whatsapp_message', '' ) );
	$message = hello_elementor_child_format_option_text( $message );

	if ( '' !== trim( $message ) ) {
		$url = add_query_arg( 'text', $message, $url );
	}

	return $url;
}

/**
 * Print the global WhatsApp button on front-end pages.
 * Hooked in wp_body_open and wp_footer as a fallback; static flag avoids duplicates.
 *
 * @return void
 */
function hello_elementor_child_print_whatsapp_button() {
	static $printed = false;

	if ( $printed || is_admin() || wp_doing_ajax() || ! hello_elementor_child_is_whatsapp_enabled() ) {
		return;
	}

	$url = hello_elementor_child_get_whatsapp_url();

	if ( '' === $url ) {
		return;
	}

	$printed  = true;
	$label    = hello_elementor_child_get_translated_option( 'whatsapp_label', __( 'Escríbenos por WhatsApp', 'hello-elementor-child' ) );
	$icon_url = HELLO_ELEMENTOR_CHILD_URI . 'assets/img/whatsapp.svg';
	$classes  = 'hello-child-whatsapp-button';

	if ( hello_elementor_child_is_back_to_top_enabled() ) {
		$classes .= ' hello-child-whatsapp-button--has-back-to-top';
	}
	?>
	<a class="<?php echo esc_attr( $classes ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( hello_elementor_child_format_option_text( $label ) ); ?>">
		<span class="hello-child-whatsapp-button__icon" aria-hidden="true">
			<img src="<?php echo esc_url( $icon_url ); ?>" alt="" width="1000" height="1000" loading="lazy" decoding="async">
		</span>
	</a>
	<?php
}
add_action( 'wp_body_open', 'hello_elementor_child_print_whatsapp_button', 21 );
add_action( 'wp_footer', 'hello_elementor_child_print_whatsapp_button', 31 );
