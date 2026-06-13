<?php

/**
 * Add default issue type taxonomies
 */
add_action( 'emanager/install/issue_type', 'eman_install_issue_type' );
function eman_install_issue_type()
{
	$taxonomy = 'em_punchlist';
	$terms    = array(
		'punchlist'            => 'Punchlist',
		'qaqc'                 => 'QAQC',
		'worktocomplete'       => 'Work to Complete',
		'new_scope'            => 'New Scope',
		'safety'               => 'Safety',
		'commissioning'        => 'Commissioning',
		'agency_violation'     => 'Agency Violation'
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
	add_option( 'emanager_installed_issuetype', current_time('timestamp') );
}