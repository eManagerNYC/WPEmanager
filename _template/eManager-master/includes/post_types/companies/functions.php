<?php

/**
 * Create custom post titles for specific post types
 */
add_action( 'acf/save_post', 'eman_companies_save', 20 );
function eman_companies_save( $post_id )
{
	$post_type = get_post_type($post_id);
	if ( 'em_companies' == $post_type )
	{
		$cpt = ( $settings = eman_post_types($post_type) ) ? $settings : false;

		/**
		 * Update company document center folder name
		 */
		$post = get_post( $post_id );
		foreach ( $_POST['fields'] as $field_key => $field_value )
		{
			$field = get_field_object($field_key, $post_id);
			if ( 'form_post_title' == $field['name'] && $field_value != $post->post_title )
			{
				$post_data = array(
					'ID'        => $post_id,
					'post_name' => sanitize_title($field_value)
				);
				wp_update_post( $post_data );
				do_action( 'emanager_file_manager/rename_directory', "Companies/{$post->post_title}", "Companies/{$field_value}" );
			}
		}
	}
}

/**
 * Company is created, add a folder
 */
add_action( 'acf/create_object/post/created', 'eman_companies_create_folder' );
function eman_companies_create_folder( $post_id )
{
	$post_type = get_post_type($post_id);
	if ( 'em_companies' == $post_type )
	{
		$post = get_post( $post_id );
		do_action( 'emanager_file_manager/create_directory', "Companies/{$post->post_title}" );
	}
}
