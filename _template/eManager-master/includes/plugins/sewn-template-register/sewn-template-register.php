<?php

/**
 * @link              https://github.com/jupitercow/sewn-in-template-register
 * @since             1.1.0
 * @package           Sewn_Login
 *
 * @wordpress-plugin
 * Plugin Name:       Sewn In Template Register
 * Plugin URI:        https://wordpress.org/plugins/sewn-in-template-register/
 * Description:       Add log in form to a page template. Moves everything to a page template.
 * Version:           1.1.0
 * Author:            Jupitercow
 * Author URI:        http://Jupitercow.com/
 * Contributor:       Jake Snyder
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sewn_register
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'Sewn_Register';
if (! class_exists($class_name) ) :

class Sewn_Register
{
	/**
	 * The unique prefix for Sewn In.
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
		$this->settings();

		register_activation_hook( __FILE__,   array($this, 'activation') );
		register_deactivation_hook( __FILE__, array($this, 'deactivation') );

		add_action( 'plugins_loaded',         array($this, 'plugins_loaded') );
		add_action( 'init',                   array($this, 'plugins_loaded') );

		add_action( 'init',                   array($this, 'init') );
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
		$this->prefix      = 'sewn';
		$this->plugin_name = strtolower(__CLASS__);
		$this->version     = '1.1.0';
		$this->settings    = array(
			'dir'      => $this->get_dir_url( __FILE__ ),
			'path'     => plugin_dir_path( __FILE__ ),
			'pages'    => array(
				'register' => array(
					'page_name'  => 'register',
					'page_title' => 'Register'
				),
				'profile' => array(
					'page_name'  => 'profile',
					'page_title' => 'Profile'
				)
			),
			'strings' => array(
				'notification_registered' => __( "Successfully registered!", $this->plugin_name ),
				'notification_profile'    => __( "Profile updated", $this->plugin_name ),
				'notification_exists'     => __( "That email address already exists, follow the links below to get access", $this->plugin_name ),
				'notification_invalid'    => __( "Please enter a valid email address", $this->plugin_name ),
			),
		);
		$this->settings['messages'] = array(
			'registered' => array(
				'key'     => 'action',
				'value'   => 'registered',
				'message' => $this->settings['strings']['notification_registered'],
				'args'    => 'fade=true&page=' . $this->settings['pages']['profile']['page_name'] . ',' . $this->settings['pages']['register']['page_name'],
			),
			'profile' => array(
				'key'     => 'action',
				'value'   => 'profile',
				'message' => $this->settings['strings']['notification_profile'],
				'args'    => 'fade=true&page=' . $this->settings['pages']['profile']['page_name'],
			),
			'exists' => array(
				'key'     => 'action',
				'value'   => 'exists',
				'message' => $this->settings['strings']['notification_exists'],
				'args'    => 'page=' . $this->settings['pages']['register']['page_name'],
			),
			'invalid' => array(
				'key'     => 'action',
				'value'   => 'invalid',
				'message' => $this->settings['strings']['notification_invalid'],
				'args'    => 'page=' . $this->settings['pages']['register']['page_name'],
			),
		);
	}

	/**
	 * Activation of plugin
	 *
	 * @author  Jake Snyder
	 * @since	1.0.2
	 * @return	void
	 */
	public function activation()
	{
		flush_rewrite_rules();
	}

	/**
	 * Deactivation of plugin
	 *
	 * @author  Jake Snyder
	 * @since	1.0.2
	 * @return	void
	 */
	public function deactivation()
	{
		flush_rewrite_rules();
	}

	/**
	 * On plugins_loaded test if we can use sewn_notifications
	 *
	 * @author  Jake Snyder
	 * @since	1.0.1.0.0
	 * @return	void
	 */
	public function plugins_loaded()
	{
		// Have the login plugin use frontend notifictions plugin
		if ( apply_filters( "{$this->prefix}/register/use_sewn_notifications", true ) )
		{
			if ( class_exists('Sewn_Notifications') ) {
				add_filter( "{$this->prefix}/notifications/queries", array($this, 'add_notifications') );
			} else {
				add_filter( "{$this->prefix}/register/use_sewn_notifications", '__return_false' );
			}
		}
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
		if (! $this->test_requirements() ) { return; }

		$this->settings = apply_filters( "{$this->prefix}/register/settings", $this->settings );

		$this->plugins_loaded();
		$this->register_field_groups();

		add_action( "{$this->prefix}/register/the_form",   array($this, 'the_form'), 10, 2 );
		add_filter( "{$this->prefix}/register/get_form",   array($this, 'get_form'), 10 );

		// Redirect pages if the user is logged in or not
		add_action( 'template_redirect',                   array($this, 'template_redirect') );

		// Add form to Registration Page
		add_filter( 'the_content',                         array($this, 'add_form_to_page') );

		// Add a fake post for profile and register pages if they don't already exist
		add_filter( 'the_posts',                           array($this, 'add_post') );

		// Load any scripts and styles
		add_action( 'wp_enqueue_scripts',                  array($this, 'enqueue_scripts') );

		// Test if the email exists already
		add_action( "wp_ajax_{$this->plugin_name}_test_email",        array($this, 'ajax_test_email') );
		add_action( "wp_ajax_nopriv_{$this->plugin_name}_test_email", array($this, 'ajax_test_email') );
	}

	/**
	 * Make sure that any neccessary dependancies exist
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	bool True if everything exists
	 */
	public function test_requirements()
	{
		// Look for ACF
		if (! class_exists('Acf') ) { return false; }
		return true;
	}

	/**
	 * Add this plugin's notification messages to the sewn_notifications plugin.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	array $queries The modified queries for the frontend_notification plugin
	 */
	public function add_notifications( $queries )
	{
		$queries = array_merge($queries, $this->settings['messages']);
		return $queries;
	}
    
	/**
	 * See if not register post exists and add it dynamically if not
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	object $posts Modified $posts with the new register post
	 */
	public function add_post( $posts )
	{
		global $wp, $wp_query;

		// Check if the requested page matches our target, and no posts have been retrieved
		if ( (! $posts || 1 < count($posts)) && array_key_exists(strtolower($wp->request), $this->settings['pages']) )
		{
			// Add the fake post
			$post    = $this->create_post( strtolower($wp->request) );
			$posts   = array();
			$posts[] = $post;

			$wp_query->is_page     = true;
			$wp_query->is_singular = true;
			$wp_query->is_home     = false;
			$wp_query->is_archive  = false;
			$wp_query->is_category = false;
			$wp_query->queried_object = $post;

			//Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
			unset( $wp_query->query["error"] );
			$wp_query->query_vars["error"] = "";
			$wp_query->is_404 = false;
		}
		return $posts;
	}
    
	/**
	 * Create a dynamic post on-the-fly for the register page.
	 *
	 * source: http://scott.sherrillmix.com/blog/blogger/creating-a-better-fake-post-with-a-wordpress-plugin/
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	object $post Dynamically created post
	 */
	public function create_post( $type )
	{
		// Create a fake post.
		$post = new stdClass();
		$post->ID                    = 1;
		$post->post_author           = 1;
		$post->post_date             = current_time('mysql');
		$post->post_date_gmt         = current_time('mysql', 1);
		$post->post_content          = '';
		$post->post_title            = $this->settings['pages'][$type]['page_title'];
		$post->post_excerpt          = '';
		$post->post_status           = 'publish';
		$post->comment_status        = 'closed';
		$post->ping_status           = 'closed';
		$post->post_password         = '';
		$post->post_name             = $this->settings['pages'][$type]['page_name'];
		$post->to_ping               = '';
		$post->pinged                = '';
		$post->post_modified         = current_time('mysql');
		$post->post_modified_gmt     = current_time('mysql', 1);
		$post->post_content_filtered = '';
		$post->post_parent           = 0;
		$post->guid                  = home_url('/' . $this->settings['pages'][$type]['page_name'] . '/');
		$post->menu_order            = 0;
		$post->post_type             = 'page';
		$post->post_mime_type        = '';
		$post->comment_count         = 0;
		$post->filter                = 'raw';
		return $post;   
	}

	/**
	 * Adds a form to the login page, this can be turned off using the filter: 'customize_register/add_form'
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	string $content The post content for login page with the login form a
	 */
	public function add_form_to_page( $content )
	{
		if ( is_main_query() && in_the_loop() && apply_filters( "{$this->prefix}/register/add_form", true ) )
		{
			$messages = $footer = '';
			$args = false;
			if (! empty($_GET['action']) )
			{
				$action = $_GET['action'];
				if (! apply_filters( "{$this->prefix}/register/use_sewn_notifications", true ) && apply_filters( "{$this->prefix}/register/show_messages", true ) )
					$messages = (! empty($this->settings['messages'][$action]['message']) ) ? "<p class=\"{$this->plugin_name}_messages\">" . $this->settings['messages'][$action]['message'] . '</p>' : '';
			}

			if ( is_page($this->settings['pages']['register']['page_name']) )
			{
				$args = array(
					'field_groups' => apply_filters( "{$this->prefix}/register/field_groups", array() ),
				);
				ob_start(); ?>
				<div class="hidden" style="display:none; visibility:hidden;">
				<form name="loginform" id="loginform" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post'); ?>" method="post">
					<input type="text" name="user_login" id="user_login" class="input" value="" size="20" />
					<input type="hidden" name="redirect_to" value="<?php echo add_query_arg('action', 'recovered', home_url('/login/')); ?>" />
				</form>
				</div>
				<?php
				$footer = ob_get_clean();
			}
			elseif ( is_page($this->settings['pages']['profile']['page_name']) )
			{
				add_filter( 'acf/load_value/name=username', array($this, 'load_value_user_login'), 10, 3 );
				add_filter( 'acf/load_value/name=email',    array($this, 'load_value_user_email'), 10, 3 );

				$args = array(
					'field_groups' => apply_filters( "{$this->prefix}/register/profile/field_groups", array() ),
					'form_type' => 'profile'
				);
			}

			$content = $messages . $content . ($args ? $this->get_form( $args ) : '') . $footer;
		}

		return $content;
	}
		public function load_value_user_login( $value, $post_id, $field )
		{
			$current_user = wp_get_current_user();
			return $current_user->user_login;
		}
		public function load_value_user_email( $value, $post_id, $field )
		{
			$current_user = wp_get_current_user();
			return $current_user->user_email;
		}

	/**
	 * Create the registration form, can be accessed using the action: 'sewn_register/the_form'
	 *
	 * @author  Jake Snyder
	 * @since	1.0.1
	 * @return	string $args The arguments for wp_login_form()
	 * @return	string $content The post content for login page with the login form a
	 */
	public function the_form( $options=array() )
	{
		echo apply_filters( "{$this->prefix}/register/get_form", $options );
	}

	/**
	 * Create the register/profile form. Works almost exactly like "acf_form".
	 *
	 * Can be accessed using the filter: 'sewn_register/get_form'.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	string $content The post content for login page with the login form a
	 */
	public function get_form( $options=array() )
	{
		// defaults
		$defaults = array(
			'field_groups' => array(),
			'submit_value' => false,
			'form_type'    => 'register'
		);

		if (! is_array($options) ) {
			$options = array();
		}

		// merge defaults with options
		$options = array_merge($defaults, $options);

		// Add default Submit Value
		if (! $options['submit_value'] )
		{
			$options['submit_value'] = ( 'profile' == $options['form_type'] ) ? __( "Update", $this->plugin_name ) : __( "Register", $this->plugin_name );
		}

		// Set up the post ID
		if ( is_user_logged_in() && 'profile' == $options['form_type'] )
		{
			$current_user = wp_get_current_user();
			$options['post_id'] = 'user_' . $current_user->ID;
		}
		else
		{
			$options['post_id'] = 'new_user';
		}

		// Add the default field groups to anything the user adds
		$options['field_groups'] = array_merge( array( 'acf_register' ), $options['field_groups'] );

		// Add the return
		$action                  = ( 'profile' == $options['form_type'] ) ? 'profile' : 'registered';
		$options['return']       = add_query_arg( 'action', $action, get_permalink() );

		// The actual form
		ob_start();
		acf_form( $options );
		return ob_get_clean();
	}

	/**
	 * Redirect the pages depending on if the user is logged in or not.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	string $content The post content for login page with the login form a
	 */
	public function template_redirect()
	{
		if ( array_key_exists(get_query_var('pagename'), $this->settings['pages']) )
		{
			$redirect = false;
			if ( 'register' == get_query_var('pagename') && is_user_logged_in() )
			{
				$redirect = apply_filters( 'acf/' . "{$this->prefix}/register/redirect/register", home_url('/profile/') );
			}
			if ( 'profile' == get_query_var('pagename') && ! is_user_logged_in())
			{
				$redirect = apply_filters( "{$this->prefix}/register/redirect/profile", wp_login_url() );
			}
			if ( $redirect ) wp_redirect( $redirect );

			// Load the acf form head
			acf_form_head();
		}
	}

	/**
	 * AJAX wrapper for the test_email function
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function ajax_test_email()
	{
		if ( empty($_POST['email']) ) return;

		echo $this->test_email( $_POST['email'] );
		die;
	}

	/**
	 * Test the email to see if it exists
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function test_email( $user_email )
	{
		if (! is_email( $user_email ) )
		{
			return 'invalid';
		}
		elseif ( email_exists( $user_email ) )
		{
			return 'exists';
		}
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function enqueue_scripts()
	{
		// register scripts
		wp_enqueue_script( $this->plugin_name, $this->settings['dir'] . 'assets/js/scripts.js', array( 'jquery' ), $this->version );
		$args = array(
			'url'      => admin_url( 'admin-ajax.php' ),
			'action'   => "{$this->plugin_name}_test_email",
			'prefix'   => $this->plugin_name,
			'spinner'  => admin_url( 'images/spinner.gif' ),
			'messages' => $this->settings['messages'],
			'links'    => array(
				'login'    => array(
					'href'     => add_query_arg( 'email', '[email]', wp_login_url() ),
					'text'     => __( "Log In", $this->plugin_name ),
				),
				'recover'  => array(
					'href'     => '#javascript_required',
					'text'     => __( "Recover your password", $this->plugin_name ),
				),
			),
		);
		wp_localize_script( $this->plugin_name, $this->plugin_name, $args );

		// register styles
		wp_enqueue_style( $this->plugin_name, $this->settings['dir'] . 'assets/css/style.css', array(), $this->version );
	}

	/**
	 * Add a basic registration form
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function register_field_groups()
	{
		if ( function_exists('register_field_group') )
		{
			$args = array(
				'id' => 'acf_register',
				'title' => apply_filters( "{$this->prefix}/register/group/title", "Register" ),
				'fields' => array (),
				'location' => array (
					array (
						array (
							'param' => 'ef_user',
							'operator' => '!=',
							'value' => 'all',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array (
					'position' => 'normal',
					'layout' => 'no_box',
					'hide_on_screen' => array (
					),
				),
				'menu_order' => -10,
			);

			if ( apply_filters( "{$this->prefix}/register/username/add", true ) )
			{
				$args['fields'][] = array (
					'key'           => 'field_52b75fe28969e_login',
					'label'         => apply_filters( "{$this->prefix}/register/username/title", 'Username' ),
					'name'          => apply_filters( "{$this->prefix}/register/username/name", 'username' ),
					'type'          => apply_filters( "{$this->prefix}/register/username/type", 'text' ),
					'required'      => apply_filters( "{$this->prefix}/register/username/required", 1 ),
					'default_value' => '',
					'placeholder'   => '',
					'prepend'       => '',
					'append'        => '',
					'formatting'    => 'html',
					'maxlength'     => '',
				);
			}

			$args['fields'][] = array (
				'key'           => 'field_52b76078896a1_email',
				'label'         => apply_filters( "{$this->prefix}/register/email/title", 'Email Address' ),
				'name'          => apply_filters( "{$this->prefix}/register/email/name", 'email' ),
				'type'          => apply_filters( "{$this->prefix}/register/email/type", 'email' ),
				'required'      => apply_filters( "{$this->prefix}/register/email/required", 1 ),
				'default_value' => '',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);

			if ( apply_filters( "{$this->prefix}/register/name/add", true ) )
			{
				$args['fields'][] = array (
					'key'           => 'field_52b75fe28969e_first_name',
					'label'         => apply_filters( "{$this->prefix}/register/first_name/title", 'First Name' ),
					'name'          => apply_filters( "{$this->prefix}/register/first_name/name", 'first_name' ),
					'type'          => apply_filters( "{$this->prefix}/register/first_name/type", 'text' ),
					'required'      => apply_filters( "{$this->prefix}/register/first_name/required", 1 ),
					'default_value' => '',
					'placeholder'   => '',
					'prepend'       => '',
					'append'        => '',
					'formatting'    => 'html',
					'maxlength'     => '',
				);
				$args['fields'][] = array (
					'key'           => 'field_52b7603a8969f_last_name',
					'label'         => apply_filters( "{$this->prefix}/register/last_name/title", 'Last Name' ),
					'name'          => apply_filters( "{$this->prefix}/register/last_name/name", 'last_name' ),
					'type'          => apply_filters( "{$this->prefix}/register/last_name/type", 'text' ),
					'required'      => apply_filters( "{$this->prefix}/register/last_name/required", 1 ),
					'default_value' => '',
					'placeholder'   => '',
					'prepend'       => '',
					'append'        => '',
					'formatting'    => 'html',
					'maxlength'     => '',
				);
			}

			if ( apply_filters( "{$this->prefix}/register/password/add", true ) )
			{
				$args['fields'][] = array (
					'key'         => 'field_52b760ea896a2_pass1',
					'label'       => apply_filters( "{$this->prefix}/register/pass1/title", 'Password' ),
					'name'        => apply_filters( "{$this->prefix}/register/pass1/name", 'pass1' ),
					'type'        => apply_filters( "{$this->prefix}/register/pass1/type", 'password' ),
					'required'    => apply_filters( "{$this->prefix}/register/pass1/required", 1 ),
					'placeholder' => '',
					'prepend'     => '',
					'append'      => '',
				);
				$args['fields'][] = array (
					'key'         => 'field_52b7610b896a3_pass2',
					'label'       => apply_filters( "{$this->prefix}/register/pass2/title", 'Password (re-enter)' ),
					'name'        => apply_filters( "{$this->prefix}/register/pass2/name", 'pass2' ),
					'type'        => apply_filters( "{$this->prefix}/register/pass2/type", 'password' ),
					'required'    => apply_filters( "{$this->prefix}/register/pass2/required", 1 ),
					'placeholder' => '',
					'prepend'     => '',
					'append'      => '',
				);
			}

			register_field_group( $args );
		}
	}

	/**
	 * This function will calculate the directory (URL) to a file
	 *
	 * @author  Jake Snyder, based on ACF4
	 * @since	1.1.0
	 * @param	$file A reference to the file
	 * @return	string
	 */
    function get_dir_url( $file )
    {
        $dir   = str_replace( '\\' ,'/', trailingslashit(dirname($file)) );
        $count = 0;
        // if file is in plugins folder
        $dir   = str_replace( str_replace('\\' ,'/', WP_PLUGIN_DIR), plugins_url(), $dir, $count );
		// if file is in wp-content folder
        if ( $count < 1 ) {
	        $dir  = str_replace( str_replace('\\' ,'/', WP_CONTENT_DIR), content_url(), $dir, $count );
        }
		// if file is in ??? folder
        if ( $count < 1 ) {
	        $dir  = str_replace( str_replace('\\' ,'/', ABSPATH), site_url('/'), $dir );
        }
        return $dir;
    }
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;