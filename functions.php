<?php
/**
 * Theme functions and definitions.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.6.0' );
define( 'HELLO_ELEMENTOR_CHILD_LOGIN_SLUG', 'panel/admin' );
define( 'HELLO_ELEMENTOR_CHILD_REWRITE_VERSION', '20260516-1' );

define( 'HELLO_ELEMENTOR_CHILD_PATH', trailingslashit( get_stylesheet_directory() ) );
define( 'HELLO_ELEMENTOR_CHILD_URI', trailingslashit( get_stylesheet_directory_uri() ) );

require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/i18n.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/helpers.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/theme-options.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/form-security.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/assets.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/access-rules.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/custom-login.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/email-templates.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/back-to-top.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/whatsapp-button.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/shortcodes.php';
require_once HELLO_ELEMENTOR_CHILD_PATH . 'inc/agente.php';
