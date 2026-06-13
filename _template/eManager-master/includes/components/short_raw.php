<?php

add_action( 'init', 'eman_content_init' );
function eman_content_init()
{
	if ( ! is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ) )
	{
		add_filter( 'the_content', 'eman_raw_content', 9 );
		add_filter( 'eman/the_content', 'eman_raw_content', 9 );
	}
}

/**
 * Content formatter.
 *
 * @since 1.0.0
 *
 * @param sting $content Content
 */
function eman_raw_content( $content )
{
	$output           = '';
	$pattern_full     = '{(\[raw\].*?\[/raw\])}is';
	$pattern_contents = '{\[raw\](.*?)\[/raw\]}is';
	$pieces           = preg_split( $pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	foreach ( $pieces as $piece )
	{
		if ( preg_match($pattern_contents, $piece, $matches) ) {
			$output .= $matches[1];
		} else {
			$output .= shortcode_unautop( wpautop( wptexturize($piece) ) );
		}
	}
	return $output;
}