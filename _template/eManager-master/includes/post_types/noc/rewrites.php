<?php

add_action( 'init', 'eman_rewrites_noc' );
function eman_rewrites_noc()
{
	/**
	 * Support PCO vs NOC
	 */

	 // Customize the pagination support
	add_rewrite_rule(
		"noc/pco/page/([^/]*)/?$",
		'index.php?post_type=em_noc&pco=1&paged=$matches[1]',
		'top'
	);

	 // Customize add
	add_rewrite_rule(
		"noc/pco/add/?$",
		'index.php?post_type=em_noc&pco=1',
		'top'
	);

	add_rewrite_rule(
		"noc/pco/?$",
		'index.php?post_type=em_noc&pco=1',
		'top'
	);

	add_filter( 'query_vars', 'eman_query_vars_noc' );
}

function eman_query_vars_noc( $vars )
{
	$vars[] = 'pco';
	return $vars;
}