<?php

add_action( 'init', 'eman_rewrites_reviews' );
function eman_rewrites_reviews()
{
	/**
	 * BIC types
	 */
	add_rewrite_rule(
		"bic/([^/]*)/?$",
		'index.php?pagename=bic&bic_type=$matches[1]',
		'top'
	);
	// pagination
	add_rewrite_rule(
		"bic/([^/]*)/page/([^/]*)/?$",
		'index.php?pagename=bic&bic_type=$matches[1]&paged=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		"bic/page/([^/]*)/?$",
		'index.php?pagename=bic&paged=$matches[1]',
		'top'
	);
}