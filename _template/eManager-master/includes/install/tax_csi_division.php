<?php

/**
 * Add default CSI division taxonomies
 */
add_action( 'emanager/install/csi_division', 'eman_install_csi_division' );
function eman_install_csi_division()
{
	$taxonomy = 'em_csidivisions';
	$terms    = array(
		'01_general'                             => '01 General Requirements',
		'02_existingconditions'                  => '02 Existing Conditions',
		'03_concrete'                            => '03 Concrete',
		'04_masonry'                             => '04 Masonry',
		'05_metals'                              => '05 Metals',
		'06_woodplasticscomposites'              => '06 Wood, Plastics, and Composites',
		'07_thermalmoistureprotection'           => '07 Thermal and Moisture Protection',
		'08_openings'                            => '08 Openings',
		'09_finishes'                            => '09 Finishes',
		'10_specialties'                         => '10 Specialties',
		'11_equipment'                           => '11 Equipment',
		'12_furnishings'                         => '12 Furnishings',
		'13_specialconstruction'                 => '13 Special Construction',
		'14_conveyingequipment'                  => '14 Conveying Equipment',
		'21_firesuppression'                     => '21 Fire Suppression',
		'22_plumbing'                            => '22 Plumbing',
		'23_hvac'                                => '23 Heating, Ventilating, and Air Conditioning',
		'25_integratedautomation'                => '25 Integrated Automation',
		'26_electrical'                          => '26 Electrical',
		'27_communications'                      => '27 Communications',
		'28_electronicsafetysecurity'            => '28 Electronic Safety and Security',
		'31_earthwork'                           => '31 Earthwork',
		'32_exteriorimprovements'                => '32 Exterior Improvements',
		'33_utilities'                           => '33 Utilities',
		'34_transportation'                      => '34 Transportation',
		'35_marine'                              => '35 Waterway and Marine Construction',
		'40_processintegration'                  => '40 Process Integration',
		'41_materialprocessinghandlingequip'     => '41 Material Processing and Handling Equipment',
		'42_processheatingcoolingdryingequip'    => '42 Process Heating, Cooling, and Drying Equipment',
		'43_processgasliquidequip'               => '43 Process Gas and Liquid Handling, Purification, and Storage Equipment',
		'44_pollutionwastecontrolequip'          => '44 Pollution and Waste Control Equipment',
		'45_industryspecificmanufacturingequip'  => '45 Industry Specific Manufacturing Equipment',
		'46_waterwastewaterequip'                => '46 Water and Wastewater Equipment',
		'48_electricalpowergeneration'           => '48 Electrical Power Generation',
		'64_medicallabequip'                     => '64 MRS Medical and Lab Equipment'
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
	add_option( 'emanager_installed_csidivisions', current_time('timestamp') );
}