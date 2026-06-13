<?php

/**
 * Redirect log out to home page
 */
add_action( 'wp_logout', 'eman_redirect_logout' );
function eman_redirect_logout()
{
	wp_redirect( home_url('/') );
	die();
}


/**
 * Redirect non-admin from dashboard (which they can't access anyhow)
 */
if ( ! class_exists('user_switching') )
{
	add_action( 'admin_init', 'eman_redirect_nonadmin' );
	function eman_redirect_nonadmin()
	{
		if ( '/wp-admin/async-upload.php' == $_SERVER['PHP_SELF'] ) return;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		if ( current_user_can('manage_options') ) return;
		if ( defined('DOING_AJAX') && DOING_AJAX ) return;

		wp_redirect( home_url('/') );
		die;
	}
}


/**
 * Set up constants for the current user
 */
if ( ! defined('HAS_ROLE_TURNER') )  define( 'HAS_ROLE_TURNER',  eman_check_role('turner') );
if ( ! defined('HAS_ROLE_OWNER') )   define( 'HAS_ROLE_OWNER',   eman_check_role('owner') );
if ( ! defined('HAS_ROLE_SUB') )     define( 'HAS_ROLE_SUB',     eman_check_role('sub') );
if ( ! defined('HAS_ROLE_PENDING') ) define( 'HAS_ROLE_PENDING', eman_check_role('pending') );


/**
 * Redirect users from pages they don't have access to.
 *
 * Right now this is kind of general. It is probably clearer to do more granualar redirects in the affected templates.
 */
add_action( 'wp', 'eman_permissions_redirects' );
function eman_permissions_redirects()
{
	if ( ( ! is_user_logged_in() || eman_check_role('pending')) && ! is_front_page() && ! is_page('login') )
	{
		wp_redirect( home_url('/') );
		die;
	}
}


/**
 * functions for our custom Roles
 */

// Group the roles for easy reference
function eman_roles( $type='turner' )
{
	switch ( $type )
	{
		case 'owner' :
			return array('owner','owners_rep','consultant');
			break;
		case 'sub' :
			return array('subcontractor');
			break;
		case 'pending' :
			return array('subscriber');
			break;
		case 'turner' :
			return array('administrator','editor');
			break;
	}
}

// Test user roles
function eman_check_role( $roles, $user_id=false )
{
	if ( ! is_array($roles) ) {
		$roles = eman_roles($roles);
	}

	// If testing for a specific user
	if ( $user_id )
	{
		foreach ( $roles as $role )
		{
			if ( user_can( $user_id, $role ) ) {
				return true;
			}
		}
	}
	// Otherwise test current user
	else
	{
		foreach ( $roles as $role )
		{
			if ( current_user_can( $role ) ) {
				return true;
			}
		}
	}

	// If we get here, user doesn't have the role
	return false;
}


/**
 * Is the post allowed to be edited.
 *
 * This does not check if the current user has permission, just whether a post is currently in a status 
 * that is editable by a permitted user.
 *
 * @author  Jake Snyder
 * @return	void
 */
function eman_post_editable( $post )
{
	if ( is_numeric($post) ) $post = get_post($post);
	if ( ! is_object($post) ) return false;

	// Settings area always editable
	if ( emanager_post::is_settings($post) ) return true;

	$status = emanager_post::status($post, 'slug');

	// If no status yet or is in draft or revise, it is editable
	if ( 'draft' == $status || 'revise' == $status ) return true;

	return false;
}


/**
 * Does the user have permission to edit the post
 *
 * @author  Jake Snyder
 * @return	void
 */
function eman_can_edit( $post, $user_id=false )
{
	/**
	 * If you can't view this post, then you can't edit it...
	 */
	if ( ! eman_can_view($post, $user_id) ) return false;

	if ( is_numeric($post) ) $post = get_post($post);
	if ( ! is_object($post) ) return false;
	if ( ! $user_id ) $user_id = get_current_user_id();
	$user          = get_user_by('id', $user_id);
	$status        = emanager_post::status($post, 'slug');
	$bic_userlogin = emanager_bic::get_bic($post, 'user_login');

	/**
	 * Test if user has capability to edit
	 */

	// Issues have different criteria
	if ( 'em_issue' == $post->post_type )
	{
		// Original author
		if     ( emanager_post::is_author($post) || emanager_post::same_company_as_post($post) || eman_check_role('turner') ) {
			return true;
		}
	}

	// PCO Request exception for allowances.  Allow user to edit if in their court and status is before sent to owner.
	if ( 'em_noc' == $post->post_type )
	{
		if     (($bic_userlogin === $user->user_login) && in_array($status, array('draft','manager','ready'))) 
		{
			return true;
		}

		if     (eman_check_role('turner') && in_array($status, array('draft'))) 
		{
			return true;
		}
	}

	// If the post is editable
	if ( eman_post_editable($post) )
	{
		// Original author
		if     ( emanager_post::is_author($post) ) {
			return true;
		}
		// Same company as original author
		elseif ( emanager_post::same_company_as_post($post) ) {
			return true;
		}
	}

	return false;
}


/**
 * Does the user have permission to edit the post
 *
 * @author  Jake Snyder
 * @param	int|string|object $post Either a post or post id to test a post, or the name of a post type to test a post type
 * @param	int $user_id The id of a user to test, will default to current user's id
 * @return	bool True if user can view post/post_type
 */
function eman_can_view( $post, $user_id=false )
{
	if ( is_numeric($post) ) $post = get_post($post);
	if ( ! $user_id ) $user_id = get_current_user_id();

	// Turner can view anything
	if ( eman_check_role('turner', $user_id) ) return true;

	// Pending can't view anything
	if ( eman_check_role('pending', $user_id) ) return false;

	// Test a post_type generally
	if ( is_string($post) && get_post_type_object($post) )
	{
		$post_type = $post;
		$is_post   = false;
	}
	// Test a post specifically
	elseif ( is_object($post) )
	{
		$post_type = $post->post_type;
		$is_post   = true;
		// If BIC user, all set, they can view it
		if ( $user_id == emanager_bic::get_bic($post, 'ID') ) {
			return true;
		}
	}
	else
	{
		return false;
	}

	// Settings for post type
	$cpt = ( $settings = eman_post_types($post_type) ) ? $settings : array();

	// Test settings permissions
	if ( ! empty($cpt['access']) )
	{
		// Owners have no access to settings
		if ( eman_check_role('owner', $user_id) && ! in_array('owner', $cpt['access']) ) return false;

		// Subs have limited access to settings
		if ( eman_check_role('sub', $user_id) && ! in_array('sub', $cpt['access']) ) return false;
	}
	elseif ( eman_check_role('owner', $user_id) && ( ($is_post && 'noc' === eman_pco_or_noc($post)) || ( ! $is_post && 'em_noc' == $post_type && ! get_query_var('pco')) ) )
	{
		return true;
	}
	else
	{
		return false;
	}

	if ( $is_post )
	{
		// Test if user is in same company or created post
		if ( $is_post && (emanager_post::is_author($post) || emanager_post::same_company_as_post($post) || ('em_issue' == $post->post_type && eman_check_role('owner', $user_id)) | ('em_letter' == $post->post_type && eman_check_role('owner', $user_id)) ) ) return true;

		// If is NOC (not PCO), and is owner group, they can view
		if ( $is_post && 'pco' == eman_pco_or_noc($post) && eman_check_role('owner', $user_id) ) return true;

		return false;
	}
	else
	{
		return true;
	}
}


/**
 * Test if the post is in a review phase, if so return the reviewer(s)
 *
 * @author  Jake Snyder
 * @return	array User(s) who can review
 */
function eman_post_reviewable( $post )
{
	// Get current post status
	$status = emanager_post::status($post, 'slug');
	if ( $status && ! in_array($status, array('void','approved','approve','draft','revise','executed')) ) {
		return true;
	}

	return false;
}


/**
 * Test if the current user is assigned to review current post
 *
 * @author  Jake Snyder
 * @return	void
 */
function eman_can_review( $post, $user_id=false ) {
	// Get the post if an id was provide
	if ( is_numeric($post) ) { $post = get_post($post); }
	// If no post then nothing to review
	if ( ! is_object($post) ) { return false; }

	// Get current user if no user provided
	if ( ! $user_id ) $user_id = get_current_user_id();

	// Test if owner review phase (doesn't require bic)
	$owner_review = false;
	$status = emanager_post::status($post, 'slug');
	if ( 'em_noc' == $post->post_type && in_array($status, array('submitted','executed','recommend')) && eman_check_role('owner') ) {#( current_user_can('owner') || current_user_can('owners_rep') ) ) {
		return true;
	}

	// Test if the post is currently reviewable, returns the reviews if so
	if ( eman_post_reviewable($post) ) {
		if ( 'em_letter' === get_post_type( $post ) && current_user_can('manage_options') ) {
			return true;
		} elseif ( $user_id == emanager_bic::get_bic($post, 'ID') ) {
			return true;
		}
	}

	return false;
}