<?php

add_action( 'init', 'eman_register_post_type_ssid' );
function eman_register_post_type_ssid()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'SSIDs',
		'singular_name'      => 'SSID',
		'add_new_item'       => 'Add new SSID',
		'edit_item'          => 'Edit SSID',
		'new_item'           => 'New SSID',
		'view_item'          => 'View SSID',
		'search_items'       => 'Search SSIDs',
		'not_found'          => 'No SSID found',
		'not_found_in_trash' => 'No SSID found in trash',
		'parent_item_colon'  => 'Parent SSID',
	);
	$emanager = array(
		'title_label' => 'Directive Title',
		'icon'        => 'list-alt',
		'form'        => array('acf_ssid','acf_ssi-signature'),
		'type'        => 'dashboard',
		'access'      => array('turner'),
		'table_cols'  => array(
			'title'       => 'Title',
			'status'      => 'Status',
			'issue_to'    => 'Issue To',
		),
		'taxonomy'    => array(
			'name'    => 'em_status',
			'terms'   => array(
				'draft'    => 'Draft',
				'pending'  => 'Pending',
				'approved' => 'Approved',
			),
		),
	);
	register_post_type( 'em_ssid', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_change',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'ssid', 'feeds' => false ),
		'has_archive'        => 'ssid',
		'emanager'           => $emanager,
	) );
}