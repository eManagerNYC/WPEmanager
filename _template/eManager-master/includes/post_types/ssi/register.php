<?php

add_action( 'init', 'eman_register_post_type_ssi' );
function eman_register_post_type_ssi()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'SSIs',
		'singular_name'      => 'SSI',
		'add_new_item'       => 'Add new SSI',
		'edit_item'          => 'Edit SSI',
		'new_item'           => 'New SSI',
		'view_item'          => 'View SSI',
		'search_items'       => 'Search SSIs',
		'not_found'          => 'No SSI found',
		'not_found_in_trash' => 'No SSI found in trash',
		'parent_item_colon'  => 'Parent SSI',
	);
	$emanager = array(
		'title_label' => 'Scope Title',
		'icon'        => 'list-alt',
		'form'        => array('acf_ssi','acf_ssi-signature'),
		'type'        => 'dashboard',
		'access'      => array('owner'),
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
	register_post_type( 'em_ssi', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_change',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'ssi', 'feeds' => false ),
		'has_archive'        => 'ssi',
		'emanager'           => $emanager,
	) );
}