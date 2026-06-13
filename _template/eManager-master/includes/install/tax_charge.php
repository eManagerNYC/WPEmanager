<?php

/**
 * Add default charge (billed / paid) taxonomies
 */
add_action( 'emanager/install/charge', 'eman_install_charge' );
function eman_install_charge()
{
	$taxonomy = 'em_charge';
	$terms    = array(
		'billed'     => 'Billed',
		'paid'       => 'Paid',
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
	update_option( 'emanager_install_charge', current_time('timestamp') );
}