<?php

/**
 * Filter the responsible_person selectors
 */
add_filter( 'acf/load_field/name=sap_reason', 'eman_load_selected_sap_reasons' );
function eman_load_selected_sap_reasons( $field )
{
	$choices = eman_get_field('sap_reasons', 'options');
	if ( $choices && is_array($choices) )
	{
		$field['choices'] = array();#array( 'null' => "- Select -" );
		foreach ( $choices as $choice )
		{
			$field['choices'][ $choice ] = $choice;
		}
	}

	return $field;
}