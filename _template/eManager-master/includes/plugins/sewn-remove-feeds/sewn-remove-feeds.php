<?php
/*
Plugin Name: Sewn In Remove WordPress Feeds
Plugin URI: http://bitbucket.org/jupitercow/no-feeds
Description: Disable feeds in order to not leak information out through unused feeds.
Version: 1.0.0
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

if (! class_exists('sewn_remove_feeds') ) :

add_action( 'init', array('sewn_remove_feeds', 'init') );

class sewn_remove_feeds
{
	/**
	 * Class prefix
	 *
	 * @since 	1.0.0
	 * @var 	string
	 */
	const PREFIX = __CLASS__;

	/**
	 * Add feed types
	 *
	 * @since 	1.0.0
	 * @var 	array
	 */
	public static $feed_types = array( '', 'rdf', 'rss', 'rss2', 'atom', 'rss2_comments', 'atom_comments' );

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function init()
	{
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );

		foreach( apply_filters( self::PREFIX . '/all_feeds', self::$feed_types ) as $feed )
		{
			if ( $feed )
			{
				if ( apply_filters( self::PREFIX . '/type=' . $feed, true ) )
				{
					add_action( 'do_feed_' . $feed, array(__CLASS__, 'disable_feeds'), 1 );
				}
			}
		}
	}

	/**
	 * Disable the feeds by redirecting them to the homepage
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function disable_feeds()
	{
		wp_redirect( home_url('/'), 302 );
		die;
	}
}

endif;