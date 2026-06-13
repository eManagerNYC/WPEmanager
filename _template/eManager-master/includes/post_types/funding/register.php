<?php

add_action( 'init', 'eman_register_post_type_funding' );
function eman_register_post_type_funding()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Funding Sources',
		'singular_name'      => 'Funding Source',
		'add_new_item'       => 'Add new Funding Source',
		'edit_item'          => 'Edit Funding Source',
		'new_item'           => 'New Funding Source',
		'view_item'          => 'View Funding Source',
		'search_items'       => 'Search Funding Sources',
		'not_found'          => 'No Funding Source found',
		'not_found_in_trash' => 'No Funding Source found in trash',
		'parent_item_colon'  => 'Parent Funding Source',
	);
	$emanager = array(
		'title_label' => 'Funding Source',
		'icon'        => 'dollar',
		'form'        => false,
		'type'        => 'settings',
		'access'      => array(),
        'table_cols'  => array(
            'title'			=> 'Title',
            'createdby'		=> 'Created by',
		),
	);
	register_post_type( 'em_funding', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_settings',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'settings/funding', 'feeds' => false ),
		'has_archive'        => 'settings/funding',
		'emanager'           => $emanager,
	) );
}
