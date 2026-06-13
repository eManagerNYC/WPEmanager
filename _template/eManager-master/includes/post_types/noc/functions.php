<?php


/**
 * Switch between NOC and PCO
 */
add_action( 'pre_get_posts', 'eman_pre_get_posts' );
function eman_pre_get_posts( $query )
{
	// Otherwise remove the noc or pco statuses.
	if ( ! is_admin() && is_post_type_archive('em_noc') && $query->is_main_query() && (empty($_GET['filter-status']) || 'null' == $_GET['filter-status']) ) // && in_the_loop()
	{
		$noc_terms = array( 'submitted', 'recommend', 'pending', 'executed', 'returned' );
		if ( get_query_var('pco') ) {
			$query->set('tax_query', array(
				array(
					'taxonomy' => 'em_status',
					'field'    => 'slug',
					'terms'    => $noc_terms,
					'operator' => 'NOT IN',
				)
			) );
		} else {
			$query->set('tax_query', array(
				array(
					'taxonomy' => 'em_status',
					'field'    => 'slug',
					'terms'    => $noc_terms,
					'operator' => 'IN',
				)
			) );
		}
	}
}

/**
 * NOC numbers
 *
 * @author  Jake Snyder
 * @since	3.0.17
 * @return  string User's name
 */
function eman_noc_numbers( $post )
{
	if ( is_numeric($post) ) {
		$post = get_post($post);
	}
	if ( ! is_object($post) ) {
		return false;
	}

	$noc_number = eman_get_field('noc_number', $post->ID);
	if ( ! $noc_number ) {
		$noc_number = get_post_meta($post->ID, 'noc_number', true);
	}

	$pco_number = eman_get_field('pco_number', $post->ID);
	if ( ! $pco_number ) {
		$pco_number = get_post_meta($post->ID, 'pco_number', true);
	}

	if ( ! $noc_number || ! $pco_number )
	{
		$reviews = new WP_Query( array(
			'post_type' => 'em_reviews',
			#'fields' => 'id=>parent',
			'posts_per_page' => 1,
			'order' => 'DESC',
			'orderby'=> 'date',
			'em_status' => 'submitted',
			'meta_query' => array(
				array(
					'key' => 'reviewed_id',
					'value' => $post->ID,
					'compare' => '=',
				)
			)
		) );

		if ( ! empty($reviews->posts[0]) )
		{
			$noc_number = get_post_meta($reviews->posts[0]->ID, 'noc_number', true);
			if ( $noc_number ) update_post_meta($post->ID, 'noc_number', $noc_number);

			$pco_number = get_post_meta($reviews->posts[0]->ID, 'pco_number', true);
			if ( $pco_number ) update_post_meta($post->ID, 'pco_number', $pco_number);

			emanager_table_filters::update_meta_index($post->ID);
		}
	}

	$output = array(
		'noc' => $noc_number,
		'pco' => $pco_number,
	);

	return $output;
}

/**
 * Test if a post is pco, mainly to separate from noc
 *
 * @author  Jake Snyder
 * @return  string User's name
 */
function eman_pco_or_noc( $post )
{
	if ( is_numeric($post) ) $post = get_post($post);
	if ( ! is_object($post) ) return false;

	// If is NOC, but not submitted or executed
	if ( 'em_noc' == $post->post_type )
	{
		return ( ! has_term('submitted', 'em_status', $post) && ! has_term('executed', 'em_status', $post) && ! has_term('recommend', 'em_status', $post) ) ? 'pco' : 'noc';
	}

	return false;
}

/**
 * Add pco to body class
 *
 * @author  Jake Snyder
 * @return  array	$classes
 */
add_filter( 'body_class','my_body_classes' );
function my_body_classes( $classes )
{
	global $post;
	if ( is_object($post) && 'em_noc' == $post->post_type && 'pco' == eman_pco_or_noc($post) && is_single() )
	{
		foreach ( $classes as $key => $value )
		{
			if ( 'single-em_noc' == $value ) {
				$classes[$key] = 'single-em_pco';
			}
		}
	}
	return $classes;
}

function noc_estimate_total($postid) {
    $contractors_and_estimate = eman_get_field('contractors_and_estimate', $postid);
    if ( is_array($contractors_and_estimate) )
      {
        $total = 0;
        $row_count=0;
        foreach ( $contractors_and_estimate as $row )
        {
          $item_count=0;
          foreach ( $row as $key2 => $item )
          {
            if ( 'estimated_value' == $key2 ) {
              $total += $item;
            }
            $item_count++;
          }
          $row_count++;
        }
      }
    return $total;
}
