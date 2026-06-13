<?php

add_action( 'init', 'eman_register_post_type_activities' );
function eman_register_post_type_activities()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Activities',
		'singular_name'      => 'Activity',
		'add_new_item'       => 'Add new Activity',
		'edit_item'          => 'Edit Activity',
		'new_item'           => 'New Activity',
		'view_item'          => 'View Activity',
		'search_items'       => 'Search Activitys',
		'not_found'          => 'No Activity found',
		'not_found_in_trash' => 'No Activity found in trash',
		'parent_item_colon'  => 'Parent Activity',
	);
	$emanager = array(
		'title_label'        => 'Activity Label',
		'icon'               => 'list-alt',
		'form'               => array('acf_activities','acf_activities-signature'),
		'type'               => 'dashboard',
		'access'             => array('turner'),
		'table_cols'         => array(
			'title'              => 'Title',
			'location'           => 'location',
			'estimated_start'       => 'Estimated Start',
			'duration'              => 'Duration'
			// 'actual_start'          => 'Actual Start'
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
	register_post_type( 'em_activities', array(
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
		'rewrite'            => array( 'slug' => 'activity', 'feeds' => false ),
		'has_archive'        => 'activities',
		'emanager'           => $emanager,
	) );
}