<?php
add_action('init', 'kanban_posttype');
add_action('init', 'register_board_taxonomies');
add_action( 'init', 'build_board_taxonomies' );
function kanban_posttype() {
    // Custom Post Type Labels      
    $labels = array(
      'name'               => 'Kanban',
      'singular_name'      => 'Kanban',
      'add_new'            => 'Add new',
      'add_new_item'       => 'Add new Kanban',
      'edit_item'          => 'Edit Kanban',
      'new_item'           => 'New Kanban',
      'all_items'          => 'Kanban Cards',
      'view_item'          => 'View Kanban',
      'search_items'       => 'Search Kanbans',
      'not_found'          => 'No Kanban found',
      'not_found_in_trash' => 'No Kanban found in trash',
      'parent_item_colon'  => 'Parent Kanban',
      'menu_name'          => 'Kanban'
    );

    // Custom Post Type Capabilities  
    $capabilities = array(
      'edit_post'          => 'edit_post',
      'edit_posts'         => 'edit_posts',
      'edit_others_posts'  => 'edit_others_posts',
      'publish_posts'      => 'publish_posts',
      'read_post'          => 'read_post',
      'read_private_posts' => 'read_private_posts',
      'delete_post'        => 'delete_post'
    );

    // Custom Post Type Taxonomies  
    $taxonomies = array('boards');

    // Custom Post Type Supports  
    $supports = array('title', 'editor', 'wuthor', 'revisions', 'post-formats', 'author', 'page-attributes');

    // Custom Post Type Arguments  
    $args = array(
        'labels'             => $labels,
        'hierarchical'       => true,
        'description'        => 'Kanban',
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => true,
        'exclude_from_search'=> true,
        'query_var'          => true,
        'rewrite'            => false,
        'can_export'         => true,
        'has_archive'        => true,
        'menu_position'      => 25,
        'taxonomies'   => $taxonomies,
        'supports'           => $supports,
/*        'capabilities'   => $capabilities, */
        'capability_type'    => 'post',
        'menu_icon'      => '',
    );
    register_post_type('em_kanban', $args);
}
function register_board_taxonomies() {
  $labels = array(
    'name'                       => 'boards',
    'singular_name'              => 'Boards',
    'search_items'               => 'Search Boards',
    'popular_items'              => 'Popular Boards',
    'all_items'                  => 'All Boards',
    'parent_item'                => 'Parent Boards',
    'parent_item_colon'          => 'Parent: Boards',
    'edit_item'                  => 'Edit Boards',
    'view_item'                  => 'View Boards',
    'update_item'                => 'Update Boards',
    'add_new_item'               => 'Add New Boards',
    'new_item_name'              => 'New Boards Name',
    'add_or_remove_items'        => 'Add or remove Boards',
    'choose_from_most_used'      => 'Choose from the most used Boards',
    'separate_items_with_commas' => 'Separate Boards with commas',
    'menu_name'                  => 'Boards',
  );

  // Taxonomy Capabilities  
  $capabilities = array(
      'edit_terms'   => 'manage_categories',
      'manage_terms' => 'manage_categories',
      'delete_terms' => 'manage_categories',
      'assign_terms' => 'edit_posts'
  );

  // Linked Custom Post Types
  $cpts = array('em_kanban');

  // Taxonomy Arguments  
  $args = array(
      'labels'             => $labels,
      'hierarchical'       => true,
      'description'        => '',
      'public'             => true,
      'show_ui'            => true,
      'show_tagcloud'      => true,
      'show_in_nav_menus'  => false,
      'show_admin_column'  => true,
      'query_var'          => true,
      'rewrite'            => true,
/*      'capabilities'   => $capabilities, */
  );
  register_taxonomy( 'boards', $cpts, $args );
}

function build_board_taxonomies() { 
	$parent_term    = term_exists( 'boards', 'boards' ); // array is returned if taxonomy is given
	$parent_term_id = $parent_term['term_id']; // get numeric term id

	wp_insert_term('Open','boards', array('description'=> '','slug' => 'open','parent'=> $parent_term_id));
	wp_insert_term('In Progress','boards', array('description'=> '','slug' => 'in_progress','parent'=> $parent_term_id));
	wp_insert_term('Closed','boards', array('description'=> '','slug' => 'closed','parent'=> $parent_term_id));
}