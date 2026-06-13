<?php

/**
 * Load post types
 */
foreach ( glob(get_template_directory() . '/includes/post_types/*/*.php') as $filename )
{
	if ( is_file($filename) ) {
		require_once( $filename );
	}
}


/**
 * eman_post_types
 *
 * This is an interface for $global settings for custom post types
 *
 * @author  Jake Snyder
 * @return	obj The requested custom post type settings object, if a $setting is specified and exists, it will return the specific setting
 */
function eman_post_types( $post_type, $setting=false )
{
	// Get ALL emanager cpt settings
	if ( 'all' == $post_type )
	{
		if ( empty($GLOBALS['wp_post_types']) ) {
			return false;
		}

		$all_emanager = array();
		foreach ( $GLOBALS['wp_post_types'] as $slug => $post_type )
		{
			if ( ! empty($post_type->emanager) ) {
				$all_emanager[$slug] = $post_type->emanager;
			}
		}
		return $all_emanager;

	// If provided a post type name
	} elseif ( is_string($post_type) ) {
		$post_type_object = get_post_type_object( $post_type );

	// If provided a post type object already
	} elseif ( is_object($post_type) ) {
		$post_type_object = $post_type;

	// Otherwise there is nothing to do here
	} else {
		return false;
	}

	// Make sure the post type exists and has an emanager array
	if ( empty($post_type_object->emanager) ) {
		return false;
	}

	// If a specific setting is requested, get that
	if ( $setting && ! empty($post_type_object->emanager[$setting]) ) {
		return $post_type_object->emanager[$setting];
	}

	// Otherwise return the full emanager settings
	return $post_type_object->emanager;
}