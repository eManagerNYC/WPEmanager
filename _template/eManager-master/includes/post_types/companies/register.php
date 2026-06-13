<?php

add_action( 'init', 'eman_register_post_type_companies' );
function eman_register_post_type_companies()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Companies',
		'singular_name'      => 'Company',
		'add_new_item'       => 'Add new Company',
		'edit_item'          => 'Edit Company',
		'new_item'           => 'New Company',
		'view_item'          => 'View Company',
		'search_items'       => 'Search Companies',
		'not_found'          => 'No Company found',
		'not_found_in_trash' => 'No Company found in trash',
		'parent_item_colon'  => 'Parent Companies',
		'menu_name'          => 'Companies'
	);
	$emanager = array(
		'title_label' => 'Company Name',
		'icon'        => 'group',
		'form'        => 'acf_companies',
		'type'        => 'settings',
		'access'      => array(),
		'table_cols'  => array(
			'title'              => 'Title',
			'address'     => 'Address',
			'website'     => 'Website',
		),
	);
	register_post_type( 'em_companies', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_settings',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'settings/company', 'feeds' => false ),
		'has_archive'        => 'settings/companies',
		'emanager'           => $emanager,
	) );
}