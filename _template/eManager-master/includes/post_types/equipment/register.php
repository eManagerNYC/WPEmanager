<?php

add_action( 'init', 'eman_register_post_type_equipment' );
function eman_register_post_type_equipment()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Equipment',
		'singular_name'      => 'Equipment',
		'add_new_item'       => 'Add new Equipment',
		'edit_item'          => 'Edit Equipment',
		'new_item'           => 'New Equipment',
		'view_item'          => 'View Equipment',
		'search_items'       => 'Search Equipment',
		'not_found'          => 'No Equipment found',
		'not_found_in_trash' => 'No Equipment found in trash',
		'parent_item_colon'  => 'Parent Equipment',
	);
	$emanager = array(
		'title_label' => 'Equpiment Name',
		'icon'        => 'truck',
		'form'        => 'acf_equipment',
		'type'        => 'settings',
		'access'      => array('sub'),
		'table_cols'  => array(
			'title'       => 'Title',
			'company'     => 'Company',
			'rate'        => 'Rate',
		),
	);
	register_post_type( 'em_equipment', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_settings',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'settings/equipment', 'feeds' => false ),
		'has_archive'        => 'settings/equipment',
		'emanager'           => $emanager,
	) );
}