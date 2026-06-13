<?php

/**
 * Remove the company field from sub contractors
 */
add_filter( 'acf/load_field/name=company', 'eman_load_field_company');
function eman_load_field_company( $field )
{
	if ( eman_check_role('sub') && ( ! is_single() || ! empty($_REQUEST['edit']) ) ) {
		$field = false;
	}

	return $field;
}