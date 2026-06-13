<?php

/**
 * Enqueue Scripts & Styles
 */

/**
 * Add enqueue scripts/styles
 *
 * @return	void
 */
add_action( 'wp_enqueue_scripts', 'eman_styles', 999 );
add_action( 'wp_enqueue_scripts', 'eman_scripts', 999 );
add_action( 'wp_print_styles', 'custom_acf_deregister_styles', 100 );
add_action( 'wp_print_styles', 'remove_acf_styles', 200 );

/**
 * Load in the base styles
 *
 * @return	void
 */
function eman_styles()
{
	global $wp_styles; // call global $wp_styles variable to add conditional wrapper around ie stylesheet

	// register main stylesheet
	wp_enqueue_style( 'patch-stylesheet', get_stylesheet_directory_uri() . '/assets/css/style.css', array(), '', 'all' );

	// ie-only style sheet
	wp_enqueue_style( 'patch-ie-only', get_stylesheet_directory_uri() . '/assets/css/ie.css', array(), '' );

	// Remove plugin styles
	wp_dequeue_style( 'sewn_notifications' );
	wp_dequeue_style( 'acf-input-signature' );

	$wp_styles->add_data( 'patch-ie-only', 'conditional', 'lt IE 9' ); // add conditional wrapper around ie stylesheet
}

/**
 * Load in the base scripts
 *
 * @return	void
 */
function eman_scripts()
{
	global $wp_version, $post, $wp, $query_string;

	$request = strtolower($wp->request);
	if ( $request ) $request .= '/';
	$post_id = ( $post ) ? intval( $post->ID ) : 0;

	/* call jQuery from Google and move to footer * /
	wp_deregister_script('jquery');
	wp_register_script('jquery', ('//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'), false, '1.11.0', true);

	/* move core jQuery to footer * /
	wp_deregister_script('jquery');
	wp_register_script('jquery', includes_url( '/js/jquery/jquery.js' ), false, null, true);

	/* modernizr (without media query polyfill) */
	wp_enqueue_script( 'patch-modernizr', get_stylesheet_directory_uri() . '/assets/js/modernizr.custom.min.js', array(), '2.5.3', false );

	/* Load Google Charts Support */
	wp_register_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array(), '', false );

	/* comment reply script for threaded comments */
	if ( is_singular() && comments_open() && 1 == get_option('thread_comments') ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_register_script( 'pdfjs', get_template_directory_uri() . '/assets/js/pdf.js', array(), null, false );

	$emanager_scripts = array(
		'jquery',
		#'jquery-ui-core',
		#'jquery-ui-datepicker',
		'pdfjs',
		#'google-charts',
	);

	/* Adding scripts file in the footer */
	wp_enqueue_script( 'patch-js', get_stylesheet_directory_uri() . '/assets/js/scripts.js', $emanager_scripts, '', true );
	$args = array(
		'ajaxurl' => admin_url('admin-ajax.php'),
	);
	wp_localize_script( 'patch-js', 'patch', $args );

	$upload_dir = wp_upload_dir();
	$post_type  = eman_archive_info('post_type');
	$args = array(
		'home_url'     => home_url(),
		'admin_url'    => admin_url(),
		'ajaxurl'      => admin_url( 'admin-ajax.php' ),
		'upload'       => base64_encode( $upload_dir['baseurl'] ),
		'folder'       => 0,
		'wp_version'   => $wp_version,
		'post_id'      => $post_id,
		'post_type'    => $post_type,
		'query_string' => $query_string,
		'spinner'      => admin_url( 'images/spinner.gif' ),
		#'connector'    => add_query_arg( array( 'connector' => 1, 'nonce' => wp_create_nonce('elfinder'), 'action' => 'elfinder_connector' ), home_url('/' . $request) ),
		'connector'    => admin_url( 'admin-ajax.php' ),
		'data'         => array( 'nonce' => wp_create_nonce('elfinder'), 'action' => 'elfinder_connector', 'folder' => untrailingslashit($request) )
	);
	if ( class_exists('Eman_File_Manager') && ! empty($wp->request) ) {
		$file_manager = new Eman_File_Manager();
		$args['folder'] = base64_encode( $file_manager->url_to_folder($wp->request) );
	}
	wp_localize_script( 'patch-js', 'eman', $args );
}

/**
 * Deregister admin styles on the front end when using ACF forms
 */
function custom_acf_deregister_styles()
{
	if ( ! is_admin() ) {
		wp_deregister_style( 'wp-admin' );
	}
}

/**
 * Deregister most of the ACF default styles
 */
function remove_acf_styles()
{
	$styles = array(
		#'acf',
		#'acf-field-group',
		#'acf-pro-field-group',
		#'acf-global',
		#'acf-input',
		#'acf-pro-input',
		#'acf-datepicker',
	);
	foreach( $styles as $v ) {
		wp_deregister_style( $v ); 
	}
}
/**/

/**
 * Remove jQuery Migrate
 *
 * Be absolutely sure, you are ok to do this, and test your code afterwards.
 *
 * @return	void
 * /
add_filter( 'wp_default_scripts', 'eman_dequeue_jquery_migrate' );
function eman_dequeue_jquery_migrate( &$scripts )
{
	if ( ! is_admin() )
	{
		$scripts->remove( 'jquery');
		$scripts->add( 'jquery', false, array( 'jquery-core' ), '1.10.2' );
	}
}
/**/
