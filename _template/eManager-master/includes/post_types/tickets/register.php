<?php

add_action( 'init', 'eman_register_post_type_tickets' );
function eman_register_post_type_tickets()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Tickets',
		'singular_name'      => 'Ticket',
		'add_new_item'       => 'Add new Ticket',
		'edit_item'          => 'Edit Ticket',
		'new_item'           => 'New Ticket',
		'view_item'          => 'View Ticket',
		'search_items'       => 'Search Tickets',
		'not_found'          => 'No Ticket found',
		'not_found_in_trash' => 'No Ticket found in trash',
		'parent_item_colon'  => 'Parent Ticket',
	);
	$emanager = array(
		'title_label' => 'Work Subject',
		'icon'        => 'list-alt',
		'form'        => array('acf_tickets','acf_tickets-signature'),
		'type'        => 'dashboard',
		'access'      => array('sub'),
		'table_cols'  => array(
			'request_number' => 'Ticket#',
			'pco_number'  => 'PCO#',
			'ewo_number'  => 'EWO#',
			'work_date'   => 'Work Date',
			'title'       => 'Title',
			'status'      => 'Status',
			'reviewer'    => 'BIC',
			'company'     => 'Company',
		),
		'taxonomy'    => array(
			'name'        => 'em_status',
			'terms'       => array(
				'draft'          => 'Draft',
				'superintendent' => 'Superintendent Review',
				'supervisor'     => 'Supervisor Review',
				'verified'       => 'Time and Material Verified',
				'revise'         => 'Revise',
				'void'           => 'Void',
			),
		),
	);
	register_post_type( 'em_tickets', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_change',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'ticket', 'feeds' => false ),
		'has_archive'        => 'tickets',
		'emanager'           => $emanager,
	) );
}
