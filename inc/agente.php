<?php
/**
 * El agente de Solución Web, dentro del tema.
 *
 * ─── Por qué vive aquí y ya no en un plugin ─────────────────────────────────
 *
 * El agente le cuenta al Panel Kinsta quién tiene acceso de administrador a
 * este sitio, y enciende la verificación en dos pasos por correo. Eso no se
 * puede averiguar desde fuera: la API del hosting no sabe listar los usuarios
 * de WordPress, y es justo lo que hay que ver cuando un malware se crea un
 * administrador.
 *
 * Hasta ahora era un plugin aparte, y había que instalarlo sitio por sitio.
 * Como este tema hijo ya va en todos nuestros sitios, el agente viaja con él:
 * un sitio nuevo nace conectado, sin que nadie se acuerde de instalar nada.
 * Es el mismo camino que siguió el antiguo plugin no_url_field, que hoy es
 * inc/form-security.php.
 *
 * ─── Qué sale de este sitio, y qué no ───────────────────────────────────────
 *
 * Sale: id, usuario, correo, roles y fecha de alta de quien tiene control del
 * sitio; si la verificación en dos pasos está activa; y si el sitio logra
 * enviar correo.
 *
 * NO sale: contraseñas (ni cifradas), contenido, pedidos, clientes, ni nada
 * más de la base de datos.
 *
 * Y el sitio no abre ninguna puerta nueva: es él quien llama al panel, no al
 * revés. No queda un endpoint esperando peticiones de internet.
 *
 * ─── Si el plugin suelto sigue instalado ────────────────────────────────────
 *
 * Los sitios que ya tenían el plugin lo siguen teniendo. Los plugins cargan
 * antes que el tema, así que cuando se llega aquí el plugin ya definió
 * SWA_VERSION — y entonces este archivo se aparta y no hace nada. Nunca corren
 * los dos: ni dos reportes al panel, ni dos clases con el mismo nombre.
 *
 * El día que se quite el plugin de un sitio, el tema toma el relevo solo, con
 * los mismos ajustes: están guardados en la base de datos, no en el plugin.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * El plugin manda. Si está activo, el tema no se mete.
 *
 * Se mira la constante y no la lista de plugins activos, porque la constante
 * es la prueba de que el plugin se cargó de verdad en ESTA petición. Lo otro
 * es una lista que puede mentir.
 */
if ( defined( 'SWA_VERSION' ) ) {
	return;
}

/**
 * La versión del agente, con marca de dónde vive.
 *
 * El panel enseña esto tal cual en la ficha del sitio, así que la marca sirve
 * para saber de un vistazo qué sitios ya van con el agente dentro del tema y
 * cuáles siguen con el plugin.
 */
define( 'SWA_VERSION', '0.6.4-tema' );
define( 'SWA_RUTA', trailingslashit( get_stylesheet_directory() ) . 'inc/agente/' );

require_once SWA_RUTA . 'class-ajustes.php';
require_once SWA_RUTA . 'class-firma.php';
require_once SWA_RUTA . 'class-correo.php';
require_once SWA_RUTA . 'class-normas.php';
require_once SWA_RUTA . 'class-formularios.php';
require_once SWA_RUTA . 'class-fondo.php';
require_once SWA_RUTA . 'class-tienda.php';
require_once SWA_RUTA . 'class-silencio.php';
require_once SWA_RUTA . 'class-ordenes.php';
require_once SWA_RUTA . 'class-reporte.php';
require_once SWA_RUTA . 'class-vigilante.php';
require_once SWA_RUTA . 'class-dos-factores.php';
require_once SWA_RUTA . 'class-admin.php';

/**
 * Arranca el agente.
 *
 * Esto se llama directo, sin colgar de ningún gancho, y no es un descuido: el
 * functions.php del tema se ejecuta durante after_setup_theme, o sea DESPUÉS
 * de plugins_loaded, que es donde arrancaba el plugin. Colgarlo de
 * plugins_loaded aquí sería colgarlo de un tren que ya pasó.
 */
SWA_Reporte::init();
SWA_Ordenes::init();
SWA_Vigilante::init();
SWA_Dos_Factores::init();
SWA_Silencio::init();

if ( is_admin() ) {
	SWA_Admin::init();
}

/**
 * Los relojes.
 *
 * Un tema no tiene gancho de activación, así que no hay un único momento
 * donde programarlos. Se hace en dos sitios y ninguno sobra:
 *
 *   - al cambiar de tema, que es el equivalente a activar un plugin;
 *   - y en cada carga, dentro del init() de cada clase, que ya comprueba si
 *     el reloj está puesto antes de ponerlo. Esto es lo que recupera los
 *     sitios donde el tema se instaló copiando archivos, sin pasar por el
 *     cambio de tema.
 *
 * Al irse a otro tema se apagan, para no dejar un sitio reportando desde un
 * tema que ya nadie usa.
 */
add_action(
	'after_switch_theme',
	static function (): void {
		SWA_Reporte::programar();
		SWA_Ordenes::programar();
	}
);

add_action(
	'switch_theme',
	static function (): void {
		SWA_Reporte::desprogramar();
		SWA_Ordenes::desprogramar();
	}
);
