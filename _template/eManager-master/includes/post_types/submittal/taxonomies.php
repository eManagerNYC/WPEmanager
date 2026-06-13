<?php

add_action( 'init', 'eman_register_taxonomy_submittal' );
function eman_register_taxonomy_submittal()
{
	register_taxonomy( 'em_submittaltype',
		array('em_submittal'),
		array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'              => 'Submittal Types',
				'singular_name'     => 'Submittal Type',
				'search_items'      => 'Search Submittal Type',
				'all_items'         => 'All Submittal Types',
				'parent_item'       => 'Parent Submittal Type',
				'parent_item_colon' => 'Parent Submittal Type:',
				'edit_item'         => 'Edit Submittal Type',
				'update_item'       => 'Update Submittal Type',
				'add_new_item'      => 'Add New Submittal Type',
				'new_item_name'     => 'Submittal Type Name',
			),
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		)
	);
}