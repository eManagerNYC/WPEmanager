<?php

add_action( 'after_setup_theme', 'eman_register_taxonomy_site' );
function eman_register_taxonomy_site()
{
	/**
	 * Set up the status taxonomy, and we will use it for all of the items that need status
	 */
	register_taxonomy( 'em_status',
		array('em_noc', 'em_tickets', 'em_dcr', 'em_reviews', 'em_issue', 'em_letter'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Statuses',
				'singular_name'     => 'Status',
				'search_items'      => 'Search Status',
				'all_items'         => 'All Statuses',
				'parent_item'       => 'Parent Status',
				'parent_item_colon' => 'Parent Status:',
				'edit_item'         => 'Edit Status',
				'update_item'       => 'Update Status',
				'add_new_item'      => 'Add New Status',
				'new_item_name'     => 'Status Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);

	register_taxonomy( 'em_clboxes',
		array('em_checklist'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Checklist Boxes',
				'singular_name'     => 'Checklist Box',
				'search_items'      => 'Search Checklist Box',
				'all_items'         => 'All Checklist Boxes',
				'parent_item'       => 'Parent Checklist Box',
				'parent_item_colon' => 'Parent Checklist Box:',
				'edit_item'         => 'Edit Checklist Box',
				'update_item'       => 'Update Checklist Box',
				'add_new_item'      => 'Add New Checklist Box',
				'new_item_name'     => 'Checklist Box Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);
}