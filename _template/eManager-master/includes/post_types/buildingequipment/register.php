<?php

add_action( 'init', 'eman_register_post_type_buildingequipment' );
function eman_register_post_type_buildingequipment()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Building Equipment',
		'singular_name'      => 'Building Equipment',
		'add_new_item'       => 'Add new Building Equipment',
		'edit_item'          => 'Edit Building Equipment',
		'new_item'           => 'New Building Equipment',
		'view_item'          => 'View Building Equipment',
		'search_items'       => 'Search Building Equipment',
		'not_found'          => 'No Building Equipment found',
		'not_found_in_trash' => 'No Building Equipment found in trash',
		'parent_item_colon'  => 'Parent Building Equipment',
	);
	$emanager = array(
		'title_label' => 'Building Equipment',
		'icon'        => 'list-alt',
		'form'        => array('acf_building-systems-equipment-item' ),
		'type'        => 'dashboard',
		'access'      => array('turner'),
		'table_cols'  => array(
			'title'              => 'Title',
			'building_system'    => 'System',
			'equipment_type'     => 'Equipment Type',
		),
	);
	register_post_type( 'em_buildingequipment', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'edit.php?post_type=em_facility',
		'exclude_from_search'=> true,
		'has_archive'        => true,
		'menu_position'      => 1,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'buildingequipment', 'feeds' => false ),
		'has_archive'        => 'buildingequipment',
		'emanager'           => $emanager,
	) );
}