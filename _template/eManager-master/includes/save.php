<?php

/**
 * Load scripts to aid in saving posts
 */
foreach ( glob(get_template_directory() . '/includes/save/*.php') as $filename )
{
	if ( is_file($filename) ) {
		require_once( $filename );
	}
}