<?php

/**
 * PCO Number, get value if there is one already (eg: post pullback)
 */
add_action( 'acf/load_value/name=company', 'eman_load_value_company', 10, 3 );
function eman_load_value_company( $value, $post_id, $field )
{
	if ( ! $value ) {
		$value = get_post_meta($post_id, 'company', true);
	}

	return $value;
}