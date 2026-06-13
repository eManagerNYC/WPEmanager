<?php

add_action( 'init', 'eman_register_post_type_reviews' );
function eman_register_post_type_reviews()
{
	$menu_position = 30;
	$labels = array(
		'name'               => 'Reviews',
		'singular_name'      => 'Review',
		'add_new_item'       => 'Add new Review',
		'edit_item'          => 'Edit Review',
		'new_item'           => 'New Review',
		'view_item'          => 'View Review',
		'search_items'       => 'Search Reviews',
		'not_found'          => 'No Review found',
		'not_found_in_trash' => 'No Review found in trash',
		'parent_item_colon'  => 'Parent Review',
	);
	register_post_type( 'em_reviews', array(
		'labels'             => $labels,
		'hierarchical'       => true,
		'description'        => $labels['name'],
		'public'             => true,
		'show_in_nav_menus'  => false,
		'exclude_from_search'=> true,
		'menu_position'      => $menu_position,
		'supports'           => array( 'title', 'comments', 'author', 'revisions', 'page-attributes' ),
		'rewrite'            => array( 'feeds' => false ),
		'has_archive'        => false,
	) );
}