<?php

/**
 * Add default submittal type taxonomies
 */
add_action( 'emanager/install/submittal_type', 'eman_install_submittal_type' );
function eman_install_submittal_type()
{
	$taxonomy = 'em_submittaltype';
	$terms    = array(
		'shop_drawings'			=> 'Shop Drawings',
		'asbuilt_drawings'      => 'As Built Drawings',
		'product_data'          => 'Product Data',
		'samples'               => 'Samples',
		'calculations'          => 'Calculations',
		'mockups'               => 'Mock ups',
		'reports'               => 'Reports',
		'certificates'          => 'Certificates',
		'ommanuals'             => 'O &amp M Manuals',
		'warranties'            => 'Warranties',
		'other'                 => 'Other'
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
	add_option( 'emanager_installed_submittaltype', current_time('timestamp') );
}