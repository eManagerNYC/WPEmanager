<?php

add_action( 'init', 'eman_register_taxonomy_tickets' );
function eman_register_taxonomy_tickets()
{
	register_taxonomy( 'em_charge',
		array('em_tickets'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Charged',
				'singular_name'     => 'Charged',
				'search_items'      => 'Search Charged',
				'all_items'         => 'All Charged',
				'parent_item'       => 'Parent Charged',
				'parent_item_colon' => 'Parent Charged:',
				'edit_item'         => 'Edit Charged',
				'update_item'       => 'Update Charged',
				'add_new_item'      => 'Add New Charged',
				'new_item_name'     => 'Status Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);
}