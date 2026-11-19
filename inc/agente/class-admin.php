<?php
/**
 * La pantalla del agente, con el diseño del tema.
 *
 * Es el único archivo del agente que cambia al meterlo en el tema hijo. Los
 * otros doce son copia exacta del plugin, a propósito: así una corrección en
 * el agente se trae tal cual. Lo que cambia aquí son tres cosas y ninguna
 * toca la lógica —menu(), pantalla() y volver()—, porque el formulario deja
 * de verse como una pantalla suelta de WordPress y pasa a verse como el resto
 * del tema: los mismos recuadros, los mismos campos, el mismo interruptor.
 *
 * Por qué es una página aparte y no un bloque más dentro de «Opciones del
 * tema»: aquella es un único formulario grande que guarda todo de una vez
 * (options.php), y esta necesita formularios propios con botones que hacen
 * cosas —registrar, mandar el reporte, probar el correo—. Un formulario
 * dentro de otro no es HTML válido. Y además lo primero que se viene a mirar
 * aquí es si el sitio está conectado: eso tiene que verse al entrar, no al
 * final de una página de dos mil líneas.
 *
 * @package HelloElementorChild
 */

defined( 'ABSPATH' ) || exit;

class SWA_Admin {

	/** El identificador de la página, dentro de Apariencia. */
	public const PAGINA = 'hello-child-agente';

	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
		add_action( 'admin_post_swa_guardar', [ __CLASS__, 'guardar' ] );
		add_action( 'admin_post_swa_probar_correo', [ __CLASS__, 'probar_correo' ] );
		add_action( 'admin_post_swa_confirmar_correo', [ __CLASS__, 'confirmar_correo' ] );
		add_action( 'admin_post_swa_registrar', [ __CLASS__, 'registrar' ] );
		add_action( 'admin_post_swa_reportar', [ __CLASS__, 'reportar' ] );
		add_action( 'admin_post_swa_respaldo', [ __CLASS__, 'respaldo' ] );
	}

	public static function menu(): void {
		add_theme_page(
			__( 'Conexión al panel', 'hello-elementor-child' ),
			__( 'Conexión al panel', 'hello-elementor-child' ),
			'manage_options',
			self::PAGINA,
			[ __CLASS__, 'pantalla' ]
		);
	}

	private static function url( string $accion ): string {
		return wp_nonce_url( admin_url( 'admin-post.php?action=' . $accion ), $accion );
	}

	/** Los estilos son los de la página de opciones del tema, no otros. */
	private static function estilos(): void {
		if ( function_exists( 'hello_elementor_child_print_options_styles' ) ) {
			hello_elementor_child_print_options_styles();
		}
	}

	/** Abre un recuadro con título y bajada, como los del tema. */
	private static function panel_abrir( string $titulo, string $bajada = '' ): void {
		?>
		<div class="hello-child-panel">
			<div class="hello-child-panel__header">
				<div>
					<h2><?php echo esc_html( $titulo ); ?></h2>
					<?php if ( '' !== $bajada ) : ?>
						<p><?php echo esc_html( $bajada ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<div class="hello-child-panel__body">
		<?php
	}

	private static function panel_cerrar(): void {
		echo '</div></div>';
	}

	/** Una fila de formulario con la rejilla del tema. */
	private static function campo_abrir( string $id, string $etiqueta, string $tipo = 'text' ): void {
		?>
		<div class="hello-child-field hello-child-field--<?php echo esc_attr( $tipo ); ?>">
			<div class="hello-child-field__label">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $etiqueta ); ?></label>
			</div>
			<div class="hello-child-field__control">
		<?php
	}

	private static function campo_cerrar(): void {
		echo '</div></div>';
	}

	public static function pantalla(): void {
		$a      = SWA_Ajustes::todos();
		$correo = SWA_Correo::estado();
		$puede  = SWA_Correo::puede_enviar();

		// ── Pestañas ────────────────────────────────────────────────────────
		//
		// Eran cuatro bloques en una sola página larga. «Crear tabs
		// independientes mejor para una mejor administración»: cada cosa se
		// toca en un momento distinto —la conexión al instalar, el correo al
		// comprobarlo, los dos pasos al encenderlos— y verlas las cuatro a la
		// vez solo sirve para tener que buscar.
		$pestanas = [
			'conexion' => __( 'Conexión', 'hello-elementor-child' ),
			'correo'   => __( 'Correo', 'hello-elementor-child' ),
			'dospasos' => __( 'Dos pasos', 'hello-elementor-child' ),
			'respaldo' => __( 'Códigos de respaldo', 'hello-elementor-child' ),
		];

		$tab = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : 'conexion';

		if ( ! isset( $pestanas[ $tab ] ) ) {
			$tab = 'conexion';
		}

		$url_tab = static fn( string $t ) => add_query_arg(
			[ 'page' => 'hello-child-agente', 'tab' => $t ],
			admin_url( 'themes.php' )
		);
		?>
		<div class="wrap hello-child-options-wrap">
			<h1><?php echo esc_html__( 'Conexión al panel', 'hello-elementor-child' ); ?></h1>
			<p class="hello-child-page-intro">
				<?php echo esc_html__( 'Este sitio le cuenta al Panel Kinsta quién tiene acceso de administrador, y puede pedir un código por correo al entrar. El agente viene dentro del tema: no hay nada que instalar aparte.', 'hello-elementor-child' ); ?>
			</p>

			<?php self::estilos(); ?>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $pestanas as $clave => $nombre ) : ?>
					<a class="nav-tab <?php echo $clave === $tab ? 'nav-tab-active' : ''; ?>"
						href="<?php echo esc_url( $url_tab( $clave ) ); ?>"><?php
						echo esc_html( $nombre );
					?></a>
				<?php endforeach; ?>
			</h2>

			<?php if ( ! empty( $_GET['swa_msg'] ) ) : ?>
				<?php
				[ $tipo, $texto ] = array_pad( explode( ':', sanitize_text_field( wp_unslash( (string) $_GET['swa_msg'] ) ), 2 ), 2, '' );
				printf( '<div class="notice notice-%s"><p>%s</p></div>', 'error' === $tipo ? 'error' : 'success', esc_html( urldecode( $texto ) ) );
				?>
			<?php endif; ?>

			<?php if ( SWA_Dos_Factores::apagado() ) : ?>
				<div class="notice notice-warning"><p>
					<strong><?php echo esc_html__( 'La verificación en dos pasos está apagada por constante.', 'hello-elementor-child' ); ?></strong>
					<?php echo esc_html__( 'Hay un', 'hello-elementor-child' ); ?> <code>define( 'SWA_2FA_APAGADO', true )</code>
					<?php echo esc_html__( 'en wp-config.php. Es la salida de emergencia; conviene quitarla cuando ya no haga falta.', 'hello-elementor-child' ); ?>
				</p></div>
			<?php endif; ?>

			<?php
			// ── 1. La conexión ──────────────────────────────────────────────
			if ( 'conexion' === $tab ) :
			self::panel_abrir(
				__( 'Conexión con el panel', 'hello-elementor-child' ),
				__( 'La dirección del panel y la clave con la que este sitio se presenta la primera vez.', 'hello-elementor-child' )
			);
			?>
				<?php if ( ! $a['panel_activo'] ) : ?>
					<div class="notice notice-warning inline"><p>
						<strong><?php echo esc_html__( 'El reporte al panel está apagado.', 'hello-elementor-child' ); ?></strong>
						<?php echo esc_html__( 'Este sitio no le manda nada al panel ni le pregunta nada. El código por correo al entrar y el silencio de los avisos de WordPress siguen funcionando igual.', 'hello-elementor-child' ); ?>
					</p></div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="swa_guardar">
					<input type="hidden" name="seccion" value="conexion">
					<?php wp_nonce_field( 'swa_guardar' ); ?>

					<div class="hello-child-fields">
						<?php self::campo_abrir( 'panel_activo', __( 'Reportar al panel', 'hello-elementor-child' ), 'check' ); ?>
							<label>
								<input type="checkbox" name="panel_activo" id="panel_activo" value="1"
									<?php checked( (int) $a['panel_activo'], 1 ); ?>>
								<?php echo esc_html__( 'Este sitio le reporta al Panel Kinsta', 'hello-elementor-child' ); ?>
							</label>
							<p class="description">
								<?php echo esc_html__( 'Apagalo si este sitio no está en el servidor del panel: deja de mandar su reporte diario y de preguntar si hay algo pendiente. Lo demás del tema no cambia.', 'hello-elementor-child' ); ?>
							</p>
						<?php self::campo_cerrar(); ?>

						<?php self::campo_abrir( 'panel_url', __( 'Dirección del panel', 'hello-elementor-child' ), 'url' ); ?>
							<input type="url" class="hello-child-input" name="panel_url" id="panel_url"
								value="<?php echo esc_attr( $a['panel_url'] ); ?>" placeholder="https://...">
						<?php self::campo_cerrar(); ?>

						<?php self::campo_abrir( 'clave_registro', __( 'Clave de registro', 'hello-elementor-child' ), 'password' ); ?>
							<input type="password" class="hello-child-input" name="clave_registro" id="clave_registro" value="" autocomplete="off"
								placeholder="<?php echo '' !== $a['clave_registro'] ? esc_attr__( '•••••••• (ya configurada)', 'hello-elementor-child' ) : ''; ?>">
							<p class="hello-child-description">
								<?php echo esc_html__( 'En el panel: Panel Kinsta → Ajustes, en el recuadro «Conectar los sitios». Ahí está también la dirección que va arriba.', 'hello-elementor-child' ); ?>
							</p>
							<p class="hello-child-description">
								<?php echo esc_html__( 'Solo se usa en este momento: al registrarse, el panel le entrega a este sitio un secreto propio, distinto en cada sitio, y de ahí en adelante este sitio firma sus reportes con el suyo. Ese secreto no hay que copiarlo ni verlo nunca.', 'hello-elementor-child' ); ?>
							</p>
						<?php self::campo_cerrar(); ?>

						<?php self::campo_abrir( 'swa-estado', __( 'Estado', 'hello-elementor-child' ), 'estado' ); ?>
							<?php if ( SWA_Ajustes::registrado() ) : ?>
								<p style="margin:0;color:#008a20;font-weight:600">
									<?php echo esc_html__( '✓ Registrado en el panel', 'hello-elementor-child' ); ?>
								</p>
								<?php $ult = get_option( 'swa_ultimo_reporte', '' ); ?>
								<p class="hello-child-description">
									<?php echo esc_html__( 'Último reporte:', 'hello-elementor-child' ); ?>
									<?php echo esc_html( $ult ?: __( 'ninguno todavía', 'hello-elementor-child' ) ); ?>
								</p>
								<?php $err = get_option( 'swa_ultimo_error', '' ); ?>
								<?php if ( $err ) : ?>
									<p class="hello-child-description" style="color:#d63638">
										<?php echo esc_html__( 'Último error:', 'hello-elementor-child' ); ?> <?php echo esc_html( $err ); ?>
									</p>
								<?php endif; ?>
							<?php else : ?>
								<p style="margin:0;color:#d63638;font-weight:600">
									<?php echo esc_html__( 'Sin registrar', 'hello-elementor-child' ); ?>
								</p>
								<p class="hello-child-description">
									<?php echo esc_html__( 'Guardá la dirección y la clave, y después pulsá «Registrar este sitio».', 'hello-elementor-child' ); ?>
								</p>
							<?php endif; ?>
						<?php self::campo_cerrar(); ?>
					</div>

					<div class="hello-child-save-area">
						<button type="submit" class="button button-primary"><?php echo esc_html__( 'Guardar', 'hello-elementor-child' ); ?></button>
						<a class="button" href="<?php echo esc_url( self::url( 'swa_registrar' ) ); ?>"><?php echo esc_html__( 'Registrar este sitio', 'hello-elementor-child' ); ?></a>
						<a class="button" href="<?php echo esc_url( self::url( 'swa_reportar' ) ); ?>"><?php echo esc_html__( 'Mandar el reporte ahora', 'hello-elementor-child' ); ?></a>
					</div>
				</form>
			<?php
			self::panel_cerrar();
			endif;

			// ── 2. El correo ────────────────────────────────────────────────
			if ( 'correo' === $tab ) :
			self::panel_abrir(
				__( 'El correo de este sitio', 'hello-elementor-child' ),
				__( 'Antes de activar la verificación en dos pasos hay que comprobar que el sitio puede enviar correo. Si no puede, el código nunca llega y los administradores quedan afuera de su propio sitio.', 'hello-elementor-child' )
			);
			?>
				<p>
					<?php if ( $puede ) : ?>
						<strong style="color:#008a20"><?php echo esc_html__( '✓ Confirmado: el correo llega.', 'hello-elementor-child' ); ?></strong>
						<?php echo esc_html__( 'Probado el', 'hello-elementor-child' ); ?>
						<?php echo esc_html( (string) ( $correo['probado'] ?? '' ) ); ?>
						<?php echo esc_html__( 'y confirmado por', 'hello-elementor-child' ); ?>
						<?php echo esc_html( (string) ( $correo['confirmado_por'] ?? __( 'alguien', 'hello-elementor-child' ) ) ); ?>.
					<?php elseif ( SWA_Correo::envio_aceptado() ) : ?>
						<strong style="color:#dba617"><?php echo esc_html__( 'Se mandó, falta confirmar que llegó.', 'hello-elementor-child' ); ?></strong>
						<?php echo esc_html__( 'Se envió a', 'hello-elementor-child' ); ?>
						<?php echo esc_html( (string) ( $correo['destino'] ?? '' ) ); ?>.
					<?php elseif ( $correo ) : ?>
						<strong style="color:#d63638"><?php echo esc_html__( '✗ El envío falló.', 'hello-elementor-child' ); ?></strong>
						<?php echo esc_html( (string) ( $correo['error'] ?: __( 'sin detalle del error', 'hello-elementor-child' ) ) ); ?>
					<?php else : ?>
						<strong style="color:#dba617"><?php echo esc_html__( 'Todavía no se ha probado.', 'hello-elementor-child' ); ?></strong>
					<?php endif; ?>
				</p>

				<?php if ( SWA_Correo::envio_aceptado() && ! $puede ) : ?>
					<div class="notice notice-warning inline">
						<p>
							<strong><?php echo esc_html__( 'Andá a ver tu bandeja antes de seguir.', 'hello-elementor-child' ); ?></strong>
							<?php echo esc_html__( 'Que WordPress diga que mandó el correo no significa que haya llegado: con un SMTP mal configurado el mensaje sale del servidor y se pierde en el camino, o llega horas después. Eso es justo lo que dejaría a los administradores sin poder entrar.', 'hello-elementor-child' ); ?>
						</p>
					</div>
					<div class="hello-child-save-area">
						<a class="button button-primary" href="<?php echo esc_url( self::url( 'swa_confirmar_correo' ) ); ?>"><?php echo esc_html__( 'Sí, me llegó', 'hello-elementor-child' ); ?></a>
						<a class="button" href="<?php echo esc_url( self::url( 'swa_probar_correo' ) ); ?>"><?php echo esc_html__( 'Volver a probar', 'hello-elementor-child' ); ?></a>
					</div>
				<?php else : ?>
					<div class="hello-child-save-area">
						<a class="button" href="<?php echo esc_url( self::url( 'swa_probar_correo' ) ); ?>">
							<?php echo $puede ? esc_html__( 'Volver a probar', 'hello-elementor-child' ) : esc_html__( 'Probar el envío', 'hello-elementor-child' ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php
			self::panel_cerrar();

			$apagado = SWA_Dos_Factores::apagado_automatico();
			if ( $apagado ) :
				?>
				<div class="notice notice-error">
					<p>
						<strong>
							<?php echo esc_html__( 'La verificación en dos pasos se apagó sola el', 'hello-elementor-child' ); ?>
							<?php echo esc_html( (string) ( $apagado['cuando'] ?? '' ) ); ?>.
						</strong>
					</p>
					<p><?php echo esc_html( (string) ( $apagado['motivo'] ?? '' ) ); ?></p>
					<p><?php echo esc_html__( 'Nadie quedó encerrado: el sitio volvió a pedir solo la contraseña. Antes de volver a activarla hay que arreglar el correo y confirmar que llega.', 'hello-elementor-child' ); ?></p>
				</div>
				<?php
			endif;
			endif;

			// ── 3. Dos pasos ────────────────────────────────────────────────
			if ( 'dospasos' === $tab ) :
			self::panel_abrir(
				__( 'Verificación en dos pasos', 'hello-elementor-child' ),
				__( 'Pide un código por correo al entrar, además de la contraseña.', 'hello-elementor-child' )
			);
			?>
				<?php if ( ! $puede ) : ?>
					<div class="notice notice-error inline"><p>
						<strong><?php echo esc_html__( 'No se puede activar todavía.', 'hello-elementor-child' ); ?></strong>
						<?php
						echo SWA_Correo::envio_aceptado()
							? esc_html__( 'Falta confirmar que el correo de prueba llegó de verdad. Está arriba.', 'hello-elementor-child' )
							: esc_html__( 'Primero hay que lograr que el sitio envíe correo. Activarla ahora dejaría a los administradores sin poder entrar.', 'hello-elementor-child' );
						?>
					</p></div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="swa_guardar">
					<?php wp_nonce_field( 'swa_guardar' ); ?>

					<div class="hello-child-fields">
						<?php self::campo_abrir( 'swa-2fa-activo', __( 'Activar', 'hello-elementor-child' ), 'checkbox' ); ?>
							<label class="hello-child-switch">
								<input type="checkbox" id="swa-2fa-activo" name="2fa_activo" value="1" <?php checked( $a['2fa_activo'] ); ?> <?php disabled( ! $puede ); ?>>
								<span class="hello-child-switch__slider" aria-hidden="true"></span>
								<span class="hello-child-switch__text"><?php echo esc_html__( 'Pedir un código por correo al entrar', 'hello-elementor-child' ); ?></span>
							</label>
						<?php self::campo_cerrar(); ?>

						<?php self::campo_abrir( 'swa-2fa-roles', __( 'A qué roles', 'hello-elementor-child' ), 'roles' ); ?>
							<?php foreach ( wp_roles()->get_names() as $slug => $nombre ) : ?>
								<label style="display:inline-block;margin:0 16px 8px 0">
									<input type="checkbox" name="2fa_roles[]" value="<?php echo esc_attr( $slug ); ?>"
										<?php checked( in_array( $slug, (array) $a['2fa_roles'], true ) ); ?>>
									<?php echo esc_html( $nombre ); ?>
								</label>
							<?php endforeach; ?>
							<p class="hello-child-description">
								<?php echo esc_html__( 'Administrador y editor. Nunca suscriptor ni cliente de tienda: pedirle un código por correo a cada comprador tumba la venta.', 'hello-elementor-child' ); ?>
							</p>
						<?php self::campo_cerrar(); ?>

						<?php self::campo_abrir( 'swa-gracia', __( 'Periodo de gracia', 'hello-elementor-child' ), 'select' ); ?>
							<select name="gracia_dias" id="swa-gracia" class="hello-child-input hello-child-input--small" style="width:auto">
								<option value=""><?php echo esc_html__( 'Sin gracia: obligatorio desde ya', 'hello-elementor-child' ); ?></option>
								<option value="3"><?php echo esc_html__( '3 días', 'hello-elementor-child' ); ?></option>
								<option value="7" selected><?php echo esc_html__( '7 días', 'hello-elementor-child' ); ?></option>
								<option value="15"><?php echo esc_html__( '15 días', 'hello-elementor-child' ); ?></option>
							</select>
							<p class="hello-child-description">
								<?php echo esc_html__( 'Durante la gracia, quien todavía no lo ha usado entra sin código. Sirve para avisar primero y obligar después.', 'hello-elementor-child' ); ?>
								<?php if ( (int) $a['2fa_gracia_hasta'] > time() ) : ?>
									<br><strong><?php echo esc_html__( 'Vence el', 'hello-elementor-child' ); ?> <?php echo esc_html( wp_date( 'j \d\e F, H:i', (int) $a['2fa_gracia_hasta'] ) ); ?>.</strong>
								<?php endif; ?>
							</p>
						<?php self::campo_cerrar(); ?>

						<?php self::campo_abrir( 'swa-xmlrpc', __( 'XML-RPC', 'hello-elementor-child' ), 'checkbox' ); ?>
							<label class="hello-child-switch">
								<input type="checkbox" id="swa-xmlrpc" name="cerrar_xmlrpc" value="1" <?php checked( $a['cerrar_xmlrpc'] ); ?>>
								<span class="hello-child-switch__slider" aria-hidden="true"></span>
								<span class="hello-child-switch__text"><?php echo esc_html__( 'Cerrar XML-RPC', 'hello-elementor-child' ); ?></span>
							</label>
							<p class="hello-child-description">
								<?php echo esc_html__( 'La verificación en dos pasos protege el formulario de login y nada más. XML-RPC acepta usuario y contraseña por otra vía y no pasa por ella. Si nadie lo usa en este sitio, conviene cerrarlo en la misma visita.', 'hello-elementor-child' ); ?>
							</p>
							<p class="hello-child-description">
								<strong><?php echo esc_html__( 'Ojo:', 'hello-elementor-child' ); ?></strong>
								<?php echo esc_html__( 'lo usan la app de WordPress y algunas integraciones.', 'hello-elementor-child' ); ?>
							</p>
						<?php self::campo_cerrar(); ?>
					</div>

					<div class="hello-child-save-area">
						<button type="submit" class="button button-primary"><?php echo esc_html__( 'Guardar', 'hello-elementor-child' ); ?></button>
					</div>
				</form>
			<?php
			self::panel_cerrar();
			endif;

			// ── 4. Códigos de respaldo ──────────────────────────────────────
			if ( 'respaldo' === $tab ) :
			self::panel_abrir(
				__( 'Códigos de respaldo', 'hello-elementor-child' ),
				__( 'Sirven para entrar si te quedás sin acceso al correo.', 'hello-elementor-child' )
			);
			?>
				<p>
					<?php echo esc_html__( 'Tenés', 'hello-elementor-child' ); ?>
					<strong><?php echo (int) SWA_Dos_Factores::cuantos_respaldo( get_current_user_id() ); ?></strong>
					<?php echo esc_html__( 'códigos de respaldo sin usar.', 'hello-elementor-child' ); ?>
				</p>

				<?php
				$recien = get_transient( 'swa_respaldo_' . get_current_user_id() );
				if ( is_array( $recien ) && $recien ) :
					delete_transient( 'swa_respaldo_' . get_current_user_id() );
					?>
					<div class="notice notice-warning inline">
						<p><strong><?php echo esc_html__( 'Guardá estos códigos ahora. No se van a volver a mostrar.', 'hello-elementor-child' ); ?></strong></p>
						<p style="font-family:ui-monospace,Menlo,monospace;font-size:15px;line-height:2">
							<?php echo esc_html( implode( '   ', $recien ) ); ?>
						</p>
					</div>
				<?php endif; ?>

				<div class="hello-child-save-area">
					<a class="button" href="<?php echo esc_url( self::url( 'swa_respaldo' ) ); ?>"><?php echo esc_html__( 'Generar códigos nuevos', 'hello-elementor-child' ); ?></a>
				</div>
			<?php
			self::panel_cerrar();
			endif;
			?>
		</div>
		<?php
	}

	private static function volver( string $mensaje, bool $error = false ): void {
		wp_safe_redirect(
			admin_url( 'themes.php?page=' . self::PAGINA . '&swa_msg=' . ( $error ? 'error:' : 'ok:' ) . rawurlencode( $mensaje ) )
		);
		exit;
	}

	public static function guardar(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sin permiso.' );
		}
		check_admin_referer( 'swa_guardar' );

		$nuevos = [];

		// La casilla solo llega cuando está marcada, así que el apagado se
		// deduce de su ausencia — y solo en el formulario que la trae, para
		// que guardar otra pestaña no la apague sin querer.
		if ( isset( $_POST['seccion'] ) && 'conexion' === $_POST['seccion'] ) {
			$nuevos['panel_activo'] = isset( $_POST['panel_activo'] ) ? 1 : 0;
		}

		if ( isset( $_POST['panel_url'] ) ) {
			$nuevos['panel_url'] = esc_url_raw( wp_unslash( (string) $_POST['panel_url'] ) );
		}

		if ( isset( $_POST['clave_registro'] ) && '' !== $_POST['clave_registro'] ) {
			$nuevos['clave_registro'] = sanitize_text_field( wp_unslash( (string) $_POST['clave_registro'] ) );
		}

		// Las casillas solo llegan cuando están marcadas, así que el formulario
		// de arriba (conexión) no debe tocar los ajustes de abajo (2FA).
		if ( isset( $_POST['2fa_roles'] ) || isset( $_POST['2fa_activo'] ) || isset( $_POST['gracia_dias'] ) ) {
			$nuevos['2fa_activo']    = isset( $_POST['2fa_activo'] ) ? 1 : 0;
			$nuevos['cerrar_xmlrpc'] = isset( $_POST['cerrar_xmlrpc'] ) ? 1 : 0;
			$nuevos['2fa_roles']     = isset( $_POST['2fa_roles'] )
				? array_map( 'sanitize_key', (array) wp_unslash( $_POST['2fa_roles'] ) )
				: [];

			$dias = isset( $_POST['gracia_dias'] ) ? (int) $_POST['gracia_dias'] : 0;
			if ( $dias > 0 && ! SWA_Ajustes::obtener( '2fa_activo' ) ) {
				// La gracia empieza a contar al activar, no al guardar.
				$nuevos['2fa_gracia_hasta'] = time() + $dias * DAY_IN_SECONDS;
			}
		}

		SWA_Ajustes::guardar( $nuevos );
		self::volver( 'Ajustes guardados.' );
	}

	public static function probar_correo(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sin permiso.' );
		}
		check_admin_referer( 'swa_probar_correo' );

		$e = SWA_Correo::probar();

		$e['ok']
			? self::volver( 'El correo salió bien, a ' . $e['destino'] . '. Revisá que haya llegado.' )
			: self::volver( 'El envío falló: ' . ( $e['error'] ?: 'sin detalle' ), true );
	}

	public static function confirmar_correo(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sin permiso.' );
		}
		check_admin_referer( 'swa_confirmar_correo' );

		SWA_Correo::confirmar_recibido();
		self::volver( 'Confirmado. Ya se puede activar la verificación en dos pasos.' );
	}

	public static function registrar(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sin permiso.' );
		}
		check_admin_referer( 'swa_registrar' );

		$r = SWA_Reporte::registrar();
		is_wp_error( $r ) ? self::volver( $r->get_error_message(), true ) : self::volver( 'Sitio registrado en el panel.' );
	}

	public static function reportar(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sin permiso.' );
		}
		check_admin_referer( 'swa_reportar' );

		$r = SWA_Reporte::enviar();
		is_wp_error( $r ) ? self::volver( $r->get_error_message(), true ) : self::volver( 'Reporte enviado.' );
	}

	public static function respaldo(): void {
		if ( ! current_user_can( 'read' ) ) {
			wp_die( 'Sin permiso.' );
		}
		check_admin_referer( 'swa_respaldo' );

		$codigos = SWA_Dos_Factores::generar_respaldo( get_current_user_id() );
		set_transient( 'swa_respaldo_' . get_current_user_id(), $codigos, 300 );

		self::volver( 'Códigos generados. Guardalos: no se vuelven a mostrar.' );
	}
}
