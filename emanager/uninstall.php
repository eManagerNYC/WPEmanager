<?php
/**
 * Uninstall eManager: removes roles, options and generated pages.
 *
 * Module data tables are preserved by default so an accidental uninstall never
 * destroys tens of thousands of project records. To also drop every eManager
 * table, define this in wp-config.php before uninstalling:
 *
 *   define( 'EM_DROP_TABLES_ON_UNINSTALL', true );
 *
 * @package eManager
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/includes/class-em-roles.php';
EM_Roles::remove_roles();

foreach ( array( 'em_project', 'em_page_dashboard', 'em_page_login', 'em_page_register' ) as $option ) {
	if ( in_array( $option, array( 'em_page_dashboard', 'em_page_login', 'em_page_register' ), true ) ) {
		$page_id = (int) get_option( $option );
		if ( $page_id ) {
			wp_delete_post( $page_id, true );
		}
	}
	delete_option( $option );
}

delete_transient( 'em_companies_cache' );
delete_transient( 'em_modules_cache' );

// Optional, opt-in: drop all eManager data tables.
if ( defined( 'EM_DROP_TABLES_ON_UNINSTALL' ) && EM_DROP_TABLES_ON_UNINSTALL ) {
	require_once __DIR__ . '/includes/class-em-db.php';
	EM_DB::drop_all_tables();
}
