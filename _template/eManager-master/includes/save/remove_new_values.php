<?php

/**
 * Plugin Name:       New Post and New User Fix
 * Plugin URI:        http://Jupitercow.com/
 * Description:       Fixes new_post and new_user addition problem where old information is added from the options table...
 * Author:            Jupitercow
 * Contributor:       Jake Snyder
 */

add_filter( 'acf/load_value', 'eman_remove_new_values', 99, 3 );
function eman_remove_new_values( $value, $post_id, $field )
{
	if ( 'new_post' == $post_id || 'new_user' == $post_id ) {
		$value = false;
	}
	return $value;
}