<?php

/**
 * Create custom post titles for specific post types
 */
add_action( 'acf/save_post', 'eman_reviews_save', 20 );
function eman_reviews_save( $post_id )
{
	$post_type = get_post_type($post_id);
	if ( 'em_reviews' == $post_type )
	{
		$cpt = ( $settings = eman_post_types($post_type) ) ? $settings : false;

		/**
		 * Add a title to the review
		 */
		$current_user = wp_get_current_user();
		$parent_id    = ( isset($GLOBALS['post']->ID) ) ? $GLOBALS['post']->ID : 0;
		$post_data    = array(
			'ID'         => $post_id,
			'post_title' => ucwords( str_replace('em_','',$post_type) ) .', '. get_the_title($parent_id) . ( ! empty($_POST['submit']) ? ': ' . $_POST['submit'] : '') . ' by ' . $current_user->display_name,
		);
		$post_data['post_name'] = sanitize_title( $post_data['post_title'] );
		wp_update_post( $post_data );
	}
}