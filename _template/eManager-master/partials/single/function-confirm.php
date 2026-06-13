<?php
if ( ! empty($_GET['action']) && 'confirm' == $_GET['action'] && eman_can_edit($GLOBALS['post']) )
{
	/**
	 * Create a review
	 */
	$turner_responsible = get_post_meta($GLOBALS['post']->ID, 'turner_responsible', true);

	// Update the confirmation review status per post_type
	$new_status = 'superintendent';
	if ( 'em_noc' == $GLOBALS['post']->post_type ) {
		$new_status = 'manager';
	} elseif ( 'em_issue' == $GLOBALS['post']->post_type ) {
		$new_status = 'open';
	} elseif ( 'em_letter' == $GLOBALS['post']->post_type ) {
		$new_status = 'pending';
	}

	$review_id          = emanager_bic::add_review( $turner_responsible, $GLOBALS['post']->ID, $new_status );

	do_action( 'emanager/cofirm_post', $GLOBALS['post']->ID );

	/**
	 * Update meta & email the superintendent
	 */
	if ( $review_id )
	{
		// Mark confirmation
		update_post_meta( $review_id, 'confirm', current_time('timestamp') );

		// Redirect
		wp_safe_redirect( add_query_arg('action', 'confirmed', get_permalink()) );
		die;
	}

	wp_safe_redirect( add_query_arg('action', 'notconfirmed', get_permalink()) );
	die;
}