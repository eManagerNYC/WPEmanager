<?php
/*
Plugin Name: Sewn In Avatars
Plugin URI: 
Description: Allow users to upload their own avatars. Keeps Gravatar as an option for users also.
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

if (! class_exists('sewn_avatars') ) :

add_action( 'init', array('sewn_avatars', 'init') );

class sewn_avatars
{
	/**
	 * Class prefix
	 *
	 * @since 	1.0.0
	 * @var 	string
	 */
	const PREFIX = __CLASS__;

	/**
	 * Settings
	 *
	 * @since 	1.0.0
	 * @var 	string
	 */
	public static $settings = array();

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function init()
	{
		self::$settings = add_filter( self::PREFIX . '/settings', array(
			'messages' => array(
				'admin' => array(
					'acf' => __( 'Sewn In Simple SEO requires the <a href="http://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> plugin to be installed and activated.', 'sewn_avatars' ),
				),
			),
			'field_groups' => array(
				'title' => __( 'SEO Meta Data', 'sewn_avatars' ),
				'fields' => array(
					'meta_title' => array (
						'key' => 'field_' . self::PREFIX . '_meta_title',
						'label' => __( 'Title', 'sewn_avatars' ),
						'name' => 'meta_title',
						'type' => 'text',
						'instructions' => __( 'Title display in search engines is limited to 70 chars', 'sewn_avatars' ),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => 70,
					),
					'meta_description' => array (
						'key' => 'field_' . self::PREFIX . '_meta_description',
						'label' => __( 'Description', 'sewn_avatars' ),
						'name' => 'meta_description',
						'type' => 'textarea',
						'instructions' => __( 'The meta description is limited to 156 chars and will show up on the search engine results page.', 'sewn_avatars' ),
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => 156,
						'formatting' => 'none',
					),
					'meta_image' => array(
						'key' => 'field_' . self::PREFIX . '_meta_image',
						'label' => __( 'Open Graph Image', 'sewn_avatars' ),
						'name' => 'meta_image',
						'type' => 'image',
						'instructions' => __( 'Used by Facebook when a user a shares this content.', 'sewn_avatars' ),
						'save_format' => 'url',
						'preview_size' => 'thumbnail',
						'library' => 'all',
					),
					'xml_sitemap_exclude' => array(
						'key' => 'field_' . self::PREFIX . '_xml',
						'label' => __( 'Exclude from XML Sitemap', 'sewn_avatars' ),
						'name' => 'xml_sitemap_exclude',
						'type' => 'true_false',
						'instructions' => __( 'This will keep the page from showing in the XML sitemap', 'sewn_avatars' ),
						'message' => '',
						'default_value' => 0,
					),
				),
			),
		) );

		// actions
		add_action( 'admin_init',                            array(__CLASS__, 'dependencies') );

		// filters
		add_filter( 'get_avatar',                            array(__CLASS__, 'custom_avatar'), 10, 5 );

		// Load ACF Fields
		self::register_field_groups();
	}

	/**
	 * Check for dependencies
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function dependencies()
	{
		if (! class_exists( 'acf' ) ) {
			add_action( 'admin_notices', array(__CLASS__, 'acf_dependency_message') );
		}
	}

	/**
	 * Add a nag for ACF
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function acf_dependency_message()
	{
		?>
		<div class="update-nag">
			<?php echo self::$settings['messages']['admin']['acf']; ?>
		</div>
		<?php
	}

	/**
	 * Customize the get_avatar function to be more flexible (allow overriding), but still take advantage of the fallbacks.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	string	$avatar			Full avatar img tag
	 * @param	mixed	$id_or_email	User id or email used to retrieve the avatar
	 * @param	int		$size			The integer size requested
	 * @return	string					The provided avatar or the modified version if an image exists
	 */
	public static function custom_avatar( $avatar, $id_or_email, $size, $default, $alt )
	{
		$user = false;
		if ( is_object($id_or_email) )
		{
			if (! empty($id_or_email->user_id) ) {
				$user = get_user_by('id', $id_or_email->user_id);
			}
		}
		elseif ( is_numeric($id_or_email) )
		{
			$user = get_user_by('id', $id_or_email);
		}
		elseif ( is_string($id_or_email) && is_email($id_or_email) )
		{
			$user = get_user_by('email', $id_or_email);
		}

		if ( $user && function_exists('get_field') )
		{
			$new_avatar = get_user_meta($user->ID, 'avatar', true);
//
// Finish getting the avatar properly
//
			$size = apply_filters( 'custom_avatar/size', 'thumbnail' );

			$url = false;
			if ( is_array($new_avatar) ) {
				$url = $new_avatar['sizes'][$size];
			} elseif ( is_numeric($new_avatar) ) {
				$url = wp_get_attachment_image( $new_avatar, $size );
			} elseif ( $new_avatar ) {
				$url = $new_avatar;
			}

			if ( $url )
			{
				$dom = new DOMDocument();
				$dom->loadHTML( $avatar );
				$tags = $dom->getElementsByTagName('img');
				if ( 0 < count($tags) )
				{
					$tag = $tags->item(0);
					$tag->setAttribute( 'src', $url );
					return $dom->saveHTML();
				}
			}
		}

		return $avatar;
	}

	/**
	 * Better SEO: ACF SEO Fields
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function register_field_groups()
	{
		if (! function_exists("register_field_group") ) return;

		$args = array (
			'id' => self::PREFIX . '-meta-data',
			'title' => self::$settings['field_groups']['title'],
			'fields' => array (
				self::$settings['field_groups']['fields']['meta_title'],
				self::$settings['field_groups']['fields']['meta_description'],
			),
			'location' => array (),
			'options' => array (
				'position' => 'normal',
				'layout' => 'default',
				'hide_on_screen' => array (
				),
			),
			'menu_order' => 9999,
		);

		if ( apply_filters( self::PREFIX . '/add_image', false ) ) {
			$args['fields'][] = self::$settings['field_groups']['fields']['meta_image'];
		}

		if ( apply_filters( self::PREFIX . '/add_xml_sitemap', self::$settings['add_xml_sitemap'] ) ) {
			$args['fields'][] = self::$settings['field_groups']['fields']['xml_sitemap_exclude'];
		}

		$default_location = array (
			'param' => 'post_type',
			'operator' => '==',
			'value' => '',
			'order_no' => 0,
			'group_no' => 0,
		);

		$post_types = apply_filters( self::PREFIX . '/post_types', array() );

		$i=0;
		foreach ( $post_types as $post_type )
		{
			$new_location = $default_location;
			$new_location['value'] = $post_type;
			$new_location['group_no'] = $i;
			$args['location'][] = array( $new_location );
			$i++;
		}

		register_field_group( $args );
	}
}

endif;