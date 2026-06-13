<?php

add_action( 'init', 'eman_register_post_type_facility' );
function eman_register_post_type_facility()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Facility Management',
		'singular_name'      => 'Facility Management',
		'add_new_item'       => 'Add new Facility Management',
		'edit_item'          => 'Edit Facility Management',
		'new_item'           => 'New Facility Management',
		'view_item'          => 'View Facility Management',
		'search_items'       => 'Search Facility Management',
		'not_found'          => 'No Facility Management found',
		'not_found_in_trash' => 'No Facility Management found in trash',
		'parent_item_colon'  => 'Parent Facility Management',
	);
	$emanager = array();
	register_post_type( 'em_facility', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'exclude_from_search'=> true,
		'has_archive'        => true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'facility', 'feeds' => false ),
		'has_archive'        => 'facility',
		'emanager'           => $emanager,
	) );
}