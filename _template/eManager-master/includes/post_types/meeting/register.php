<?php

add_action( 'init', 'eman_register_post_type_meeting' );
function eman_register_post_type_meeting()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Meeting Minutes',
		'singular_name'      => 'Meeting Minute',
		'add_new_item'       => 'Add new Meeting Minute',
		'edit_item'          => 'Edit Meeting Minute',
		'new_item'           => 'New Meeting Minute',
		'view_item'          => 'View Meeting Minute',
		'search_items'       => 'Search Meeting Minutes',
		'not_found'          => 'No Meeting Minute found',
		'not_found_in_trash' => 'No Meeting Minute found in trash',
		'parent_item_colon'  => 'Parent Meeting Minute',
	);
	$emanager = array(
		'title_label'        => 'Meeting Label',
		'icon'               => 'list-alt',
		'form'               => array('acf_meeting','acf_meeting-signature'),
		'type'               => 'dashboard',
		'access'             => array('turner'),
		'table_cols'         => array(
			'title'              => 'Title',
			'date_created'       => 'Date Created'
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
	register_post_type( 'em_meeting', array(
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
		'rewrite'            => array( 'slug' => 'meeting', 'feeds' => false ),
		'has_archive'        => 'meetings',
		'emanager'           => $emanager,
	) );
}