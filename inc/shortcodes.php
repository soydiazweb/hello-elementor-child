<?php
/**
 * Theme shortcodes.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a small "Powered by" credit link.
 *
 * Usage examples:
 * [powerby]
 * [powerby url="https://www.solucionweb.com" text="Solución Web"]
 * [powerby label="Desarrollado por" text="fullcom"]
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function hello_elementor_child_powerby_shortcode( $atts ) {
	$atts = shortcode_atts(
		[
			'label'  => hello_elementor_child_get_translated_option( 'powerby_label', __( 'Powered by', 'hello-elementor-child' ) ),
			'text'   => hello_elementor_child_get_option( 'powerby_link_text', 'Solución Web' ),
			'texto'  => '',
			'url'    => hello_elementor_child_get_option( 'powerby_url', 'https://www.solucionweb.com' ),
			'target' => '1' === (string) hello_elementor_child_get_option( 'powerby_target_blank', '1' ) ? '_blank' : '_self',
		],
		(array) $atts,
		'powerby'
	);

	$link_text = '' !== trim( (string) $atts['texto'] ) ? $atts['texto'] : $atts['text'];
	$target    = '_self' === $atts['target'] ? '_self' : '_blank';
	$rel       = '_blank' === $target ? ' rel="noopener noreferrer"' : '';

	return sprintf(
		'<span class="sw-powered-by"><span class="sw-powered-by__label">%1$s</span> <span class="sw-powered-by__year">%2$s</span> <a class="sw-powered-by__link" href="%3$s" target="%4$s"%5$s>%6$s</a></span>',
		esc_html( hello_elementor_child_format_option_text( $atts['label'] ) ),
		esc_html( wp_date( 'Y' ) ),
		esc_url( $atts['url'] ),
		esc_attr( $target ),
		$rel,
		esc_html( hello_elementor_child_format_option_text( $link_text ) )
	);
}
add_shortcode( 'powerby', 'hello_elementor_child_powerby_shortcode' );

/**
 * Render a back button that sends the visitor one page back in browser history.
 *
 * Usage examples:
 * [back]
 * [back text="Volver"]
 * [back icon="‹" fallback="/contacto/"]
 * [back class="my-class"]
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function hello_elementor_child_back_shortcode( $atts ) {
	$atts = shortcode_atts(
		[
			'text'       => hello_elementor_child_get_translated_option( 'back_button_text', __( 'Back', 'hello-elementor-child' ) ),
			'texto'      => '',
			'label'      => '',
			'icon'       => hello_elementor_child_get_option( 'back_button_icon', '←' ),
			'class'      => '',
			'fallback'   => hello_elementor_child_get_option( 'back_button_fallback', '' ),
			'aria_label' => '',
		],
		(array) $atts,
		'back'
	);

	$label = $atts['text'];

	if ( '' !== trim( (string) $atts['label'] ) ) {
		$label = $atts['label'];
	} elseif ( '' !== trim( (string) $atts['texto'] ) ) {
		$label = $atts['texto'];
	}

	$icon        = trim( (string) $atts['icon'] );
	$extra_class = sanitize_html_class( $atts['class'] );
	$classes     = trim( 'sw-back-button' . ( $extra_class ? ' ' . $extra_class : '' ) );
	$fallback    = '' !== trim( (string) $atts['fallback'] ) ? $atts['fallback'] : home_url( '/' );
	$aria_label  = '' !== trim( (string) $atts['aria_label'] ) ? $atts['aria_label'] : $label;

	$icon_html = '' !== $icon ? sprintf(
		'<span class="sw-back-button__icon" aria-hidden="true">%s</span>',
		esc_html( $icon )
	) : '';

	return sprintf(
		'<button type="button" class="%1$s" data-sw-back-button data-fallback-url="%2$s" aria-label="%3$s">%4$s<span class="sw-back-button__text">%5$s</span></button>',
		esc_attr( $classes ),
		esc_url( $fallback ),
		esc_attr( hello_elementor_child_format_option_text( $aria_label ) ),
		$icon_html,
		esc_html( hello_elementor_child_format_option_text( $label ) )
	);
}
add_shortcode( 'back', 'hello_elementor_child_back_shortcode' );
