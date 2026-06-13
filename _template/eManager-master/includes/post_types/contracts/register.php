<?php

add_action( 'init', 'eman_register_post_type_contracts' );
function eman_register_post_type_contracts()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Contracts',
		'singular_name'      => 'Contract',
		'add_new_item'       => 'Add new Contract',
		'edit_item'          => 'Edit Contract',
		'new_item'           => 'New Contract',
		'view_item'          => 'View Contract',
		'search_items'       => 'Search Contracts',
		'not_found'          => 'No Contract found',
		'not_found_in_trash' => 'No Contract found in trash',
		'parent_item_colon'  => 'Parent Contract',
	);
	$emanager = array(
		'title_label' => false,
		'icon'        => 'file-text',
		'form'        => 'acf_contracts',
		'type'        => 'settings',
		'access'      => array(),
		'table_cols'  => array(
			'title'           => 'Title',
			'company'         => 'Company',
			'bid_description' => 'Package',
		),
	);
	register_post_type( 'em_contracts', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_settings',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'settings/contract', 'feeds' => false ),
		'has_archive'        => 'settings/contracts',
		'emanager'           => $emanager,
	) );
}