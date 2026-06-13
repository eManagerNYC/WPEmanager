<?php

add_action( 'init', 'eman_register_post_type_observation' );
function eman_register_post_type_observation()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Observations',
		'singular_name'      => 'Observation',
		'add_new_item'       => 'Add new Observation',
		'edit_item'          => 'Edit Observation',
		'new_item'           => 'New Observation',
		'view_item'          => 'View Observation',
		'search_items'       => 'Search Observations',
		'not_found'          => 'No Observation found',
		'not_found_in_trash' => 'No Observation found in trash',
		'parent_item_colon'  => 'Parent Observation',
	);
	$emanager = array(
		'title_label'        => 'Observation Label',
		'icon'               => 'list-alt',
		'form'               => array('acf_observation'),
		'type'               => 'dashboard',
		'access'             => array('turner'),
		'table_cols'         => array(
			'datetime'           => 'Date & Time',
			'title'       => 'Title',
			'createdby'   => 'Observer',
			'reviewer'    => 'BIC'
		),
		'taxonomy'       => array(),
	);
	register_post_type( 'em_observation', array(
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
		'rewrite'            => array( 'slug' => 'observation', 'feeds' => false ),
		'has_archive'        => 'observations',
		'emanager'           => $emanager,
	) );
}