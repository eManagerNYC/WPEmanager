<?php
/**
 * Customize options page
 */
add_filter( 'acf/options_page/settings', 'eman_options_pages' );
function eman_options_pages( $settings )
{
	$settings['title'] = 'Job Settings';
	$settings['pages'] = array('License Key', 'Job Info', 'Site Settings', 'Staff Assignments');
	return $settings;
}