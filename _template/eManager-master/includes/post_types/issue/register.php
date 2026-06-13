<?php

add_action( 'init', 'eman_register_post_type_issue' );
function eman_register_post_type_issue()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Issues',
		'singular_name'      => 'Issue',
		'add_new_item'       => 'Add new Issue',
		'edit_item'          => 'Edit Issue',
		'new_item'           => 'New Issue',
		'view_item'          => 'View Issue',
		'search_items'       => 'Search Issues',
		'not_found'          => 'No Issue found',
		'not_found_in_trash' => 'No Issue found in trash',
		'parent_item_colon'  => 'Parent Issue',
	);
	$emanager = array(
		'title_label'        => 'Issue Label',
		'icon'               => 'list-ol',
		'form'               => array('acf_issue-item','acf_issue-owner','acf_issue-turner','acf_issue-signature'),
		'type'               => 'dashboard',
		'access'             => array('turner','owner','sub'),
		'table_cols'         => array(
			'title'              => 'Title',
			'company'            => 'Company',
			'contract'           => 'Assigned Contract',
			'scope'              => 'Location',
			'issue_type'         => 'Issue Type',
			'status'             => 'Status',
		),
		'taxonomy'       => array(),
	);
	register_post_type( 'em_issue', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_field',
		'exclude_from_search'=> true,
		'has_archive'        => true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'issue', 'feeds' => false ),
		'has_archive'        => 'issues',
		'emanager'           => $emanager,
	) );
}