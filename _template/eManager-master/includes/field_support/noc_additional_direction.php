<?php

/**
 * Add additional direction to NOC if filled out in the list view
 */
if ( 'Yes' === eman_get_field('additional_direction') ) {
	add_filter('acf/load_field/name=direction', 'add_additional_direction');
}
function add_additional_direction( $field )
{
	// reset choices
	$field['choices'] = eman_get_field('direction');

	// load repeater from the options page
	if ( eman_get_field('direction_list', 'option') )
	{
		// loop through the repeater and use the sub fields "value" and "label"
		while ( has_sub_field('direction', 'option') )
		{
			$value = get_sub_field('value');
			$label = get_sub_field('label');

			$field['choices'][ $value ] = $label;
		}
	}

    // Important: return the field
    return $field;
}