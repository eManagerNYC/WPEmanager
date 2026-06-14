<?php
/**
 * Plugin Name:       eManager — Construction Management Dashboard
 * Plugin URI:        https://github.com/emanager-app/emanager
 * Description:       A general-contracting portal for mega projects: a lightweight, modular WordPress dashboard with a role-gated change-order workflow (PCO → NOC → Directive → Proposal → COR → eTicket) and config-driven CRUD modules stored in native WordPress database tables. Modules are installable via ZIP.
 * Version:           3.9.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            eManager
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       emanager
 * Domain Path:       /languages
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

define( 'EM_VERSION', '3.9.0' );
define( 'EM_FILE', __FILE__ );
define( 'EM_DIR', plugin_dir_path( __FILE__ ) );
define( 'EM_URL', plugin_dir_url( __FILE__ ) );
define( 'EM_MODULES_DIR', EM_DIR . 'modules/' );

// Custom (user-installed) modules live in uploads so plugin updates never wipe them.
$em_uploads = wp_upload_dir();
define( 'EM_CUSTOM_MODULES_DIR', trailingslashit( $em_uploads['basedir'] ) . 'emanager-modules/' );
define( 'EM_CUSTOM_MODULES_URL', trailingslashit( $em_uploads['baseurl'] ) . 'emanager-modules/' );

require_once EM_DIR . 'includes/class-em-roles.php';
require_once EM_DIR . 'includes/class-em-db.php';
require_once EM_DIR . 'includes/class-em-workflow.php';
require_once EM_DIR . 'includes/class-em-modules.php';
require_once EM_DIR . 'includes/class-em-installer.php';
require_once EM_DIR . 'includes/class-em-api.php';
require_once EM_DIR . 'includes/class-em-auth.php';
require_once EM_DIR . 'includes/class-em-public.php';
require_once EM_DIR . 'includes/class-em-admin.php';

/**
 * Plugin activation: roles, capabilities, dashboard pages.
 */
function em_activate() {
	EM_Roles::add_roles();
	EM_Auth::create_pages();
	if ( ! file_exists( EM_CUSTOM_MODULES_DIR ) ) {
		wp_mkdir_p( EM_CUSTOM_MODULES_DIR );
	}
	// Rescan modules fresh, then create shared tables + one table per module.
	EM_Modules::flush_cache();
	EM_DB::install_tables();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'em_activate' );

/**
 * Plugin deactivation: keep data, just flush rules.
 */
function em_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'em_deactivate' );

/**
 * Boot the plugin.
 */
function em_init() {
	// Translations for wp.org-hosted plugins are loaded automatically since WP 4.6.

	EM_Modules::instance();   // Module registry (built-in + custom).
	EM_Auth::instance();      // Login / registration / access control.
	EM_Public::instance();    // Shortcode, assets, partials.

	if ( is_admin() ) {
		EM_Admin::instance(); // Settings screens.
	}
}
add_action( 'plugins_loaded', 'em_init' );

/**
 * REST API routes.
 */
add_action( 'rest_api_init', array( 'EM_Api', 'register_routes' ) );
