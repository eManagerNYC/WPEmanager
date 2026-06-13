<?php
/*
Plugin Name: Sewn In Remove WordPress Pingbacks
Plugin URI: https://github.com/jupitercow/sewn-in-remove-pingbacks
Description: Disable pingbacks.
Version: 1.0.1
Author: Jake Snyder
Author URI: http://Jupitercow.com/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

------------------------------------------------------------------------
Copyright 2014 Jupitercow, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if (! class_exists('sewn_remove_pingbacks') ) :

register_activation_hook( __FILE__, array('sewn_remove_pingbacks', 'activate') );
register_deactivation_hook( __FILE__, array('sewn_remove_pingbacks', 'deactivate') );

add_action( 'init', array('sewn_remove_pingbacks', 'init') );

class sewn_remove_pingbacks
{
	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function init()
	{
		// remove RSD link
		remove_action( 'wp_head', 'rsd_link' );

		// actions
		add_action( 'xmlrpc_call',                        array(__CLASS__, 'disable_xmlrpc') );
		#add_filter( 'xmlrpc_methods',                     array(__CLASS__, 'remove_xmlrpc_methods') );

		// filters
		add_filter( 'wp_headers',                         array(__CLASS__, 'remove_ping_headers'), 10, 1 );
		add_filter( 'rewrite_rules_array',                array(__CLASS__, 'remove_ping_rewrites') );
		add_filter( 'bloginfo_url',                       array(__CLASS__, 'remove_ping_pingback_url'), 10, 2 );
		add_filter( 'pre_update_option_enable_xmlrpc',    '__return_false' );
		add_filter( 'pre_option_enable_xmlrpc',           '__return_zero' );
		add_filter( 'mod_rewrite_rules',                  array(__CLASS__, 'htaccess') );
	}

	/**
	 * activate
	 *
	 * Activate the plugin
	 *
	 * @author	Jake Snyder
	 * @since	1.0.1
	 * @return	void
	 */
	public static function activate()
	{
		add_filter( 'mod_rewrite_rules', array(__CLASS__, 'htaccess') );
		flush_rewrite_rules();
	}

	/**
	 * deactivate
	 *
	 * Deactivate the plugin
	 *
	 * @author	Jake Snyder
	 * @since	1.0.1
	 * @return	void
	 */
	public static function deactivate()
	{
		remove_filter( 'mod_rewrite_rules', array(__CLASS__, 'htaccess') );
		flush_rewrite_rules();
	}

	/**
	 * disable_xmlrpc
	 *
	 * Disable the xmlprc
	 *
	 * @author	Jake Snyder
	 * @since	1.0.0
	 * @param	string	$action	xmlrpc actions
	 * @return	void
	 */
	public static function disable_xmlrpc( $action )
	{
		if ( 'pingback.ping' === $action )
		{
			wp_die( 
				'Pingbacks are not supported',
				'Not Allowed!',
				array( 'response' => 403 )
			);
		}
	}

	
	/**
	 * remove_xmlrpc_methods
	 *
	 * Remove the actual XMLRPC methods
	 *
	 * @author	Jake Snyder
	 * @since	1.0.1
	 * @param	array	$methods	xmlrpc methods
	 * @return	array	The modified methods with pingback removed
	 */
	public static function remove_xmlrpc_methods( $methods )
	{
		unset( $methods['pingback.ping'] );
		#unset( $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}

	/**
	 * remove_ping_headers
	 *
	 * DISABLING PINGBACKS AND TRACKBACKS
	 * Intercepts header and rewrites X-Pingback
	 * Does not modify $post['ping_status'] - could read 'open'
	 * Does not modify $default_ping_status - could read 'open'
	 *
	 * @author	Jake Snyder
	 * @since	1.0.0
	 * @param	array	$headers	Array of header items
	 * @return	array	Modified header array with pingbacks removed
	 */
	public static function remove_ping_headers( $headers )
	{
		if ( isset($headers['X-Pingback']) ) {
			unset($headers['X-Pingback']);
		}
		return $headers;
	}

	/**
	 * remove_ping_rewrites
	 *
	 * Kill the rewrite rule
	 *
	 * @author	Jake Snyder
	 * @since	1.0.0
	 * @param 	array	$rules	Array of rewrite rules
	 * @return 	array	Modified rewrite rules with pingbacks removed
	 */
	public static function remove_ping_rewrites( $rules )
	{
		foreach ( $rules as $rule => $rewrite )
		{
			if ( preg_match( '/trackback\/\?\$$/i', $rule ) ) {
				unset( $rules[$rule] );
			}
		}
		return $rules;
	}

	/**
	 * remove_ping_pingback_url
	 *
	 * Kill bloginfo( 'pingback_url' )
	 *
	 * @author	Jake Snyder
	 * @since	1.0.0
	 * @param 	mixed	$output	The URL returned by bloginfo().
	 * @param 	mixed	$show	Type of information requested.
	 * @return 	array	Modified rewrite rules with pingbacks removed
	 */
	public static function remove_ping_pingback_url( $output, $show )
	{
		if ( 'pingback_url' == $show ) {
			$output = '';
		}
		return $output;
	}

	/**
	 * htaccess
	 *
	 * Remove access to the xml-rpc file completely at the server level
	 *
	 * @author	Jake Snyder
	 * @since	1.0.0
	 * @param	array	$rules	The current rules
	 * @return	array	Modified rules
	 */
	public static function htaccess( $rules )
	{
		$new_rules = "<Files xmlrpc.php>\n\tSatisfy any\n\tOrder allow,deny\n\tDeny from all\n</Files>\n\n";
		return $new_rules . $rules;
	}
}

endif;