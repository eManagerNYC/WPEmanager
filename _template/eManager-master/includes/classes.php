<?php

/**
 * Load the functions for eManager
 */
foreach ( glob(get_template_directory() . '/includes/classes/*.php') as $filename )
{
	if ( is_file($filename) ) {
		require_once( $filename );
	}
}