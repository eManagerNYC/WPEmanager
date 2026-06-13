<?php

/**
 * Create custom post titles for specific post types
 */
add_action( 'acf/save_post', 'eman_contracts_save', 20 );
function eman_contracts_save( $post_id )
{
	$post_type = get_post_type($post_id);
	if ( 'em_contracts' == $post_type )
	{
		$cpt = ( $settings = eman_post_types($post_type) ) ? $settings : false;

		/**
		 * Update contract titles
		 */
		$post_data = array(
			'ID' => $post_id,
		);
		$fields = array();
		foreach ( $_POST['fields'] as $field_key => $field_value )
		{
			$field = get_field_object($field_key, $post_id);
			$fields[$field['name']] = $field_value;
		}

		$post_data['post_title'] = $fields['number'] .', '. get_the_title($fields['company']) .', '. $fields['bid_description'];
		$post_data['post_name']  = sanitize_title($post_data['post_title']);

		wp_update_post( $post_data );
	}
}