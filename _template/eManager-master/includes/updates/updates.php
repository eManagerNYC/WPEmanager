<?php

/**
 * Manage automatic updating
 */


/**
 * URL to your API
 */
$eman_updates_url = 'https://demo.emanagercloud.com/api/index.php';


/**
 * TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
 */
set_site_transient( 'update_themes', null );


/**
 * Add our custom check to WP
 */
add_filter( 'pre_set_site_transient_update_themes', 'eman_check_for_update' );
function eman_check_for_update( $checked_data )
{
	global $wp_version, $eman_updates_url;

	$theme_base = get_option('template');
	$url        = home_url();

	// Get theme info
	if ( function_exists('wp_get_theme') )
	{
		$theme_data    = wp_get_theme( $theme_base );
		$theme_version = $theme_data->Version;
	}
	else
	{
		$theme_data    = get_theme_data( TEMPLATEPATH . '/style.css');
		$theme_version = $theme_data['Version'];
	}

	// Set up data to check for update
	$args = array(
		'body'       => array(
			'action'    => 'theme_update',
			'slug'      => $theme_base,
			'version'   => $theme_version,
			'api-key'   => md5( $url ),
		),
		'user-agent' => "WordPress/$wp_version; $url",
	);
	$raw_response = wp_remote_post( $eman_updates_url, $args );

	// If successful, add to the checked data
	if ( ! is_wp_error($raw_response) && 200 == $raw_response['response']['code'] && ! empty($raw_response['body']) && is_serialized($raw_response['body']) )
	{
		$checked_data->response[$theme_base] = unserialize($raw_response['body']);
	}

	return $checked_data;
}

/**
 * Take over the Theme info screen on WP multisite
 * /
add_filter( 'themes_api', 'my_theme_api_call', 10, 3 );
function my_theme_api_call( $def, $action, $args )
{
	global $wp_version, $eman_updates_url;

	$theme_base = get_option('template');
	$url        = home_url();
	
	if ( $args->slug != $theme_base ) { return $def; }

	// Get theme info
	if ( function_exists('wp_get_theme') )
	{
		$theme_data    = wp_get_theme( $theme_base );
		$theme_version = $theme_data->Version;
	}
	else
	{
		$theme_data    = get_theme_data( TEMPLATEPATH . '/style.css');
		$theme_version = $theme_data['Version'];
	}
	
	// Get the current version
	$args->version  = $theme_version;
	$request_string = prepare_request( $action, $args );
	$request        = wp_remote_post( $eman_updates_url, $request_string );

	if ( is_wp_error($request) )
	{
		$result = new WP_Error( 'themes_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message() );
	}
	else
	{
		$result = unserialize($request['body']);

		if ( false === $res ) {
			$result = new WP_Error( 'themes_api_failed', __('An unknown error occurred'), $request['body'] );
		}
	}

	return $result;
}
/**/
if ( is_admin() ) {
	$current = get_transient( 'update_themes' );
}
