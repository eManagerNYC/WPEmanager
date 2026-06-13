<?php

/**
 * Load components scripts
 */
foreach ( glob(get_template_directory() . '/includes/components/*.php') as $filename )
{
	if ( is_file($filename) ) {
		require_once( $filename );
	}
}