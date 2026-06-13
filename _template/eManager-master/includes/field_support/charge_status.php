<?php

/**
 * AJAX Update billed / paid charge status
 */
add_action( 'wp_ajax_eman_update_charge', 'eman_update_charge' );
function eman_update_charge()
{
	if ( empty($_POST['post_id']) || empty($_POST['selected']) || empty($_POST['value']) ) {
		return false;
	}

	$output = array( 'status' => 0 );

	if ( $post = get_post($_POST['post_id']) )
	{
		$status = false;
		if ( $_POST['selected'] && 'false' != $_POST['selected'] ) {
			$status = wp_set_object_terms( $post->ID, $_POST['value'], 'em_charge', true );
		} else {
			$status = wp_remove_object_terms( $post->ID, $_POST['value'], 'em_charge' );
		}

		if ( $status && ! is_wp_error($status) ) {
			$output = array( 'status' => 1 );
		}
	}

	echo json_encode($output);
	die;
}