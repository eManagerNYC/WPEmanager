<?php
/**
 * Cleanup WP functionality
 */

/**
 * Launch some basic cleanup
 */
// launching operation cleanup
add_action( 'init',                       'eman_head_cleanup' );
// remove WP version from RSS
add_filter( 'the_generator',              'eman_rss_version' );
// cleaning up random code around images
add_filter( 'the_content',                'eman_filter_ptags_on_images' );
// cleaning up excerpt
add_filter( 'excerpt_more',               'eman_excerpt_more' );

/**
 * Cleanup the head output
 *
 */
function eman_head_cleanup()
{
	// Remove canonical links
	// remove_action('wp_head',           'rel_canonical');
	// Remove shortlink from head and header
	remove_action( 'wp_head',            'wp_shortlink_wp_head', 10, 0 );
	remove_action( 'template_redirect',  'wp_shortlink_header', 11, 0 );
	// category feeds
	remove_action( 'wp_head',            'feed_links_extra', 3 );
	// post and comment feeds
	remove_action( 'wp_head',            'feed_links', 2 );
	// windows live writer
	remove_action( 'wp_head',            'wlwmanifest_link' );
	// index link
	remove_action( 'wp_head',            'index_rel_link' );
	// previous link
	remove_action( 'wp_head',            'parent_post_rel_link', 10, 0 );
	// start link
	remove_action( 'wp_head',            'start_post_rel_link', 10, 0 );
	// links for adjacent posts
	remove_action( 'wp_head',            'adjacent_posts_rel_link', 10, 0); 
	remove_action( 'wp_head',            'adjacent_posts_rel_link_wp_head', 10, 0 );
	// WP version
	remove_action( 'wp_head',            'wp_generator' );
	// remove WP version from css
	add_filter( 'style_loader_src',      'eman_remove_wp_ver_css_js', 9999 );
	// remove Wp version from scripts
	add_filter( 'script_loader_src',     'eman_remove_wp_ver_css_js', 9999 );
	// remove check comment flood
	remove_filter('check_comment_flood', 'check_comment_flood_db');
}

/**
 * Remove WP version from RSS
 *
 * @return	string Empty string
 */
function eman_rss_version()
{
	return '';
}

/**
 * Remove the p from around imgs
 *
 * @return	string Modified content
 */
function eman_filter_ptags_on_images( $content )
{
	return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}

/**
 * Updates the […] for Read More links
 *
 * @return	bool Modified status for comments.
 */
function eman_excerpt_more( $more )
{
	global $post;
	return "&hellip;" . ' <a class="excerpt-read-more" href="'. get_permalink($post->ID) . '" title="Read ' . get_the_title($post->ID).'">Read more &raquo;</a>';
}

/**
 * Remove WP version from scripts
 *
 * @return	bool Modified status for comments.
 */
function eman_remove_wp_ver_css_js( $src )
{
	if ( strpos( $src, 'ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}