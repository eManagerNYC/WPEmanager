<?php

add_action( 'init', 'eman_register_post_type_inspection' );
function eman_register_post_type_inspection()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Inspections',
		'singular_name'      => 'Inspection',
		'add_new_item'       => 'Add new Inspection',
		'edit_item'          => 'Edit Inspection',
		'new_item'           => 'New Inspection',
		'view_item'          => 'View Inspection',
		'search_items'       => 'Search Inspections',
		'not_found'          => 'No Inspection found',
		'not_found_in_trash' => 'No Inspection found in trash',
		'parent_item_colon'  => 'Parent Inspection',
	);
	$emanager = array(
		'title_label'        => 'Inspection',
		'icon'               => 'list-alt',
		'form'               => array('acf_inspection','acf_inspection-signature'),
		'type'               => 'dashboard',
		'access'             => array('turner'),
		'table_cols'         => array(
			'title'              => 'Title',
			'date'               => 'date',
			'location'           => 'location',
		),
		'taxonomy'       => array(
			'name'           => 'em_status',
			'terms'          => array(
				'draft'          => 'Draft',
				'in_progress'    => 'In Progress',
				'approved'       => 'Approved',
			),
		),
	);
	register_post_type( 'em_inspection', array(
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
		'rewrite'            => array( 'slug' => 'inspection', 'feeds' => false ),
		'has_archive'        => 'inspection',
		'emanager'           => $emanager,
	) );
}