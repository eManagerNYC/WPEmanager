<?php

add_action( 'init', 'eman_register_post_type_noc' );
function eman_register_post_type_noc()
{
	$name = 'NOC';
	if ( ! empty($_SERVER['SERVER_NAME']) && false !== strpos( $_SERVER['SERVER_NAME'], 'nyuk') ) {
		$name = 'PCO';
	}
	$menu_position = 30;
	$labels = array(
		'name'               => $name . 's',
		'singular_name'      => $name,
		'add_new_item'       => 'Add new ' . $name,
		'edit_item'          => 'Edit ' . $name,
		'new_item'           => 'New ' . $name,
		'view_item'          => 'View ' . $name,
		'search_items'       => 'Search ' . $name . 's',
		'not_found'          => 'No ' . $name . ' found',
		'not_found_in_trash' => 'No ' . $name . ' found in trash',
		'parent_item_colon'  => 'Parent ' . $name,
	);
	$emanager = array(
		'title_label' => 'Subject',
		'icon'        => 'random',
		'form'        => 'acf_noc',
		'type'        => 'dashboard',
		'access'      => array(),
		'table_cols'  => array(
			'noc_number'  => 'NOC#',
			'pco_number'  => 'PCO#',
			'title'       => 'Title',
			'sap_reason'  => 'SAP Reason',
			'status'      => 'Status',
			'noc_total'   => 'Value',
			'createdby'   => 'Requester',
			'reviewer'    => 'BIC',
			'importance'  => 'Importance',
		),
		'taxonomy'    => array(
			'name'  => 'em_noc_status',
			'terms' => array(
				'draft'      => 'Draft',
				'supervisor' => 'Supervisor Review',
				'ready'      => 'Ready to Submit',
				'pending'    => 'NOC Pending',
				'returned'   => 'NOC Returned',
				'revise'     => 'Revise',
				'void'       => 'Void',
			),
		),
	);

	register_post_type( 'em_noc', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'show_in_menu'       => 'emanager_change',
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'slug' => 'noc', 'feeds' => false ),
		'has_archive'        => 'noc',
		'emanager'           => $emanager,
	) );
}