<?php

add_action( 'init', 'eman_register_taxonomy_contracts' );
function eman_register_taxonomy_contracts()
{
	register_taxonomy( 'em_csidivisions',
		array('em_contracts'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Divisions',
				'singular_name'     => 'Division',
				'search_items'      => 'Search Divisions',
				'all_items'         => 'All Divisions',
				'parent_item'       => 'Parent Division',
				'parent_item_colon' => 'Parent Division:',
				'edit_item'         => 'Edit Division',
				'update_item'       => 'Update Division',
				'add_new_item'      => 'Add New Division',
				'new_item_name'     => 'Status Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);
}