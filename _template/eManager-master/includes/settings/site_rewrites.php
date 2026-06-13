<?php

add_action( 'init', 'eman_rewrites_site' );
function eman_rewrites_site()
{
	/**
	 * Support the "add" on archive pages to switch to the form
	 */
	add_rewrite_rule(
		"([^/]*)/add/?$",
		'index.php?post_type=em_$matches[1]&add=1',
		'top'
	);

	/**
	 * Support the "orientation" steps
	 */
	add_rewrite_rule(
		"orientation/([^/]*)/?$",
		'index.php?pagename=orientation&add=$matches[1]',
		'top'
	);

	/**
	 * Support the "orientation" steps
	 */
	add_rewrite_rule(
		"orientation/([^/]*)/([^/]*)/?$",
		'index.php?pagename=orientation&add=$matches[1]&orientation_post=$matches[2]',
		'top'
	);

	/**
	 * Set up the pagination support
	 */
	add_rewrite_rule(
		"([^/]*)/page/([^/]*)/?$",
		'index.php?post_type=em_$matches[1]&paged=$matches[2]',
		'top'
	);

	/**
	 * Directory settings
	 */
	add_rewrite_rule(
		"directory/?$",
		'index.php?settings=directory',
		'top'
	);

	/**
	 * Profile settings
	 */
	add_rewrite_rule(
		"profile/?$",
		'index.php?settings=profile',
		'top'
	);

	/**
	 * Settings
	 */
	add_rewrite_rule(
		"settings/?$",
		'index.php?settings=home',
		'top'
	);
	add_rewrite_rule(
		"settings/([^/]*)/add/?$",
		'index.php?post_type=em_$matches[1]&add=1',
		'top'
	);

	add_filter( 'query_vars', 'eman_query_vars_site' );
}

function eman_query_vars_site( $vars )
{
	$vars[] = 'add';
	$vars[] = 'orientation_post';
	$vars[] = 'settings';
	$vars[] = 'bic_type';
	return $vars;
}

/**
 * Add template redirects, profile and settings
 */
add_action( 'template_redirect', 'eman_template_redirects' );
function eman_template_redirects()
{
	if ( $settings = get_query_var('settings') )
	{
		if ( 'home' == $settings )
		{
			add_filter( 'template_include', function() {
				return get_template_directory() . '/page-settings.php';
			} );
		}

		if ( 'profile' == $settings )
		{
			add_filter( 'template_include', function() {
				return get_template_directory() . '/page-profile.php';
			} );
		}
	}
}