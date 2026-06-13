<?php

add_action( 'init', 'eman_register_post_type_labortypes' );
function eman_register_post_type_labortypes()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Labor Types',
		'singular_name'      => 'Labor Type',
		'add_new_item'       => 'Add new Labor Type',
		'edit_item'          => 'Edit Labor Type',
		'new_item'           => 'New Labor Type',
		'view_item'          => 'View Labor Type',
		'search_items'       => 'Search Labor Types',
		'not_found'          => 'No Labor Type found',
		'not_found_in_trash' => 'No Labor Type found in trash',
		'parent_item_colon'  => 'Parent Labor Type',
	);
	$emanager = array(
		'title_label' => 'Labor Class Name',
		'icon'        => 'male',
		'form'        => 'acf_labortypes',
		'type'        => 'settings',
		'access'      => array('sub'),
		'table_cols'  => array(
			'title'       => 'Title',
			'company'     => 'Company',
			'rt'          => 'Regular',
			'pt'          => 'Premium',
			'ot'          => 'Overtime',
			'dt'          => 'Double',
			'pdt'         => 'Premium Double',
		),
	);
	register_post_type( 'em_labortypes', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_settings',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'settings/labortype', 'feeds' => false ),
		'has_archive'        => 'settings/labortypes',
		'emanager'           => $emanager,
	) );
}