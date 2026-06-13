<?php

add_action( 'init', 'eman_register_post_type_actionitem' );
function eman_register_post_type_actionitem()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Action Items',
		'singular_name'      => 'Action Item',
		'add_new_item'       => 'Add new Action Item',
		'edit_item'          => 'Edit Action Item',
		'new_item'           => 'New Action Item',
		'view_item'          => 'View Action Item',
		'search_items'       => 'Search Action Items',
		'not_found'          => 'No Action Item found',
		'not_found_in_trash' => 'No Action Item found in trash',
		'parent_item_colon'  => 'Parent Action Item',
	);
	$emanager = array(
		'title_label'        => 'Action Item Label',
		'icon'               => 'list-alt',
		'form'               => array('acf_actionitem','acf_actionitem-signature'),
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
	register_post_type( 'em_actionitem', array(
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
		'rewrite'            => array( 'slug' => 'actionitem', 'feeds' => false ),
		'has_archive'        => 'actionitems',
		'emanager'           => $emanager,
	) );
}