<?php

add_action( 'init', 'eman_register_taxonomy_inspection' );
function eman_register_taxonomy_inspection()
{
	register_taxonomy( 'em_inspectiontype',
		array('em_inspection'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Inspection Types',
				'singular_name'     => 'Inspection Type',
				'search_items'      => 'Search Inspection Type',
				'all_items'         => 'All Inspection Types',
				'parent_item'       => 'Parent Inspection Type',
				'parent_item_colon' => 'Parent Inspection Type:',
				'edit_item'         => 'Edit Inspection Type',
				'update_item'       => 'Update Inspection Type',
				'add_new_item'      => 'Add New Inspection Type',
				'new_item_name'     => 'Inspection Type Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);
}