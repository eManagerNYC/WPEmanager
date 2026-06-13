<?php

/**
 * Load install scripts
 */
foreach ( glob(get_template_directory() . '/includes/install/*.php') as $filename )
{
	if ( is_file($filename) ) {
		require_once( $filename );
	}
}

/**
 * eman_install
 *
 * Installs emanager features
 */
add_action( 'init', 'eman_install' );
function eman_install()
{
	// Install roles
	do_action( 'emanager/install/add_user_roles' );

	// Update ticket numbers
	if ( ! get_option('emanager_installed_ticketnumbers') ) {
		do_action( 'emanager/install/ticket_numbers' );
	}


	// Add CSI division taxonomies
	if ( ! get_option('emanager_installed_csidivisions') ) {
		do_action( 'emanager/install/csi_division' );
	}


	// Add default submittal types
	if ( ! get_option('emanager_installed_submittaltype') ) {
		do_action( 'emanager/install/submittal_type' );
	}


	// Add default issue types
	if ( ! get_option('emanager_installed_issuetype') ) {
		do_action( 'emanager/install/issue_type' );
	}


	// Install the file manager
	do_action( 'emanager_file_manager/install' );


	// Base install for emanager
	if ( ! get_option('emanager_installed') )
	{
		// Configure defaults
		do_action( 'emanager/install/defaults' );

		// flush rewrite rules
		flush_rewrite_rules(true);

		// Updates an option to keep the theme from overwriting things when it is turned off and on.
		add_option( 'emanager_installed', current_time('timestamp') );
	}


	// Add inbox
	if ( ! get_option('emanager_inbox') ) {
		do_action( 'emanager/install/inbox' );
	}


	// BIC
	if ( ! get_option('emanager_bic') ) {
		do_action( 'emanager/install/bic' );
	}


	// Add company and BIC into dcrs, tickets, and nocs
	if ( ! get_option('emanager_update_company_2') ) {
		do_action( 'emanager/install/update_company' );
	}


	// Add / Update default status taxonomies
	if ( ! get_option('emanager_install_issues') ) {
		do_action( 'emanager/install/status' );
	}


	// Add billed and paid status tracking
	if ( ! get_option('emanager_install_charge') ) {
		do_action( 'emanager/install/charge' );
	}


	// Install search index
	if ( ! get_option('emanager_install_index2') ) {
		do_action( 'emanager/install/search_index' );
	}
}