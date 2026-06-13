<?php

/**
 * eman_role_names
 *
 * Updates Role Labels for some base roles
 *
 * @return void
 */
add_action( 'init', 'eman_role_names', 20 );
function eman_role_names()
{
	global $wp_roles;

	if ( empty($wp_roles) ) $wp_roles = new WP_Roles();

	$wp_roles->roles['editor']['name']     = 'Turner';
	$wp_roles->role_names['editor']        = 'Turner';
	$wp_roles->roles['subscriber']['name'] = 'Pending';
	$wp_roles->role_names['subscriber']    = 'Pending';
}

/**
 * eman_add_user_roles
 *
 * Create extra roles that we need. I am using the author as a basis for these, in general that is a good starting place for what these users need to do.
 *
 * Runs once at initial install
 *
 * @return void
 */
add_action( 'emanager/install/add_user_roles', 'eman_install_add_user_roles' );
function eman_install_add_user_roles()
{
	$base_role = get_role('author');

	if ( is_object($base_role) )
	{
		$new_roles = array(
			'owner'         => 'Owner',
			'owners_rep'    => 'Owners Rep',
			'subcontractor' => 'Subcontractor',
			'consultant'    => 'Consultant'
		);
	
		foreach ( $new_roles as $key => $value ) {
			add_role( $key, $value, $base_role->capabilities );
		}
	
		// remove the unnecessary roles
		remove_role( 'author' );
		remove_role( 'contributor' );
	}
}