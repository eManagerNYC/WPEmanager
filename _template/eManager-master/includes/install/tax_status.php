<?php

/**
 * Add default status taxonomies
 */
add_action( 'emanager/install/status', 'eman_install_status' );
function eman_install_status()
{
	$taxonomy = 'em_status';
	$terms    = array(
		'addressed'      => 'Addressed',
		'approved'       => 'Approved',
		'approved'       => 'Approved',
		'closed'         => 'Closed',
		'disputed'       => 'Disputed',
		'draft'          => 'Draft',
		'executed'       => 'Executed',
		'in_progress'    => 'In Progress',
		'manager'        => 'Manager Review',
		'open'           => 'Open',
		'pending'        => 'Pending',
		'ready'          => 'Ready to Submit',
		'recommend'      => 'Recommended',
		'rejected'       => 'Rejected',
		'returned'       => 'Returned',
		'revise'         => 'Revise',
		'submitted'      => 'Submitted',
		'superintendent' => 'Superintendent Review',
		'verified'       => 'Verified',
		'void'           => 'Void',
	);
	foreach ( $terms as $key => $term )
	{
		if ( ! term_exists( $key, $taxonomy ) )
		{
			wp_insert_term(
				$term,
				$taxonomy,
				array( 'slug' => $key )
			);
		}
	}

	// Updates an option to keep the theme from overwriting things when it is turned off and on.
	update_option( 'emanager_install_issues', current_time('timestamp') );
}