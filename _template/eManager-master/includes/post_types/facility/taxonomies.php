<?php

add_action( 'init', 'eman_register_taxonomy_facility' );
function eman_register_taxonomy_facility()
{
	register_taxonomy( 'em_buildingsystem',
		array('em_facility'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Building Systems',
				'singular_name'     => 'Building System',
				'search_items'      => 'Search Building System',
				'all_items'         => 'All Building Systems',
				'parent_item'       => 'Parent Building System',
				'parent_item_colon' => 'Parent Building System:',
				'edit_item'         => 'Edit Building System',
				'update_item'       => 'Update Building System',
				'add_new_item'      => 'Add New Building System',
				'new_item_name'     => 'Building System Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);
	register_taxonomy( 'em_buildingequipmenttype',
		array('em_facility'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Building Equipment Types',
				'singular_name'     => 'Building Equipment Type',
				'search_items'      => 'Search Building Equipment Type',
				'all_items'         => 'All Building Equipment Types',
				'parent_item'       => 'Parent Building Equipment Type',
				'parent_item_colon' => 'Parent Building Equipment Type:',
				'edit_item'         => 'Edit Building Equipment Type',
				'update_item'       => 'Update Building Equipment Type',
				'add_new_item'      => 'Add New Building Equipment Type',
				'new_item_name'     => 'Building Equipment Type Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);
}