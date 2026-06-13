<?php

/**
 * Basic tasks to take when new posts are created
 */
add_action( 'acf/create_object/post/created', 'eman_create_request_number' );
function eman_create_request_number( $post_id )
{
	$post_type = get_post_type($post_id);
	if ( ! $post_type ) {
		return;
	}

	/**
	 * Add PCO request number or ticket number
	 */
	$accepted = array( 'em_tickets', 'em_noc' );
	if ( in_array($post_type, $accepted) ) {
		update_post_meta( $post_id, 'request_number', uniqid() );
	}
}