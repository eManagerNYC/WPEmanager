<?php
/*
Plugin Name: Sewn In Profiles
Plugin URI: http://bitbucket.org/jupitercow/customize-wordpress-profiles
Description: Just some really basic steps and framework to remove the stuff that is never used, and focus the user on the fields you want them focused on.
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

if (! class_exists('sewn_profiles') ) :

add_action( 'init', array('sewn_profiles', 'init') );

class sewn_profiles
{
	/**
	 * Class prefix
	 *
	 * @since 	1.0.0
	 * @var 	string
	 */
	const PREFIX = __CLASS__;

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function init()
	{
		add_action( 'admin_head',       array(__CLASS__, 'hide_personal_options') );
		add_action( 'personal_options', array(__CLASS__, 'personal_options_start') );
	}

	/**
	 * Remove Personal Options that are at the top of profile page
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function hide_personal_options()
	{
		if ( apply_filters( self::PREFIX . '/personal_options', true ) )
		{
			remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
			echo "\n" . '
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					// Remove profile information area
					$("#your-profile .form-table:first, #your-profile h3:first").remove();
					
					// Move the last item (user group) to the top, pause is to give it time to load
					//setTimeout(function(){
					//	$("#your-profile .form-table:last, #your-profile h3:last").prependTo("#your-profile");
					//}, 500);
				});
			</script>' . "\n";
		}
	}

	/**
	 * Start recording the page options using an output buffer
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function personal_options_start()
	{
		$action = ( IS_PROFILE_PAGE ? 'show' : 'edit' ) . '_user_profile';

		// Load the end to catch the output
		add_action( $action, array(__CLASS__, 'personal_options_stop') );
		ob_start();
	}

	/**
	 * Now you can collect the output buffer and manipulate the page options
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	string HTML that is outputted to the page
	 */
	public static function personal_options_stop()
	{
		$html = ob_get_clean();

		// remove username
		if (! apply_filters( self::PREFIX . '/user_login', true ) )
			$html = preg_replace( '~<tr>\s*<th><label for="user_login".*</tr>~imsUu', '', $html );

		// remove role
		if (! apply_filters( self::PREFIX . '/role', true ) )
			$html = preg_replace( '~<tr>\s*<th><label for="role".*</tr>~imsUu', '', $html );

		// remove first name
		if (! apply_filters( self::PREFIX . '/first_name', true ) )
			$html = preg_replace( '~<tr>\s*<th><label for="first_name".*</tr>~imsUu', '', $html );

		// remove last name
		if (! apply_filters( self::PREFIX . '/last_name', true ) )
			$html = preg_replace( '~<tr>\s*<th><label for="last_name".*</tr>~imsUu', '', $html );

		// remove nickname
		if (! apply_filters( self::PREFIX . '/nickname', true ) )
			$html = preg_replace( '~<tr>\s*<th><label for="nickname".*</tr>~imsUu', '', $html );

		// remove display name
		if (! apply_filters( self::PREFIX . '/display_name', true ) )
			$html = preg_replace( '~<tr>\s*<th><label for="display_name".*</tr>~imsUu', '', $html );


		// remove biography
		if (! apply_filters( self::PREFIX . '/description', false ) )
			$html = preg_replace( '~<tr>\s*<th><label for="description".*</tr>~imsUu', '', $html );


		// remove email
		if (! $email = apply_filters( self::PREFIX . '/email', true ) )
			$html = preg_replace( '~<tr>\s*<th><label for="email".*</tr>~imsUu', '', $html );

		// remove website
		if (! $url = apply_filters( self::PREFIX . '/url', false ) )
			$html = preg_replace( '~<tr>\s*<th><label for="url".*</tr>~imsUu', '', $html );

		// remove social
		if (! $aim = apply_filters( self::PREFIX . '/aim', false ) )
			$html = preg_replace( '~<tr>\s*<th><label for="aim".*</tr>~imsUu', '', $html );
		if (! $yim = apply_filters( self::PREFIX . '/yim', false ) )
			$html = preg_replace( '~<tr>\s*<th><label for="yim".*</tr>~imsUu', '', $html );
		if (! $jabber = apply_filters( self::PREFIX . '/jabber', false ) )
			$html = preg_replace( '~<tr>\s*<th><label for="jabber".*</tr>~imsUu', '', $html );

		// If no contact info, remove the section
		if (! ($email || $url || $aim || $yim || $jabber) )
			$html = preg_replace( '~<h3>Contact Info</h3>\s*<table class="form-table">.*</table>~imsUu', '', $html );

		// If no biography, change title to Password
		if (! apply_filters( self::PREFIX . '/description', false ) )
		{
			$headline = __( IS_PROFILE_PAGE ? 'About Yourself' : 'About the user' );
			$html     = str_replace( $headline, '<h3>Password</h3>', $html );
		}

		print $html;
	}
}

endif;