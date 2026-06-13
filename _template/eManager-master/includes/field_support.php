<?php

/**
 * Load scripts to customize acf fields
 */
foreach ( glob(get_template_directory() . '/includes/field_support/*.php') as $filename )
{
	if ( is_file($filename) ) {
		require_once( $filename );
	}
}