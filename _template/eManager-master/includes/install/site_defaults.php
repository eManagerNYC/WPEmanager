<?php

add_action( 'emanager/install/defaults', 'eman_site_defaults' );
function eman_site_defaults()
{
	eman_add_options();
	eman_delete_defaults();
	eman_new_defaults();
	#eman_delete_links();
}

function eman_add_options()
{
	$options = array(
		'page_on_front'                => 2,
		'wunderground_api_key'         => '06e499c7b392f4b5',
		'blogname'                     => "eManager",
		'blogdescription'              => '',
		'default_ping_status'          => 'closed',
		'default_comment_status'       => 'open',
		'comments_notify'              => 0,
		'moderation_notify'            => 0,
		'comment_moderation'           => 0,
		'require_name_email'           => 1,
		'show_avatars'                 => 0,
		'avatar_rating'                => 'G',
		'blogdescription'              => '',
		'comment_registration'         => 0,
		'gmt_offset'                   => '',
		'timezone_string'              => 'America/New_York',
		'date_format'                  => 'F j, Y',
		'time_format'                  => 'g:i a',
		'start_of_week'                => 0,
		'default_post_edit_rows'       => 10,
		'use_smilies'                  => 0,
		'use_balanceTags'              => 0,
		'blog_charset'                 => 'UTF-8',
		'permalink_structure'          => '/%year%/%monthnum%/%postname%/',
		'comment_registration'         => 1,
		'close_comments_for_old_posts' => 1,
		'close_comments_days_old'      => 0,
		'thread_comments'              => 0,
		'page_comments'                => 0,
		'avatar_default'               => 'mystery',
		'default_cat_name'             => 'Uncategorized',
		'default_link_cat'             => 'Links',
		'enable_app'                   => 0,
		'enable_xmlrpc'                => 0,
		'embed_autourls'               => 1,
		'show_on_front'                => 'page',
		'page_for_posts'               => 0,
		'posts_per_page'               => 25,
		'blog_public'                  => '-2',
		'comment_whitelist'            => 0, // by default turn off "user must have previously approved comment"
		'options_rss_url'              => 'https://www.facebook.com/feeds/page.php?id=363996093623367&format=rss20',
		'options_welcome_title'        => 'Welcome to eManager',
		'options_welcome_content'      => 'Save money and get rid of excessive printing with a completely digital process. Turner eManager turns a construction project into an extremely lean-and-green jobsite. The web-based platform connects Turner staff, subcontractors, owners, and consultants to communicate, manage, and report jobsite information for major fast-track projects. To learn more about eManager, contact a Turner Construction representative near you. If you are on a project using eManager register below.',
		'options_pending_title'        => 'Your Account is Pending',
		'options_pending_content'      => 'Your account is still awaiting review and confirmation. It should be activated soon, but if you are having problems, please contact a Turner representative.',
	);

	// Process all the options.
	foreach ( $options as $key => $value ) {
		update_option( $key, $value );	
	}
}

function eman_delete_defaults()
{
	// Delete Hello World
	wp_delete_post(1, true);
	// Delete default comment
	wp_delete_comment(1);
}

function eman_delete_links()
{
	// Delete links
	wp_delete_link(1); // documentation
	wp_delete_link(2); // wordpress blog
 	wp_delete_link(3); //delete suggest ideas
	wp_delete_link(4); //delete support forum
	wp_delete_link(5); //delete plugins
	wp_delete_link(6); //delete themes
	wp_delete_link(7); //delete wp planet
}

function eman_new_defaults()
{
	global $wpdb;

	// HOME ID
	$home_id = 2;

	// Add default pages
	wp_update_post( array(
		'ID'             => $home_id,
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_content'   => '',
		'menu_order'     => 0,
		'post_name'      => 'dashboard',
		'post_status'    => 'publish',
		'post_title'     => 'Dashboard',
		'post_type'      => 'page',
	) );

	// add default company: turner
	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Construction Co.' AND post_type='em_companies'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Construction Co.',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_companies',
		) );
		update_metadata( 'post', $pid, 'phone',   '212229600' );
		update_metadata( 'post', $pid, 'email',   'TUR.APP.SAP.INTF@tcco.com' );
		update_metadata( 'post', $pid, 'website', 'http://www.TurnerConstruction.com' );
		update_metadata( 'post', $pid, 'address', '375 Hudson Street, New York, NY, 10011' );
	}

	// add default turner labor types
	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Project Executive' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Project Executive',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Project Manager' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Project Manager',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Superintendent' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Superintendent',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Engineer' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Engineer',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Safety' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Safety',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Admin' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Admin',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Intern' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Intern',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Labor Foreman' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Labor Foreman',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Labor Journeyman' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Labor Journeyman',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Carpenter Foreman' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Carpenter Foreman',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Carpenter Journeyman' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Carpenter Journeyman',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Painter Foreman' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Painter Foreman',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Painter Journeyman' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Painter Journeyman',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}

	$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='Turner Accountant' AND post_type='em_labortypes'");
	if ( 0 == $wpdb->num_rows )
	{
		$pid = wp_insert_post( array(
			'post_title'  => 'Turner Accountant',
			'post_date'   => current_time('mysql'),
			'post_status' => 'publish',
			'post_type'   => 'em_labortypes'
		) );
		update_metadata( 'post', $pid, 'company', 'Turner Construction Co.' );
	}
}