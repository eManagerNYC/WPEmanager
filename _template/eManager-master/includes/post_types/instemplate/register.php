<?php

add_action( 'init', 'eman_register_post_type_instemplate' );
function eman_register_post_type_instemplate()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Inspection Templates',
		'singular_name'      => 'Inspection Template',
		'add_new_item'       => 'Add new Inspection Template',
		'edit_item'          => 'Edit Inspection Template',
		'new_item'           => 'New Inspection Template',
		'view_item'          => 'View Inspection Template',
		'search_items'       => 'Search Inspection Templates',
		'not_found'          => 'No Inspection Template found',
		'not_found_in_trash' => 'No Inspection Template found in trash',
		'parent_item_colon'  => 'Parent Inspection Template',
	);
	$emanager = array(
		'title_label'        => 'Isnpection Template',
		'icon'               => 'list-alt',
		'form'               => array('acf_inspection-templates'),
		'type'               => 'dashboard',
		'access'             => array('turner'),
		'table_cols'         => array(),
		'taxonomy'       => array(),
	);
	register_post_type( 'em_instemplate', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_field',
		'exclude_from_search'=> true,
		'has_archive'        => true,
		'menu_position'      => 1,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'inspection/template', 'feeds' => false ),
		'has_archive'        => 'inspection/template',
		'emanager'           => $emanager,
	) );
}