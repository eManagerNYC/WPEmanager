<?php

/**
 * Populate noc number in review when they exist in parent
 */
add_filter( 'acf/load_field/name=noc_number', 'eman_load_parent_noc' );
function eman_load_parent_noc( $field )
{
	if ( ! empty($GLOBALS['post']) && ! get_query_var('add') )
	{
		if ( $number = $GLOBALS['post']->noc_number ) {
			$field['value'] = $number;
		}
	}

	return $field;
}