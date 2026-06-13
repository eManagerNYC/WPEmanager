<?php

add_action( 'init', 'eman_register_post_type_photos' );
function eman_register_post_type_photos()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Photos',
		'singular_name'      => 'Photo',
		'add_new_item'       => 'Add new Photo',
		'edit_item'          => 'Edit Photo',
		'new_item'           => 'New Photo',
		'view_item'          => 'View Photo',
		'search_items'       => 'Search Photos',
		'not_found'          => 'No Photo found',
		'not_found_in_trash' => 'No Photo found in trash',
		'parent_item_colon'  => 'Parent Photo',
	);
	$emanager = array(
		'title_label'        => 'Photo Label',
		'icon'               => 'list-alt',
		'form'               => array('acf_photos','acf_photos-signature'),
		'type'               => 'dashboard',
		'access'             => array('turner'),
		'table_cols'         => array(
			'title'              => 'Title',
			'date_taken'         => 'Date Taken',
			'location'           => 'Location',
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
	register_post_type( 'em_photos', array(
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
		'rewrite'            => array( 'slug' => 'photo', 'feeds' => false ),
		'has_archive'        => 'photos',
		'emanager'           => $emanager,
	) );
}