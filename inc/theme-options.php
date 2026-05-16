<?php
/**
 * Theme options page and helpers.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return default configurable values for the child theme.
 *
 * @return array
 */
function hello_elementor_child_default_options() {
	return [
		'back_to_top_enabled'        => '1',
		'back_to_top_icon'           => '↑',
		'back_to_top_label'          => __( 'Volver arriba', 'hello-elementor-child' ),
		'back_to_top_size'           => '52',
		'back_to_top_bg_color'       => '#1c1c1e',
		'back_to_top_text_color'     => '#ffffff',
		'back_to_top_border_color'   => '#ffffff',
		'back_to_top_hover_bg_color' => '#000000',
		'whatsapp_enabled'           => '0',
		'whatsapp_number'            => '',
		'whatsapp_size'              => '52',
		'whatsapp_label'             => __( 'Escríbenos por WhatsApp', 'hello-elementor-child' ),
		'whatsapp_message'           => __( 'Hola, quiero más información.', 'hello-elementor-child' ),
		'back_button_icon'           => '←',
		'back_button_text'           => __( 'Regresar', 'hello-elementor-child' ),
		'back_button_fallback'       => '',
		'powerby_label'              => __( 'Desarrollado por', 'hello-elementor-child' ),
		'powerby_link_text'          => 'Solución Web',
		'powerby_url'                => 'https://www.solucionweb.com',
		'powerby_target_blank'       => '1',
		'login_title'                => __( 'Panel de administración', 'hello-elementor-child' ),
		'login_subtitle'             => __( 'Acceso privado y seguro', 'hello-elementor-child' ),
		'login_back_text'            => __( 'Volver al sitio web', 'hello-elementor-child' ),
		'contact_url'                => '/contacto/',
		'back_to_top_label_es'       => 'Volver arriba',
		'back_to_top_label_en'       => 'Back to top',
		'whatsapp_label_es'          => 'Escríbenos por WhatsApp',
		'whatsapp_label_en'          => 'Chat on WhatsApp',
		'whatsapp_message_es'        => 'Hola, quiero más información.',
		'whatsapp_message_en'        => 'Hello, I would like more information.',
		'back_button_text_es'        => 'Regresar',
		'back_button_text_en'        => 'Back',
		'powerby_label_es'           => 'Desarrollado por',
		'powerby_label_en'           => 'Powered by',
		'login_title_es'             => 'Panel de administración',
		'login_title_en'             => 'Admin panel',
		'login_subtitle_es'          => 'Acceso privado y seguro',
		'login_subtitle_en'          => 'Private and secure access',
		'login_back_text_es'         => 'Volver al sitio web',
		'login_back_text_en'         => 'Back to website',
	];
}

/**
 * Read all saved theme options merged with defaults.
 *
 * @return array
 */
function hello_elementor_child_get_options() {
	$stored = get_option( 'hello_elementor_child_options', [] );

	if ( ! is_array( $stored ) ) {
		$stored = [];
	}

	return array_merge( hello_elementor_child_default_options(), $stored );
}

/**
 * Read one saved theme option.
 *
 * @param string $key     Option key.
 * @param mixed  $default Fallback value.
 *
 * @return mixed
 */
function hello_elementor_child_get_option( $key, $default = '' ) {
	$options = hello_elementor_child_get_options();

	return array_key_exists( $key, $options ) ? $options[ $key ] : $default;
}

/**
 * Read an option with language-specific override support.
 *
 * It checks, in order:
 * - option_locale, e.g. back_button_text_es_gt
 * - option_language, e.g. back_button_text_es
 * - base option, e.g. back_button_text
 *
 * @param string $key     Base option key.
 * @param mixed  $default Fallback value.
 *
 * @return string
 */
function hello_elementor_child_get_translated_option( $key, $default = '' ) {
	$options = hello_elementor_child_get_options();
	$locale  = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	$locale  = strtolower( str_replace( '-', '_', (string) $locale ) );
	$lang    = preg_replace( '/_.+$/', '', $locale );

	$candidates = [
		$key . '_' . $locale,
		$key . '_' . $lang,
		$key,
	];

	foreach ( $candidates as $candidate ) {
		if ( isset( $options[ $candidate ] ) && '' !== trim( (string) $options[ $candidate ] ) ) {
			return (string) $options[ $candidate ];
		}
	}

	return (string) $default;
}

/**
 * Format configurable text placeholders.
 *
 * @param string $text Text containing optional placeholders.
 *
 * @return string
 */
function hello_elementor_child_format_option_text( $text ) {
	$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	return str_replace(
		[ '{site_name}', '{year}' ],
		[ $site_name, wp_date( 'Y' ) ],
		(string) $text
	);
}

/**
 * Check whether the global Back to Top button is enabled.
 *
 * @return bool
 */
function hello_elementor_child_is_back_to_top_enabled() {
	return '1' === (string) hello_elementor_child_get_option( 'back_to_top_enabled', '1' );
}

/**
 * Check whether the global WhatsApp floating button is enabled.
 *
 * The button only renders when it is enabled and a number is configured.
 *
 * @return bool
 */
function hello_elementor_child_is_whatsapp_enabled() {
	$number = hello_elementor_child_get_option( 'whatsapp_number', '' );

	return '1' === (string) hello_elementor_child_get_option( 'whatsapp_enabled', '0' ) && '' !== hello_elementor_child_sanitize_whatsapp_number( $number );
}

/**
 * Sanitize a WhatsApp number for wa.me URLs.
 *
 * @param string $value Raw phone number.
 *
 * @return string
 */
function hello_elementor_child_sanitize_whatsapp_number( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return '';
	}

	$value = preg_replace( '/[^0-9+]/', '', $value );
	$value = preg_replace( '/^00/', '', $value );

	return ltrim( (string) $value, '+' );
}

/**
 * Sanitize a URL or a relative path.
 *
 * @param string $value Raw value.
 *
 * @return string
 */
function hello_elementor_child_sanitize_url_or_path( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return '';
	}

	if ( 0 === strpos( $value, '/' ) && 0 !== strpos( $value, '//' ) ) {
		return '/' . ltrim( sanitize_text_field( $value ), '/' );
	}

	return esc_url_raw( $value );
}

/**
 * Sanitize a floating button size.
 *
 * @param mixed $value   Raw size value.
 * @param int   $default Default size in pixels.
 *
 * @return string
 */
function hello_elementor_child_sanitize_button_size( $value, $default = 52 ) {
	$size = absint( $value );

	if ( 0 === $size ) {
		$size = absint( $default );
	}

	$size = max( 36, min( 96, $size ) );

	return (string) $size;
}

/**
 * Sanitize a color option with a safe fallback.
 *
 * @param mixed  $value    Raw color value.
 * @param string $fallback Fallback hex color.
 *
 * @return string
 */
function hello_elementor_child_sanitize_color_option( $value, $fallback ) {
	$color = sanitize_hex_color( (string) $value );

	if ( ! $color ) {
		$color = sanitize_hex_color( (string) $fallback );
	}

	return $color ? $color : '#000000';
}

/**
 * Sanitize all theme options before saving.
 *
 * @param array $input Raw posted options.
 *
 * @return array
 */
function hello_elementor_child_sanitize_options( $input ) {
	$input    = is_array( $input ) ? $input : [];
	$defaults = hello_elementor_child_default_options();
	$output   = [];

	$text_fields = [
		'back_to_top_icon',
		'back_to_top_label',
		'whatsapp_label',
		'whatsapp_message',
		'back_button_icon',
		'back_button_text',
		'powerby_label',
		'powerby_link_text',
		'login_title',
		'login_subtitle',
		'login_back_text',
	];

	foreach ( $text_fields as $field ) {
		$output[ $field ] = isset( $input[ $field ] ) ? sanitize_text_field( wp_unslash( $input[ $field ] ) ) : $defaults[ $field ];
	}

	foreach ( $input as $field => $value ) {
		if ( preg_match( '/^(back_to_top_label|whatsapp_label|whatsapp_message|back_button_text|powerby_label|login_title|login_subtitle|login_back_text)_(es|en|fr|it|pt|de)$/', (string) $field ) ) {
			$output[ $field ] = sanitize_text_field( wp_unslash( $value ) );
		}
	}

	$output['back_to_top_enabled']        = ! empty( $input['back_to_top_enabled'] ) ? '1' : '0';
	$output['back_to_top_size']           = isset( $input['back_to_top_size'] ) ? hello_elementor_child_sanitize_button_size( wp_unslash( $input['back_to_top_size'] ), 52 ) : $defaults['back_to_top_size'];
	$output['back_to_top_bg_color']       = isset( $input['back_to_top_bg_color'] ) ? hello_elementor_child_sanitize_color_option( wp_unslash( $input['back_to_top_bg_color'] ), $defaults['back_to_top_bg_color'] ) : $defaults['back_to_top_bg_color'];
	$output['back_to_top_text_color']     = isset( $input['back_to_top_text_color'] ) ? hello_elementor_child_sanitize_color_option( wp_unslash( $input['back_to_top_text_color'] ), $defaults['back_to_top_text_color'] ) : $defaults['back_to_top_text_color'];
	$output['back_to_top_border_color']   = isset( $input['back_to_top_border_color'] ) ? hello_elementor_child_sanitize_color_option( wp_unslash( $input['back_to_top_border_color'] ), $defaults['back_to_top_border_color'] ) : $defaults['back_to_top_border_color'];
	$output['back_to_top_hover_bg_color'] = isset( $input['back_to_top_hover_bg_color'] ) ? hello_elementor_child_sanitize_color_option( wp_unslash( $input['back_to_top_hover_bg_color'] ), $defaults['back_to_top_hover_bg_color'] ) : $defaults['back_to_top_hover_bg_color'];
	$output['whatsapp_enabled']           = ! empty( $input['whatsapp_enabled'] ) ? '1' : '0';
	$output['whatsapp_size']              = isset( $input['whatsapp_size'] ) ? hello_elementor_child_sanitize_button_size( wp_unslash( $input['whatsapp_size'] ), 52 ) : $defaults['whatsapp_size'];
	$output['whatsapp_number']            = isset( $input['whatsapp_number'] ) ? hello_elementor_child_sanitize_whatsapp_number( wp_unslash( $input['whatsapp_number'] ) ) : '';
	$output['powerby_target_blank']       = ! empty( $input['powerby_target_blank'] ) ? '1' : '0';
	$output['powerby_url']                = isset( $input['powerby_url'] ) ? esc_url_raw( wp_unslash( $input['powerby_url'] ) ) : $defaults['powerby_url'];
	$output['back_button_fallback']       = isset( $input['back_button_fallback'] ) ? hello_elementor_child_sanitize_url_or_path( wp_unslash( $input['back_button_fallback'] ) ) : '';
	$output['contact_url']                = isset( $input['contact_url'] ) ? hello_elementor_child_sanitize_url_or_path( wp_unslash( $input['contact_url'] ) ) : $defaults['contact_url'];

	return $output;
}

/**
 * Register settings used by the child theme options page.
 *
 * @return void
 */
function hello_elementor_child_register_settings() {
	register_setting(
		'hello_elementor_child_options_group',
		'hello_elementor_child_options',
		[
			'type'              => 'array',
			'sanitize_callback' => 'hello_elementor_child_sanitize_options',
			'default'           => hello_elementor_child_default_options(),
		]
	);
}
add_action( 'admin_init', 'hello_elementor_child_register_settings' );

/**
 * Add the options page under Appearance.
 *
 * @return void
 */
function hello_elementor_child_add_options_page() {
	add_theme_page(
		__( 'Opciones del tema', 'hello-elementor-child' ),
		__( 'Opciones del tema', 'hello-elementor-child' ),
		'manage_options',
		'hello-child-options',
		'hello_elementor_child_render_options_page'
	);
}
add_action( 'admin_menu', 'hello_elementor_child_add_options_page' );

/**
 * Print one input field for the options page.
 *
 * @param string $key     Option key.
 * @param string $label   Field label.
 * @param string $type    Input type.
 * @param string $help    Optional help text.
 *
 * @return void
 */
function hello_elementor_child_options_field( $key, $label, $type = 'text', $help = '' ) {
	$value = hello_elementor_child_get_option( $key, '' );
	$class = 'hello-child-input';
	$attrs = '';

	if ( 'number' === $type ) {
		$class .= ' hello-child-input--small';
		$attrs  = ' min="36" max="96" step="1"';
	} elseif ( 'color' === $type ) {
		$class .= ' hello-child-input--color';
	}
	?>
	<div class="hello-child-field hello-child-field--<?php echo esc_attr( $type ); ?>">
		<div class="hello-child-field__label">
			<label for="hello-child-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
		</div>
		<div class="hello-child-field__control">
			<?php if ( 'checkbox' === $type ) : ?>
				<label class="hello-child-switch">
					<input type="checkbox" id="hello-child-<?php echo esc_attr( $key ); ?>" name="hello_elementor_child_options[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( '1', (string) $value ); ?>>
					<span class="hello-child-switch__slider" aria-hidden="true"></span>
					<span class="hello-child-switch__text"><?php echo esc_html__( 'Activo', 'hello-elementor-child' ); ?></span>
				</label>
			<?php else : ?>
				<input type="<?php echo esc_attr( $type ); ?>" id="hello-child-<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $class ); ?>" name="hello_elementor_child_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php if ( 'number' === $type ) : ?>
					<span class="hello-child-unit"><?php echo esc_html__( 'px', 'hello-elementor-child' ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ( $help ) : ?>
				<p class="hello-child-description"><?php echo esc_html( $help ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Print admin styles for the options page.
 *
 * @return void
 */
function hello_elementor_child_print_options_styles() {
	?>
	<style>
		.hello-child-options-wrap {
			--hello-admin-bg: #f6f7fb;
			--hello-admin-card: #ffffff;
			--hello-admin-border: #e4e7ef;
			--hello-admin-text: #111827;
			--hello-admin-muted: #667085;
			--hello-admin-primary: #1c1c1e;
			--hello-admin-radius: 18px;
			max-width: 1180px;
		}
		.hello-child-options-wrap h1 {
			font-size: 32px;
			font-weight: 750;
			letter-spacing: -0.03em;
			margin-bottom: 8px;
			color: var(--hello-admin-text);
		}
		.hello-child-options-wrap .hello-child-page-intro {
			max-width: 780px;
			font-size: 15px;
			color: var(--hello-admin-muted);
			margin-bottom: 22px;
		}
		.hello-child-options-wrap code {
			background: #f2f4f7;
			border: 1px solid #e5e7eb;
			padding: 2px 7px;
			border-radius: 8px;
			font-size: 12px;
		}
		.hello-child-panel {
			background: var(--hello-admin-card);
			border: 1px solid var(--hello-admin-border);
			border-radius: var(--hello-admin-radius);
			box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);
			margin: 18px 0;
			overflow: hidden;
		}
		.hello-child-panel__header {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 16px;
			padding: 22px 24px 14px;
			border-bottom: 1px solid #f0f2f7;
		}
		.hello-child-panel__header h2 {
			font-size: 19px;
			line-height: 1.25;
			margin: 0;
			color: var(--hello-admin-text);
		}
		.hello-child-panel__header p,
		.hello-child-panel__body > p {
			margin: 8px 0 0;
			color: var(--hello-admin-muted);
		}
		.hello-child-panel__body {
			padding: 20px 24px 24px;
		}
		.hello-child-help-grid {
			display: grid;
			gap: 16px;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			margin-top: 18px;
		}
		.hello-child-help-card {
			border: 1px solid #edf0f5;
			border-radius: 16px;
			padding: 16px;
			background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
		}
		.hello-child-help-card h3 {
			font-size: 15px;
			margin: 0 0 10px;
			color: var(--hello-admin-text);
		}
		.hello-child-help-card ul {
			margin: 0 0 0 18px;
			list-style: disc;
			color: var(--hello-admin-muted);
		}
		.hello-child-help-card li { margin-bottom: 6px; }
		.hello-child-fields {
			display: grid;
			gap: 0;
			border: 1px solid #edf0f5;
			border-radius: 16px;
			overflow: hidden;
		}
		.hello-child-field {
			display: grid;
			grid-template-columns: minmax(190px, 260px) minmax(0, 1fr);
			gap: 18px;
			align-items: center;
			padding: 16px;
			background: #fff;
			border-bottom: 1px solid #edf0f5;
		}
		.hello-child-field:last-child { border-bottom: 0; }
		.hello-child-field__label label {
			font-weight: 650;
			color: var(--hello-admin-text);
		}
		.hello-child-input {
			width: min(100%, 520px);
			border: 1px solid #d0d5dd !important;
			border-radius: 12px !important;
			padding: 9px 12px !important;
			min-height: 40px;
			box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
		}
		.hello-child-input--small { width: 110px; }
		.hello-child-input--color {
			width: 66px;
			padding: 3px !important;
			min-height: 42px;
		}
		.hello-child-description {
			margin: 8px 0 0;
			color: var(--hello-admin-muted);
			font-size: 13px;
		}
		.hello-child-unit {
			margin-left: 7px;
			color: var(--hello-admin-muted);
		}
		.hello-child-switch {
			display: inline-flex;
			align-items: center;
			gap: 10px;
			font-weight: 600;
			color: var(--hello-admin-text);
		}
		.hello-child-switch input {
			position: absolute;
			opacity: 0;
			pointer-events: none;
		}
		.hello-child-switch__slider {
			position: relative;
			display: inline-block;
			width: 48px;
			height: 28px;
			border-radius: 999px;
			background: #d0d5dd;
			transition: background 180ms ease;
		}
		.hello-child-switch__slider::after {
			content: '';
			position: absolute;
			top: 4px;
			left: 4px;
			width: 20px;
			height: 20px;
			border-radius: 999px;
			background: #fff;
			box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2);
			transition: transform 180ms ease;
		}
		.hello-child-switch input:checked + .hello-child-switch__slider { background: var(--hello-admin-primary); }
		.hello-child-switch input:checked + .hello-child-switch__slider::after { transform: translateX(20px); }
		.hello-child-tabs {
			margin-top: 18px;
			border: 1px solid #edf0f5;
			border-radius: 18px;
			background: #fff;
			overflow: hidden;
		}
		.hello-child-tabs__nav {
			display: flex;
			gap: 8px;
			padding: 12px;
			background: #f8fafc;
			border-bottom: 1px solid #edf0f5;
			overflow-x: auto;
		}
		.hello-child-tab-button {
			border: 1px solid transparent;
			border-radius: 999px;
			background: transparent;
			color: #475467;
			cursor: pointer;
			font-weight: 650;
			padding: 9px 14px;
			white-space: nowrap;
		}
		.hello-child-tab-button.is-active {
			background: #111827;
			color: #fff;
			box-shadow: 0 8px 22px rgba(17, 24, 39, 0.16);
		}
		.hello-child-tab-panel { display: none; padding: 18px; }
		.hello-child-tab-panel.is-active { display: block; }
		.hello-child-save-area { margin-top: 22px; }
		.hello-child-save-area .button-primary {
			border-radius: 999px;
			padding: 6px 18px;
			min-height: 40px;
			font-weight: 650;
		}
		@media (max-width: 782px) {
			.hello-child-field { grid-template-columns: 1fr; gap: 8px; }
			.hello-child-panel__header { display: block; }
		}
	</style>
	<?php
}

/**
 * Render configurable translation fields as language tabs.
 *
 * @return void
 */
function hello_elementor_child_render_translation_fields() {
	$languages = [
		'es' => __( 'Español', 'hello-elementor-child' ),
		'en' => __( 'Inglés', 'hello-elementor-child' ),
		'fr' => __( 'Francés', 'hello-elementor-child' ),
		'it' => __( 'Italiano', 'hello-elementor-child' ),
		'pt' => __( 'Portugués', 'hello-elementor-child' ),
		'de' => __( 'Alemán', 'hello-elementor-child' ),
	];

	$fields = [
		'back_to_top_label' => __( 'Texto de Back to Top', 'hello-elementor-child' ),
		'whatsapp_label'    => __( 'Texto accesible de WhatsApp', 'hello-elementor-child' ),
		'whatsapp_message'  => __( 'Mensaje inicial de WhatsApp', 'hello-elementor-child' ),
		'back_button_text'  => __( 'Texto del botón regresar', 'hello-elementor-child' ),
		'powerby_label'     => __( 'Texto antes del año en Powerby', 'hello-elementor-child' ),
		'login_title'       => __( 'Título del login', 'hello-elementor-child' ),
		'login_subtitle'    => __( 'Subtítulo del login', 'hello-elementor-child' ),
		'login_back_text'   => __( 'Texto para volver al sitio web', 'hello-elementor-child' ),
	];
	?>
	<div class="hello-child-tabs" data-hello-child-tabs>
		<div class="hello-child-tabs__nav" role="tablist" aria-label="<?php echo esc_attr__( 'Idiomas configurables', 'hello-elementor-child' ); ?>">
			<?php $index = 0; ?>
			<?php foreach ( $languages as $lang => $language_label ) : ?>
				<button type="button" class="hello-child-tab-button<?php echo 0 === $index ? ' is-active' : ''; ?>" data-hello-child-tab="<?php echo esc_attr( $lang ); ?>" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-controls="hello-child-tab-panel-<?php echo esc_attr( $lang ); ?>">
					<?php echo esc_html( $language_label ); ?>
				</button>
				<?php ++$index; ?>
			<?php endforeach; ?>
		</div>

		<?php $index = 0; ?>
		<?php foreach ( $languages as $lang => $language_label ) : ?>
			<div id="hello-child-tab-panel-<?php echo esc_attr( $lang ); ?>" class="hello-child-tab-panel<?php echo 0 === $index ? ' is-active' : ''; ?>" data-hello-child-tab-panel="<?php echo esc_attr( $lang ); ?>" role="tabpanel">
				<div class="hello-child-fields">
					<?php foreach ( $fields as $field => $field_label ) : ?>
						<?php $key = $field . '_' . $lang; ?>
						<div class="hello-child-field">
							<div class="hello-child-field__label">
								<label for="hello-child-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field_label ); ?></label>
							</div>
							<div class="hello-child-field__control">
								<input type="text" id="hello-child-<?php echo esc_attr( $key ); ?>" class="hello-child-input" name="hello_elementor_child_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( hello_elementor_child_get_option( $key, '' ) ); ?>">
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php ++$index; ?>
		<?php endforeach; ?>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('[data-hello-child-tabs]').forEach(function (tabs) {
				var buttons = tabs.querySelectorAll('[data-hello-child-tab]');
				var panels = tabs.querySelectorAll('[data-hello-child-tab-panel]');

				buttons.forEach(function (button) {
					button.addEventListener('click', function () {
						var target = button.getAttribute('data-hello-child-tab');

						buttons.forEach(function (item) {
							var active = item === button;
							item.classList.toggle('is-active', active);
							item.setAttribute('aria-selected', active ? 'true' : 'false');
						});

						panels.forEach(function (panel) {
							panel.classList.toggle('is-active', panel.getAttribute('data-hello-child-tab-panel') === target);
						});
					});
				});
			});
		});
	</script>
	<?php
}

/**
 * Render the child theme options page.
 *
 * @return void
 */
function hello_elementor_child_render_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap hello-child-options-wrap">
		<h1><?php echo esc_html__( 'Opciones del tema', 'hello-elementor-child' ); ?></h1>
		<p class="hello-child-page-intro"><?php echo esc_html__( 'Administra desde aquí las herramientas visuales, los accesos y los textos configurables incluidos en este tema hijo.', 'hello-elementor-child' ); ?></p>

		<?php hello_elementor_child_print_options_styles(); ?>

		<div class="hello-child-panel">
			<div class="hello-child-panel__header">
				<div>
					<h2><?php echo esc_html__( 'Guía rápida de uso', 'hello-elementor-child' ); ?></h2>
					<p><?php echo esc_html__( 'Estos ajustes controlan pequeñas herramientas del tema. Los cambios se guardan en la base de datos de WordPress y no requieren editar código.', 'hello-elementor-child' ); ?></p>
				</div>
			</div>
			<div class="hello-child-panel__body">
				<div class="hello-child-help-grid">
					<div class="hello-child-help-card">
						<h3><?php echo esc_html__( 'Botones flotantes', 'hello-elementor-child' ); ?></h3>
						<ul>
							<li><?php echo esc_html__( 'Back to Top aparece globalmente y devuelve al visitante al inicio de la página actual.', 'hello-elementor-child' ); ?></li>
							<li><?php echo esc_html__( 'Back to Top permite configurar tamaño, icono, color de fondo, color del icono, color del borde y color al pasar el mouse.', 'hello-elementor-child' ); ?></li>
							<li><?php echo esc_html__( 'WhatsApp aparece encima de Back to Top cuando está activo y tiene un número configurado.', 'hello-elementor-child' ); ?></li>
							<li><?php echo esc_html__( 'El icono de WhatsApp no es editable: se usa el vector fijo incluido en assets/img/whatsapp.svg.', 'hello-elementor-child' ); ?></li>
							<li><?php echo esc_html__( 'Ambos botones se mantienen circulares aunque cambie el tamaño.', 'hello-elementor-child' ); ?></li>
						</ul>
					</div>
					<div class="hello-child-help-card">
						<h3><?php echo esc_html__( 'Shortcodes disponibles', 'hello-elementor-child' ); ?></h3>
						<p><code>[powerby]</code> <?php echo esc_html__( 'muestra el año actual y el enlace de crédito configurado.', 'hello-elementor-child' ); ?></p>
						<p><code>[back]</code> <?php echo esc_html__( 'muestra un botón para regresar a la página anterior o a una URL de respaldo.', 'hello-elementor-child' ); ?></p>
						<p><?php echo esc_html__( 'Ejemplos:', 'hello-elementor-child' ); ?> <code>[powerby text="fullcom"]</code> <code>[back text="Volver" fallback="/contacto/"]</code></p>
					</div>
					<div class="hello-child-help-card">
						<h3><?php echo esc_html__( 'Traducciones administrables', 'hello-elementor-child' ); ?></h3>
						<p><?php echo esc_html__( 'Los campos generales funcionan como respaldo. En la sección Idiomas puedes completar textos por idioma usando pestañas.', 'hello-elementor-child' ); ?></p>
						<p><?php echo esc_html__( 'Si el sitio está en español, el tema intenta usar los textos de Español. Si un campo está vacío, usa el valor general.', 'hello-elementor-child' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'hello_elementor_child_options_group' ); ?>

			<div class="hello-child-panel">
				<div class="hello-child-panel__header">
					<div>
						<h2><?php echo esc_html__( 'Back to Top', 'hello-elementor-child' ); ?></h2>
						<p><?php echo esc_html__( 'Configura el botón flotante que permite volver al inicio de la página.', 'hello-elementor-child' ); ?></p>
					</div>
				</div>
				<div class="hello-child-panel__body">
					<div class="hello-child-fields">
						<?php
						hello_elementor_child_options_field( 'back_to_top_enabled', __( 'Mostrar botón', 'hello-elementor-child' ), 'checkbox' );
						hello_elementor_child_options_field( 'back_to_top_icon', __( 'Icono', 'hello-elementor-child' ), 'text', __( 'Usa un carácter simple, por ejemplo ↑, ⬆ o un emoji.', 'hello-elementor-child' ) );
						hello_elementor_child_options_field( 'back_to_top_label', __( 'Texto accesible', 'hello-elementor-child' ), 'text', __( 'Este texto se usa para accesibilidad y lectores de pantalla.', 'hello-elementor-child' ) );
						hello_elementor_child_options_field( 'back_to_top_size', __( 'Tamaño del botón', 'hello-elementor-child' ), 'number', __( 'Rango permitido: 36px a 96px. El botón siempre queda circular.', 'hello-elementor-child' ) );
						hello_elementor_child_options_field( 'back_to_top_bg_color', __( 'Color de fondo', 'hello-elementor-child' ), 'color' );
						hello_elementor_child_options_field( 'back_to_top_text_color', __( 'Color del icono', 'hello-elementor-child' ), 'color' );
						hello_elementor_child_options_field( 'back_to_top_border_color', __( 'Color del borde', 'hello-elementor-child' ), 'color' );
						hello_elementor_child_options_field( 'back_to_top_hover_bg_color', __( 'Color de fondo al pasar el mouse', 'hello-elementor-child' ), 'color' );
						?>
					</div>
				</div>
			</div>

			<div class="hello-child-panel">
				<div class="hello-child-panel__header">
					<div>
						<h2><?php echo esc_html__( 'Botón flotante de WhatsApp', 'hello-elementor-child' ); ?></h2>
						<p><?php echo esc_html__( 'Se imprime globalmente encima de Back to Top. El icono es fijo y usa el vector incluido en el tema.', 'hello-elementor-child' ); ?></p>
					</div>
				</div>
				<div class="hello-child-panel__body">
					<div class="hello-child-fields">
						<?php
						hello_elementor_child_options_field( 'whatsapp_enabled', __( 'Mostrar botón', 'hello-elementor-child' ), 'checkbox' );
						hello_elementor_child_options_field( 'whatsapp_number', __( 'Número de WhatsApp', 'hello-elementor-child' ), 'text', __( 'Usa formato internacional sin espacios ni signo +. Ejemplo: 50255555555.', 'hello-elementor-child' ) );
						hello_elementor_child_options_field( 'whatsapp_size', __( 'Tamaño del botón', 'hello-elementor-child' ), 'number', __( 'Rango permitido: 36px a 96px. El botón siempre queda circular.', 'hello-elementor-child' ) );
						hello_elementor_child_options_field( 'whatsapp_label', __( 'Texto accesible', 'hello-elementor-child' ), 'text', __( 'Este texto se usa para accesibilidad y lectores de pantalla.', 'hello-elementor-child' ) );
						hello_elementor_child_options_field( 'whatsapp_message', __( 'Mensaje inicial', 'hello-elementor-child' ), 'text', __( 'Texto opcional que se precarga en WhatsApp. Puedes usar {site_name}.', 'hello-elementor-child' ) );
						?>
					</div>
				</div>
			</div>

			<div class="hello-child-panel">
				<div class="hello-child-panel__header">
					<div>
						<h2><?php echo esc_html__( 'Shortcode regresar', 'hello-elementor-child' ); ?></h2>
						<p><code>[back]</code> <?php echo esc_html__( 'muestra un botón para volver a la página anterior.', 'hello-elementor-child' ); ?></p>
					</div>
				</div>
				<div class="hello-child-panel__body">
					<div class="hello-child-fields">
						<?php
						hello_elementor_child_options_field( 'back_button_icon', __( 'Icono', 'hello-elementor-child' ), 'text', __( 'Usa un carácter simple, por ejemplo ←, ‹ o ↩.', 'hello-elementor-child' ) );
						hello_elementor_child_options_field( 'back_button_text', __( 'Texto', 'hello-elementor-child' ), 'text' );
						hello_elementor_child_options_field( 'back_button_fallback', __( 'URL de respaldo', 'hello-elementor-child' ), 'text', __( 'Se usa cuando el navegador no tiene una página anterior. Déjalo vacío para usar la página de inicio.', 'hello-elementor-child' ) );
						?>
					</div>
				</div>
			</div>

			<div class="hello-child-panel">
				<div class="hello-child-panel__header">
					<div>
						<h2><?php echo esc_html__( 'Shortcode Powerby', 'hello-elementor-child' ); ?></h2>
						<p><code>[powerby]</code> <?php echo esc_html__( 'muestra el año actual y el enlace configurado.', 'hello-elementor-child' ); ?></p>
					</div>
				</div>
				<div class="hello-child-panel__body">
					<div class="hello-child-fields">
						<?php
						hello_elementor_child_options_field( 'powerby_label', __( 'Texto antes del año', 'hello-elementor-child' ), 'text' );
						hello_elementor_child_options_field( 'powerby_link_text', __( 'Texto del enlace', 'hello-elementor-child' ), 'text' );
						hello_elementor_child_options_field( 'powerby_url', __( 'URL del enlace', 'hello-elementor-child' ), 'url' );
						hello_elementor_child_options_field( 'powerby_target_blank', __( 'Abrir en nueva pestaña', 'hello-elementor-child' ), 'checkbox' );
						?>
					</div>
				</div>
			</div>

			<div class="hello-child-panel">
				<div class="hello-child-panel__header">
					<div>
						<h2><?php echo esc_html__( 'Login y redirecciones', 'hello-elementor-child' ); ?></h2>
						<p><?php echo esc_html__( 'Configura los textos del login personalizado y la URL de respaldo para redirecciones.', 'hello-elementor-child' ); ?></p>
					</div>
				</div>
				<div class="hello-child-panel__body">
					<div class="hello-child-fields">
						<?php
						hello_elementor_child_options_field( 'login_title', __( 'Título del login', 'hello-elementor-child' ), 'text' );
						hello_elementor_child_options_field( 'login_subtitle', __( 'Subtítulo del login', 'hello-elementor-child' ), 'text', __( 'Puedes usar {site_name}.', 'hello-elementor-child' ) );
						hello_elementor_child_options_field( 'login_back_text', __( 'Texto para volver al sitio web', 'hello-elementor-child' ), 'text' );
						hello_elementor_child_options_field( 'contact_url', __( 'URL de contacto/respaldo', 'hello-elementor-child' ), 'text' );
						?>
					</div>
				</div>
			</div>

			<div class="hello-child-panel">
				<div class="hello-child-panel__header">
					<div>
						<h2><?php echo esc_html__( 'Idiomas', 'hello-elementor-child' ); ?></h2>
						<p><?php echo esc_html__( 'Completa los textos por idioma usando las pestañas. Si un campo queda vacío, el tema usa el texto general configurado arriba.', 'hello-elementor-child' ); ?></p>
					</div>
				</div>
				<div class="hello-child-panel__body">
					<p><?php echo esc_html__( 'Funcionamiento: WordPress detecta el idioma activo, por ejemplo es_GT o en_US. El tema intenta usar primero una opción específica del idioma y luego el texto general.', 'hello-elementor-child' ); ?></p>
					<p><?php echo esc_html__( 'Para textos técnicos que no están en esta pantalla, usa los archivos .po/.mo de la carpeta languages o un plugin como Loco Translate.', 'hello-elementor-child' ); ?></p>
					<?php hello_elementor_child_render_translation_fields(); ?>
				</div>
			</div>

			<div class="hello-child-save-area">
				<?php submit_button( __( 'Guardar opciones del tema', 'hello-elementor-child' ) ); ?>
			</div>
		</form>
	</div>
	<?php
}
