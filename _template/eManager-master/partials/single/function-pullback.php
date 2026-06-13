<?php
if ( ! empty($_GET['action']) && 'pullback' == $_GET['action'] )
{
	// Get current status
	$status = emanager_post::status($GLOBALS['post'], 'slug');
	if ( 'submitted' == $status )
	{
		// Make sure the current user has access to pullback
		$gatekeepers     = eman_get_field('noc_gatekeeper', 'option');
		$approver_ids    = array();
		if ( is_array($gatekeepers) )
		{
			foreach ( $gatekeepers as $gatekeeper ) {
				$approver_ids[] = $gatekeeper['ID'];
			}
		}

		// Add a review
		if ( in_array(get_current_user_id(), $approver_ids) )
		{
			$review_id = emanager_bic::add_review( get_current_user_id() );

			// Update meta
			if ( $review_id )
			{
				// Add post to review
				update_post_meta( $review_id, 'reviewed_id', $GLOBALS['post']->ID );

				// Mark pullback
				update_post_meta( $review_id, 'pullback', current_time('timestamp') );

				// Update the status
				$new_status = 'ready';
				wp_set_object_terms( $review_id, $new_status, 'em_status' );
				wp_set_object_terms( $GLOBALS['post']->ID, $new_status, 'em_status' );
	
				wp_redirect( add_query_arg('action', 'pulledback', get_permalink()) );
				die;
			}
		}
	}

	wp_redirect( add_query_arg('action', 'notpulledback', get_permalink()) );
	die;
}