<?php

/**
 * @link              https://github.com/jupitercow/sewn-in-notifications
 * @since             1.1.0
 * @package           Sewn_Notifications
 *
 * @wordpress-plugin
 * Plugin Name:       Sewn In Notifications
 * Plugin URI:        https://wordpress.org/plugins/sewn-in-notifications/
 * Description:       Supports notifications on the front end based off of query variables. Notifications can also be added manually.
 * Version:           1.1.0
 * Author:            Jupitercow
 * Author URI:        http://Jupitercow.com/
 * Contributor:       Jake Snyder
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sewn-notifications
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'Sewn_Notifications';
if (! class_exists($class_name) ) :

class Sewn_Notifications
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
	 * Holds the current notifications
	 *
	 * @since 	1.0.0
	 * @var 	array
	 */
	protected $notifications;

	/**
	 * Holds the supported queries and their messages
	 *
	 * @since 	1.0.0
	 * @var 	array
	 */
	protected $queries;

	/**
	 * Load the plugin.
	 *
	 * @since	1.1.0
	 * @return	void
	 */
	public function run()
	{
		$this->settings();
		add_action( 'init', array($this, 'init') );
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
			'strings'  => array(
				'dismiss' => __( "Dismiss this message", $this->plugin_name ),
				'close'   => __( "close", $this->plugin_name ),
			),
		);
		$this->settings = apply_filters( "{$this->prefix}/notifications/settings", $this->settings );

		$this->queries = $this->notifications = array();
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
		// Add an action to show single notifications
		add_action( "{$this->prefix}/notifications/show", array($this, 'show_notifications') );

		// Add an action to show single notifications
		add_action( "{$this->prefix}/notifications/add",  array($this, 'add_notification'), 10, 2 );

		// AJAX to dismiss notification
		add_action( "wp_ajax_{$this->prefix}_notifications_dismiss", array($this, 'dismiss') );

		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
	}

	/**
	 * Dismiss persistant notifications permanently
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function dismiss()
	{
		if ( empty($_POST['event']) ) { return false; }

		$current_user = wp_get_current_user();
		update_user_meta( $current_user->ID, "{$this->plugin_name}_{$_POST['event']}_dismissed", current_time('timestamp') );
	}

	/**
	 * Add queries and messages to the instance
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	string $args Arguments for a new notification(s). The query key/value for request, message for notification, and notification arguments.
	 * @return	void
	 */
	public function add_query( $args )
	{
		$defaults = array(
			'key'     => false,
			'value'   => false,
			'message' => false,
			'args'    => false
		);
		$args = wp_parse_args( $args, $defaults );

		$this->queries[] = $args;
	}

	/**
	 * Get the instance queries and messages, add any global queries and messages
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	array All of the queries and messages for this instance and globally
	 */
	public function get_queries()
	{
		return apply_filters( "{$this->prefix}/notifications/queries", $this->queries );
	}

	/**
	 * Process query vars into notifications
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function query_notifications()
	{
		$output = array();

		if ( $queries = $this->get_queries() )
		{
			foreach ( $queries as $query )
			{
				if (! empty($query['message']) && ! empty($_REQUEST[$query['key']]) && $query['value'] == $_REQUEST[$query['key']] )
				{
					$output_args = array( 'message' => $query['message'] );
					$output_args['args'] = (! empty($query['args']) ) ? $query['args'] : array();
					$output[] = $output_args;
				}
			}
		}
		return $output;
	}

	/**
	 * Use this to add a notification to the notification area
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	array|string $notifications The actual text of the notification(s) to add.
	 * @param	array|string $args A collection of arguments to customize the notification. 'dismiss', adds a close button. 'event' applies an event to dismiss and makes it persistent unless it has been closed before. 'fade', fades out the notification after a set time. 'error' makes this an error notification.
	 * @return	void
	 */
	public function add_notification( $message, $args='' )
	{
		$this->notifications[] = array(
			'message' => $message,
			'args'    => $args
		);
	}

	/**
	 * Show all notifications in the notification area
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	string|null If there are notifications to show, this will return an HTML collection of them.
	 */
	public function get_notifications()
	{
		$notifications = array_merge( $this->notifications, $this->query_notifications() );

		$output = '<div class="' . $this->plugin_name . '">';
		foreach ( $notifications as $notification ) {
			$output .= $this->get_notification( $notification['message'], $notification['args'] );
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get and return a notification
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	string $message Message for notification
	 * @param	array $args Arguments for notification
	 * @return	string|null If there are notifications to show, this will return an HTML collection of them.
	 */
	public function get_notification( $message, $args )
	{
		$defaults = array(
			'dismiss' => false,
			'event'   => '',
			'fade'    => false,
			'error'   => false,
			'page'    => false
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( $page )
		{
			if ( false !== strpos($page, ',') ) {
				$page = explode(',', $page);
			}
			if (! is_page($page) ) { return; }
		}

		$output = $dismissed = $output_dismiss = '';

		// Set up the dismiss if it is needed
		if ( $dismiss )
		{
			// If event, test if the event has already been dismissed
			$event_data = '';
			if ( $event )
			{
				$current_user = wp_get_current_user();
				$dismissed    = get_user_meta( $current_user->ID, "{$this->plugin_name}_{$event}_dismissed", true );
				$event_data   = " data-event=\"$event\"";
			}

			$output_dismiss .= " <a class=\"{$this->plugin_name}-dismiss\"{$event_data} href=\"#dismiss\" title=\"{$this->settings['strings']['dismiss']}\">";
				$output_dismiss .= $this->settings['strings']['close'];
			$output_dismiss .= '</a>';
			$output_dismiss  = apply_filters( "{$this->prefix}/notifications/dismiss", $output_dismiss, $args );
		}

		if (! $dismissed )
		{
			if ( 1 == $fade ) {
				$fade = 'true';
			}

			// Set up the notification
			$output  = '<p' . ($error ? " class=\"{$this->plugin_name}-error\"" : '') . ($fade ? " data-fade=\"{$fade}\"" : '') . '>';
			$output .= $message;
			$output .= $output_dismiss;
			$output .= '</p>';
		}

		return $output;
	}

	/**
	 * Echo all notifications
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function show_notifications()
	{
		echo $this->get_notifications();
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
		// scripts
		wp_enqueue_script( $this->plugin_name, $this->settings['dir'] . 'assets/js/scripts.js', array( 'jquery' ), $this->version );
		$args = array(
			'url'    => admin_url( 'admin-ajax.php' ),
			'action' => "{$this->prefix}/notifications/dismiss",
			'nonce'  => wp_create_nonce( "{$this->prefix}/notifications/dismiss" )
		);
		wp_localize_script( $this->plugin_name, 'frontnotify', $args );

		// styles
		wp_enqueue_style( $this->plugin_name, $this->settings['dir'] . 'assets/css/style.css', array(), $this->version );
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