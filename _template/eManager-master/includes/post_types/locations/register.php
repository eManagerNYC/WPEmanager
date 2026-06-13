<?php

add_action( 'init', 'eman_register_post_type_locations' );
function eman_register_post_type_locations()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Locations',
		'singular_name'      => 'Location',
		'add_new_item'       => 'Add new Location',
		'edit_item'          => 'Edit Location',
		'new_item'           => 'New Location',
		'view_item'          => 'View Location',
		'search_items'       => 'Search Locations',
		'not_found'          => 'No Location found',
		'not_found_in_trash' => 'No Location found in trash',
		'parent_item_colon'  => 'Parent Location',
	);
	$emanager = array(
		'title_label' => false,#'Area Name',
		'icon'        => 'building-o',
		'form'        => 'acf_locations',
		'type'        => 'settings',
		'access'      => array(),
		'table_cols'  => array(
			'title'       => 'Title',
			'plan'        => 'Plan',
		),
	);
	register_post_type( 'em_locations', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_settings',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'settings/location', 'feeds' => false ),
		'has_archive'        => 'settings/locations',
		'emanager'           => $emanager,
	) );
}