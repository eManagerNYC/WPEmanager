<?php

/**
 * Basic tasks to take when new posts are created
 */
add_action( 'acf/create_object/post/created', 'eman_create_default_status' );
function eman_create_default_status( $post_id )
{
	$post_type = get_post_type($post_id);
	if ( ! $post_type ) { return; }

	// Make sure post_status goes to publish and not draft
	$args = array(
		'ID'          => $post_id,
		'post_status' => 'publish',
	);
	wp_update_post($args);

	//Set default status for tickets, noc, (dcr, directives)
	if ( in_array($post_type, array( 'em_tickets', 'em_noc', 'em_dcr', 'em_issue', 'em_invoice', 'em_letter' )) ) {
		#wp_set_object_terms( $post_id, 'draft', 'em_status' );
	}
}