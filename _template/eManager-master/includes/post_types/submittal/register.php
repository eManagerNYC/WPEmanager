<?php

add_action( 'init', 'eman_register_post_type_submittal' );
function eman_register_post_type_submittal()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Submittals',
		'singular_name'      => 'Submittal',
		'add_new_item'       => 'Add new Submittal',
		'edit_item'          => 'Edit Submittal',
		'new_item'           => 'New Submittal',
		'view_item'          => 'View Submittal',
		'search_items'       => 'Search Submittals',
		'not_found'          => 'No Submittal found',
		'not_found_in_trash' => 'No Submittal found in trash',
		'parent_item_colon'  => 'Parent Submittal',
	);
	$emanager = array(
		'title_label'        => 'Submittal Label',
		'icon'               => 'list-alt',
		'form'               => array('acf_submittal','acf_submittal-signature'),
		'type'               => 'dashboard',
		'access'             => array('turner','owner'),
		'table_cols'         => array(
			'date_created'           => 'Date Created'
		),
		'taxonomy'       => array(
			'name'           => 'em_status',
			'terms'          => array(
				'draft'          => 'Draft',
				'in_progress'           => 'In Progress',
				'completed'         => 'Completed'
			),
		),
	);
	register_post_type( 'em_submittal', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_field',
		'exclude_from_search'=> true,
		'has_archive'        => true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'submittal', 'feeds' => false ),
		'has_archive'        => 'submittals',
		'emanager'           => $emanager,
	) );
}