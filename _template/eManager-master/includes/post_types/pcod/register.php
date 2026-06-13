<?php

add_action( 'init', 'eman_register_post_type_pcod' );
function eman_register_post_type_pcod()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'PCO Directives',
		'singular_name'      => 'PCO Directive',
		'add_new_item'       => 'Add new PCO Directive',
		'edit_item'          => 'Edit PCO Directive',
		'new_item'           => 'New PCO Directive',
		'view_item'          => 'View PCO Directive',
		'search_items'       => 'Search PCO Directives',
		'not_found'          => 'No PCO Directive found',
		'not_found_in_trash' => 'No PCO Directive found in trash',
		'parent_item_colon'  => 'Parent PCO Directive',
	);
	$emanager = array(
		'title_label' => 'Directive Title',
		'icon'        => 'list-alt',
		'form'        => array('acf_pcod','acf_pcod-signature'),
		'type'        => 'dashboard',
		'access'      => array('turner','owner'),
		'table_cols'  => array(
			'title'           => 'Title',
			'pcod_noc_number' => 'Noc Number',
			'pcod_pco_number' => 'PCO Number',
			'status'          => 'Status',
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
	register_post_type( 'em_pcod', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_change',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'pcod', 'feeds' => false ),
		'has_archive'        => 'pcod',
		'emanager'           => $emanager,
	) );
}