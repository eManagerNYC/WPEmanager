<?php

/**
 * Create custom post titles for specific post types
 */
add_action( 'acf/save_post', 'eman_locations_save', 20 );
function eman_locations_save( $post_id )
{
	$post_type = get_post_type($post_id);
	if ( 'em_locations' == $post_type )
	{
		$cpt = ( $settings = eman_post_types($post_type) ) ? $settings : false;

		/**
		 * Update location titles
		 */
		$post_data = array(
			'ID' => $post_id,
		);

		$new_title = $parent_title = '';
		foreach ( $_POST['fields'] as $field_key => $field_value )
		{
			$field = get_field_object($field_key, $post_id);
			if ( 'parent' == $field['name'] && $field_value )
			{
				$parent_id = $field_value;
				while ( $parent_id && $parent = get_post($parent_id) )
				{
					$parent_title .= $parent->post_title . ' > ';
					$parent_id = $parent->post_parent;
				}
			}
			elseif ( 'area_name' == $field['name'] )
			{
				$new_title = $field_value;
			}
		}

		$post_data['post_title'] = $parent_title . $new_title;
		$post_data['post_name']  = sanitize_title($post_data['post_title']);

		wp_update_post( $post_data );
	}
}