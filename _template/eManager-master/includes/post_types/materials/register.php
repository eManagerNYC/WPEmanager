<?php

add_action( 'init', 'eman_register_post_type_materials' );
function eman_register_post_type_materials()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Materials',
		'singular_name'      => 'Material',
		'add_new_item'       => 'Add new Material',
		'edit_item'          => 'Edit Material',
		'new_item'           => 'New Material',
		'view_item'          => 'View Material',
		'search_items'       => 'Search Materials',
		'not_found'          => 'No Material found',
		'not_found_in_trash' => 'No Material found in trash',
		'parent_item_colon'  => 'Parent Material',
	);
	$emanager = array(
		'title_label' => 'Material Name',
		'icon'        => 'wrench',
		'form'        => 'acf_materials',
		'type'        => 'settings',
		'access'      => array('sub'),
		'table_cols'  => array(
			'title'       => 'Title',
			'company'     => 'Company',
			'price'       => 'Price'
		),
	);
	register_post_type( 'em_materials', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_settings',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'settings/material', 'feeds' => false ),
		'has_archive'        => 'settings/materials',
		'emanager'           => $emanager,
	) );
}