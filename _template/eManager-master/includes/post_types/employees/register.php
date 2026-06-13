<?php

add_action( 'init', 'eman_register_post_type_employees' );
function eman_register_post_type_employees()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Employees',
		'singular_name'      => 'Employee',
		'add_new_item'       => 'Add new Employee',
		'edit_item'          => 'Edit Employee',
		'new_item'           => 'New Employee',
		'view_item'          => 'View Employee',
		'search_items'       => 'Search Employees',
		'not_found'          => 'No Employee found',
		'not_found_in_trash' => 'No Employee found in trash',
		'parent_item_colon'  => 'Parent Employee',
	);
	$emanager = array(
		'title_label' => 'Employee Name',
		'icon'        => 'user',
		'form'        => 'acf_employees',
		'type'        => 'settings',
		'access'      => array('sub'),
		'table_cols'  => array(
			'title'       => 'Title',
			'company'     => 'Company',
			'phone'       => 'Phone',
			'email'       => 'Email',
		),
	);
	register_post_type( 'em_employees', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_settings',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'settings/employee', 'feeds' => false ),
		'has_archive'        => 'settings/employees',
		'emanager'           => $emanager,
	) );
}