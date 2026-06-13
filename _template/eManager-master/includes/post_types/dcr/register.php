<?php

add_action( 'init', 'eman_register_post_type_dcr' );
function eman_register_post_type_dcr()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'DCRs',
		'singular_name'      => 'DCR',
		'add_new_item'       => 'Add new DCR',
		'edit_item'          => 'Edit DCR',
		'new_item'           => 'New DCR',
		'view_item'          => 'View DCR',
		'search_items'       => 'Search DCRs',
		'not_found'          => 'No DCR found',
		'not_found_in_trash' => 'No DCR found in trash',
		'parent_item_colon'  => 'Parent DCR',
	);
	$emanager = array(
		'title_label' => false,#'DCR Subject',
		'icon'        => 'calendar',
		'form'        => 'acf_dcr',
		'type'        => 'dashboard',
		'access'      => array('sub'),
		'table_cols'  => array(
			#'ID'          => 'ID',
			'title'       => 'Title',
			'status'      => 'Status',
			#'createdby' => 'Created By',
			'company'     => 'Company',
			'reviewer'    => 'BIC',
			'work_date'   => 'Work Date',
		),
	);

	register_post_type( 'em_dcr', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_field',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'dcr', 'feeds' => false ),
		'has_archive'        => 'dcr',
		'emanager'           => $emanager,
	) );
}