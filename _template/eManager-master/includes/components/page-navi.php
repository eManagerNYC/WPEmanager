<?php

/**
 * Page Navi
 *
 * Usage: do_action('patch/page_navi');
 *
 * Based on Bones by Eddie Machado
 *
 * @return	void
 */
add_action( 'patch/page_navi', 'eman_page_navi' );
function eman_page_navi( $posts=false )
{
	if ( ! $posts || ! is_object($posts) ) {
		$posts = $GLOBALS['wp_query'];
	}

	$bignum = 999999999;
	if ( $posts->max_num_pages <= 1 ) {
		return;
	}

	echo '<nav class="pagination">';
	echo paginate_links( array(
		'base'         => str_replace( $bignum, '%#%', esc_url( get_pagenum_link($bignum) ) ),
		'format'       => '',
		'current'      => max( 1, get_query_var('paged') ),
		'total'        => $posts->max_num_pages,
		'prev_text'    => '&larr;',
		'next_text'    => '&rarr;',
		'type'         => 'list',
		'end_size'     => 3,
		'mid_size'     => 3,
	) );
	echo '</nav>';
}