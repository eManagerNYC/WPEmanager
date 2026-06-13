<?php

add_action( 'init', 'eman_register_post_type_letter' );
function eman_register_post_type_letter()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Letters',
		'singular_name'      => 'Letter',
		'add_new_item'       => 'Add new Letter',
		'edit_item'          => 'Edit Letter',
		'new_item'           => 'New Letter',
		'view_item'          => 'View Letter',
		'search_items'       => 'Search Letters',
		'not_found'          => 'No Letter found',
		'not_found_in_trash' => 'No Letter found in trash',
		'parent_item_colon'  => 'Parent Letter',
	);
	$emanager = array(
		'title_label' => 'Subject',
		'icon'        => 'random',
		'form'        => 'acf_letter',
		'type'        => 'dashboard',
		'access'      => array('turner','owner'),
		'table_cols'  => array(
			'letter_type' => 'Type',
			'letter_number'  => 'Letter#',
			'revision' => 'Rev#',
			'pco_number'  => 'PCO#',
			'sub_number'  => 'Contractor#',
			'title'       => 'Title',
			'date'  => 'Submitted',
			#'date_modified' => 'Modified',
			'status'      => 'Status',
			'value'   => 'Value',
			'createdby'   => 'Requester',
			'reviewer'    => 'BIC',
			'bic_company'    => 'BIC Company',
		),
		'taxonomy'    => array(
			'name'  => 'em_status',
			'terms' => array(
				'draft'      => 'Draft',
				'ready'      => 'Ready to Submit',
				'pending'    => 'Pending',
				'returned'   => 'Returned',
				'revise'     => 'Revise',
				'void'       => 'Void',
			),
		),
	);
	register_post_type( 'em_letter', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_change',
		'exclude_from_search'=> true,
		'has_archive'        => true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'letter', 'feeds' => false ),
		'has_archive'        => 'letter',
		'emanager'           => $emanager,
	) );
}