<?php

/**
 * Load settings scripts
 */
foreach ( glob(get_template_directory() . '/includes/settings/*.php') as $filename )
{
	if ( is_file($filename) ) {
		require_once( $filename );
	}
}