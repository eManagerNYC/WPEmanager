<?php

/**
 * @link              https://github.com/jupitercow/
 * @since             1.1.0
 * @package           Acf_Edit_Title_Content
 *
 * @wordpress-plugin
 * Plugin Name:       Advanced Custom Fields: Edit Title & Content
 * Plugin URI:        http://Jupitercow.com/
 * Description:       Allows an Advanced Custom Fields form to edit post_title and post_content in front-end forms.
 * Version:           1.1.0
 * Author:            Jupitercow
 * Author URI:        http://Jupitercow.com/
 * Contributor:       Jake Snyder
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       acf_edit_title_content
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'Acf_Edit_Title_Content';
if (! class_exists($class_name) ) :

class Acf_Edit_Title_Content
{
	/**
	 * The unique prefix for ACF.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $prefix         The string used to uniquely prefix for Sewn In.
	 */
	protected $prefix;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $settings       The array used for settings.
	 */
	protected $settings;

	/**
	 * Load the plugin.
	 *
	 * @since	1.1.0
	 * @return	void
	 */
	public function run()
	{
		if (! $this->test_requirements() ) { return false; }

		$this->settings();

		add_action( 'init',                   array($this, 'init') );
	}

	/**
	 * Make sure that any neccessary dependancies exist
	 *
	 * @author  Jake Snyder
	 * @since	1.1.0
	 * @return	bool True if everything exists
	 */
	public function test_requirements()
	{
		// Look for ACF
		if (! class_exists('acf') ) { return false; }
		return true;
	}

	/**
	 * Class settings
	 *
	 * @author  Jake Snyder
	 * @since	1.1.0
	 * @return	void
	 */
	public function settings()
	{
		$this->prefix      = 'acf';
		$this->plugin_name = strtolower(__CLASS__);
		$this->version     = '1.1.0';
		$this->settings    = array(
			'strings' => array(
				
			),
		);
		$this->settings = apply_filters( "{$this->prefix}/edit_title_content/settings", $this->settings );
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function init()
	{
		// Create the new object
		add_filter( 'acf/pre_save_post', array($this, 'process_title_content'), 10 );
		// Add a basic title/content interface for default use
		add_action( 'wp',                array($this, 'register_field_groups') );
		// Load the content fields
		add_action( 'wp',                array($this, 'load_fields') );
	}

	public function load_fields()
	{
		// Load post title
		add_filter( 'acf/load_value/name=' . apply_filters( "{$this->prefix}/edit_title_content/title/name", 'form_post_title' ),   array($this, 'load_value_post_title'), 10, 3 );
		// Load post content
		add_filter( 'acf/load_value/name=' . apply_filters( "{$this->prefix}/edit_title_content/content/name", 'form_post_content' ), array($this, 'load_value_post_content'), 10, 3 );
	}

	/**
	 * Load existing post_title
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	string $value
	 */
	public function load_value_post_title( $value, $post_id, $field )
	{
		if (! $post_id || ! is_numeric($post_id) ) return;

		$value = get_the_title($post_id);
		return $value;
	}

	/**
	 * Load existing post_content
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	string $value
	 */
	public function load_value_post_content( $value, $post_id, $field )
	{
		$post = get_post($post_id);
		if ( is_object($post) ) $value = $post->post_content;

		return $value;
	}

	/**
	 * Make sure the post_title and post_content get saved correctly and not into meta
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	int post id
	 */
	public function process_title_content( $post_id )
	{
		// Don't run if adding/updated fields/field-groups in wp-admin
		if (! is_admin() && 'acf' != get_post_type( $post_id ) && ! empty($_POST['fields']) )
		{
			$post_data = array(
				'ID' => $post_id,
			);

			$update = false;
			foreach ( $_POST['fields'] as $field_key => $field_value )
			{
				$field = get_field_object($field_key, $post_id);
				if ( apply_filters( "{$this->prefix}/edit_title_content/title/name", 'form_post_title' ) == $field['name'] )
				{
					$post_data['post_title'] = $field_value;
					// Keep it out of meta data
					unset($_POST['fields'][$field_key]);
					$update = true;
				}
				elseif ( apply_filters( "{$this->prefix}/edit_title_content/content/name", 'form_post_content' ) == $field['name'] )
				{
					$post_data['post_content'] = $field_value;
					// Keep it out of meta data
					unset($_POST['fields'][$field_key]);
					$update = true;
				}
			}

			// Save ACF field as post_content / post_title for front-end posting
			if ( $update ) {
				wp_update_post( $post_data );
			}
		}

		return $post_id;
	}

	/**
	 * Add a basic interface for adding to front end forms, so we don't have to create them in the admin
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function register_field_groups()
	{
		if ( function_exists('register_field_group') )
		{
			$args = array(
				'id'              => 'acf_post-title-content',
				'title'           => apply_filters( "{$this->prefix}/edit_title_content/group/title", 'Post Title and Content' ),
				'fields'          => array (),
				'location'        => array (
					array (
						array (
							'param'        => 'post_type',
							'operator'     => '==',
							'value'        => '',
							'order_no'     => 0,
							'group_no'     => 0,
						),
					),
				),
				'options'         => array (
					'position'       => 'normal',
					'layout'         => 'no_box',
					'hide_on_screen' => array (
					),
				),
				'menu_order'      => -10,
			);

			if ( apply_filters( "{$this->prefix}/edit_title_content/title/add", true ) )
			{
				$args['fields'][] = array (
					'key'           => 'field_5232d86ba9246title',
					'label'         => apply_filters( "{$this->prefix}/edit_title_content/title/title", 'Title' ),
					'name'          => apply_filters( "{$this->prefix}/edit_title_content/title/name", 'form_post_title' ),
					'type'          => apply_filters( "{$this->prefix}/edit_title_content/title/type", 'text' ),
					'default_value' => '',
					'required'      => 1,
					'placeholder'   => '',
					'prepend'       => '',
					'append'        => '',
					'formatting'    => 'html',
					'maxlength'     => '',
				);
			}

			if ( apply_filters( "{$this->prefix}/edit_title_content/content/add", true ) )
			{
				$args['fields'][] = array (
					'key'           => 'field_5232d8daa9247content',
					'label'         => apply_filters( "{$this->prefix}/edit_title_content/content/title", 'Content' ),
					'name'          => apply_filters( "{$this->prefix}/edit_title_content/content/name", 'form_post_content' ),
					'type'          => apply_filters( "{$this->prefix}/edit_title_content/content/type", 'wysiwyg' ),
					'default_value' => '',
					'toolbar'       => apply_filters( "{$this->prefix}/edit_title_content/content/toolbar", 'basic' ),
					'media_upload'  => apply_filters( "{$this->prefix}/edit_title_content/content/media_upload", 'no' ),
				);
			}

			register_field_group( $args );
		}
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;