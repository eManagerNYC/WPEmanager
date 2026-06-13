<?php

function ticket_total_cost( $postid )
{
	$material_cost = 0;
    $materials = eman_get_field('materials', $postid);
    if ( is_array($materials) )
    {
		$material_markup = 1;
        foreach ( $materials as $row )
        {
			if ( ! empty($row['price']) && !empty($row['amount_used']) ) {
              $material_cost += ($row['price'] * $row['amount_used']);
			}
        }
		$material_markup_arr = eman_get_field('material_markup', $postid);
		if ( is_array($material_markup_arr) )
		{
			foreach ( $material_markup_arr as $markup )
			{
				if ( ! empty($markup['value']) ) {
					$material_markup += ($markup['value']/100);
				}
			}
		}
		$material_cost *= $material_markup;
    }

	$equipment_cost = 0;
    $equipment = eman_get_field('equipment', $postid);
    if ( is_array($equipment) )
    {
		$equipment_markup = 1;
        foreach ( $equipment as $row )
        {
			if ( ! empty($row['rental_price']) && !empty($row['usage']) ) {
              $material_cost += ($row['rental_price'] * $row['usage']);
			}
        }
		$equipment_markup_arr = eman_get_field('equipment_markup', $postid);
		if ( is_array($equipment_markup_arr) )
		{
			foreach ( $equipment_markup_arr as $markup )
			{
				if ( !empty($markup['value'])) {
					$equipment_markup += ($markup['value']/100);
				}
			}
		}
		$equipment_cost *= $equipment_markup;
    }

	$labor_cost = 0;
    $labor = eman_get_field('employee_breakdown', $postid);
	if ( is_array($labor) )
	{
		foreach ( $labor as $row )
		{
			$classification = eman_get_field('classification', $row['employee']->ID);
			if ( $rates = get_fields($classification->ID) )
			{
				$ratetypes = array('rt', 'ot', 'dt', 'pt', 'pdt');
				foreach ( $ratetypes as $type )
				{
					if ( ! empty($rates[$type]) && !empty($row[$type]) ) {
						$labor_cost += ($rates[$type] * $row[$type]);
					}
				}
			}
		}
	}

    $labor = eman_get_field('classification_breakdown', $postid);
	if ( is_array($labor) )
	{
		foreach ( $labor as $row )
		{
			if ( $rates = get_fields($row['type']->ID) )
			{
				$ratetypes = array('rt', 'ot', 'dt', 'pt', 'pdt');
				foreach ( $ratetypes as $type )
				{
					if ( ! empty($rates[$type]) && !empty($row[$type]) ) {
						$labor_cost += ($rates[$type] * $row[$type]);
					}
				}
			}
		}
	}
	
	$labor_markup = 1;
	$labor_markup_arr = eman_get_field('labor_markup', $postid);
	if ( is_array($labor_markup_arr) )
	{
		foreach ( $labor_markup_arr as $markup )
		{
			if ( ! empty($markup['value']) ) {
				$labor_markup += ($markup['value']/100);
			}
		}
		$labor_cost *= $labor_markup;
	}

	$global_markup = 1;
	$global_markup_arr = eman_get_field('global_markup', $postid);
	if ( is_array($global_markup_arr) )
	{
		foreach ( $global_markup_arr as $markup )
		{
			if ( ! empty($markup['value']) ) {
				$global_markup += ($markup['value']/100);
			}
		}
	}

    return eman_number_format(($labor_cost + $material_cost + $equipment_cost) * $global_markup);
}
