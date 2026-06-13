<?php

add_action( 'init', 'eman_register_post_type_package' );
function eman_register_post_type_package()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Packages',
		'singular_name'      => 'Package',
		'add_new_item'       => 'Add new Package',
		'edit_item'          => 'Edit Package',
		'new_item'           => 'New Package',
		'view_item'          => 'View Package',
		'search_items'       => 'Search Packages',
		'not_found'          => 'No Package found',
		'not_found_in_trash' => 'No Package found in trash',
		'parent_item_colon'  => 'Parent Package',
	);
	$emanager = array(
		'title_label'        => 'Package Label',
		'icon'               => 'list-alt',
		'form'               => array('acf_package','acf_package-signature'),
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
	register_post_type( 'em_package', array(
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
		'rewrite'            => array( 'slug' => 'package', 'feeds' => false ),
		'has_archive'        => 'packagess',
		'emanager'           => $emanager,
	) );
}