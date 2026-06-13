<?php

add_action( 'init', 'eman_register_taxonomy_issue' );
function eman_register_taxonomy_issue()
{
	register_taxonomy( 'em_punchlist',
		array('em_issue'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Punchlists',
				'singular_name'     => 'Punchlist',
				'search_items'      => 'Search Punchlist',
				'all_items'         => 'All Punchlists',
				'parent_item'       => 'Parent Punchlist',
				'parent_item_colon' => 'Parent Punchlist:',
				'edit_item'         => 'Edit Punchlist',
				'update_item'       => 'Update Punchlist',
				'add_new_item'      => 'Add New Punchlist',
				'new_item_name'     => 'Punchlist Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);
}