<?php

/**
 * Populate pco number in review when they exist in parent
 */
add_filter( 'acf/load_field/name=pco_number', 'eman_load_parent_pco');
function eman_load_parent_pco( $field )
{
	if ( ! empty($GLOBALS['post']) && ! get_query_var('add') )
	{
		if ( $number = $GLOBALS['post']->pco_number ) {
			$field['value'] = $number;
		}
	}

	return $field;
}

/**
 * PCO Number, get value if there is one already (eg: post pullback)
 *
 * This is causing problems with the table lists (removing from archive 02/17 hopefully fix)
 */
add_action( 'acf/load_value/name=pco_number', 'eman_load_value_get_existing_pco_number', 10, 3 );
function eman_load_value_get_existing_pco_number( $value, $post_id, $field )
{
	if ( ! is_archive() && isset($GLOBALS['post']->ID) && is_numeric( $post_id ) )
	{
		$main_post_id = $GLOBALS['post']->ID;
		if ( ! $value && $pco_number = get_post_meta($main_post_id, 'pco_number', true) ) {
			$value = $pco_number;
		}
	}
	
	return $value;
}