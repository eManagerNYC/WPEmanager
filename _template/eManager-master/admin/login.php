<?php
/**
 * Customize the login screen
 */
add_action( 'login_init', 'eman_login_init' );
function eman_login_init()
{
	// actions
	add_action( 'login_enqueue_scripts', 'eman_login_css' );

	// filters
	#add_filter( 'login_headerurl',       'eman_login_url' );
	#add_filter( 'login_headertitle',     'eman_login_title' );
}

/**
 * Add theme log in css
 */
function eman_login_css()
{
	wp_enqueue_style( 'eman_admin_login', get_template_directory_uri() . '/assets/css/login.css', false );
}

/**
 * Change the logo link from wordpress.org to the site home
 */
function eman_login_url()
{
	return home_url( '/' );
}

/**
 * Change the alt text on the logo to site name
 */
function eman_login_title()
{
	return get_option('blogname');
}
