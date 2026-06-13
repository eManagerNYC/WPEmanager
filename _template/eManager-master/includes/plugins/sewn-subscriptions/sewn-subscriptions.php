<?php

/**
 * @link              https://github.com/jupitercow/sewn-in-subscriptions
 * @since             1.1.0
 * @package           Sewn_Subscriptions
 *
 * @wordpress-plugin
 * Plugin Name:       Sewn In Subscriptions
 * Plugin URI:        https://wordpress.org/plugins/sewn-in-subscriptions/
 * Description:       Unsubscribe from emails. This really just creates an interface for unsubscribing, it doesn't actually do anything more than set usermeta data that can be used by other functions.
 * Version:           1.1.0
 * Author:            Jupitercow
 * Author URI:        http://Jupitercow.com/
 * Contributor:       Jake Snyder
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sewn_subscriptions
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'Sewn_Subscriptions';
if (! class_exists($class_name) ) :

class Sewn_Subscriptions
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
				'subscription' => array(
					'page_name'    => 'subscription',
					'page_title'   => __( "Update Subscription", $this->plugin_name ),
					'page_content' => '',
				),
			),
			'types'    => array(
				'announcements' => __( "Announcements", $this->plugin_name ),
				'promotions'    => __( "Promotions", $this->plugin_name ),
			),
			'strings'  => array(
				'button_profile'                   => __( "Update", $this->plugin_name ),
				'label_unsubscribe'                => __( "Unsubscribe from lists", $this->plugin_name ),
				'label_checkbox'                   => __( "Check the boxes next to lists you want to unsubscribe from.", $this->plugin_name ),
				'label_update'                     => __( "Update Subscriptions", $this->plugin_name ),
				'notification_subscribe'           => __( "Successfully subscribed", $this->plugin_name ),
				'notification_unsubscribe'         => __( "Successfully unsubscribed", $this->plugin_name ),
				'notification_subscription_update' => __( "Subscription successfully updated", $this->plugin_name ),
			),
		);
		$this->settings['messages'] = array(
			'subscribe' => array(
				'key'     => 'action',
				'value'   => 'subscribe',
				'message' => $this->settings['strings']['notification_subscribe'],
				'args'    => 'fade=true&page=' . $this->settings['pages']['subscription']['page_name'],
			),
			'unsubscribe' => array(
				'key'     => 'action',
				'value'   => 'unsubscribe',
				'message' => $this->settings['strings']['notification_unsubscribe'],
				'args'    => 'fade=true&page=' . $this->settings['pages']['subscription']['page_name'],
			),
			'subscription_update' => array(
				'key'     => 'action',
				'value'   => 'subscription_update',
				'message' => $this->settings['strings']['notification_subscription_update'],
				'args'    => 'fade=true&page=' . $this->settings['pages']['subscription']['page_name'],
			)
		);
	}

	/**
	 * On plugins_loaded test if we can use sewn_notifications
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function plugins_loaded()
	{
		// Have the login plugin use frontend notifictions plugin
		if ( apply_filters( "{$this->prefix}/subscriptions/use_sewn_notifications", true ) )
		{
			if ( class_exists('Sewn_Notifications') ) {
				add_filter( "{$this->prefix}/notifications/queries", array($this, 'add_notification_messages') );
			} else {
				add_filter( "{$this->prefix}/subscriptions/use_sewn_notifications", '__return_false' );
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
		$this->settings = apply_filters( "{$this->prefix}/subscriptions/settings", $this->settings );

		$this->plugins_loaded();

		// Add the subscription update action
		add_filter( "{$this->prefix}/subscriptions/url",  array($this, 'update_subscription_url') );
		add_filter( "{$this->prefix}/subscriptions/link", array($this, 'update_subscription_link') );

		// Add custom page content
		add_filter( 'the_content',                        array($this, 'the_content') );

		// Add a fake post for profile and register pages if they don't already exist
		add_filter( 'the_posts',                          array($this, 'add_post') );
		add_filter( 'template_include',                   array($this, 'template_include'), 99 );

		// Process form and redirect as needed
		add_action( 'template_redirect',                  array($this, 'template_redirect') );

		// Add a dropdown to profile
		#add_action( 'show_user_profile',                  array($this, 'user_profile') );
		#add_action( 'edit_user_profile',                  array($this, 'user_profile') );

		// Process the profile dropdown
		#add_action( 'personal_options_update',            array($this, 'save_user_profile') );
		#add_action( 'edit_user_profile_update',           array($this, 'save_user_profile') );

		$this->register_field_groups();
	}

	/**
	 * Creates an anchor link for updating email subscriptions
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 *
	 * @param	array	$args: an array holding the options
	 *			string	+ action: Action to be taken, either subscribe or unsubscribe
	 *			int		+ user_id: The ID of the user the link is for
	 *			string	+ type: Type of action to take, right now this doesn't matter
	 * @return	void
	 */
	public function update_subscription_link( $args='' )
	{
		$defaults = array(
			'text'    => false,
			'title'   => false,
			'action'  => false,
			'class'   => 'button',
			'user_id' => false
		);
		$args = wp_parse_args( $args, $defaults );

		if (! $args['action'] )
		{
			$user = $this->get_user($args['user_id']);
			$status = get_user_meta($user->ID, "{$this->plugin_name}_status", true);
			$args['action'] = ( 'unsubscribed' == $status ) ? 'subscribe' : 'unsubscribe';
		}

		extract( $args, EXTR_SKIP );

		if (! $text )  $text  = ( 'subscribe' == $action ) ? apply_filters( "{$this->prefix}/subscriptions/text=subscribe", "Subscribe") : apply_filters( "{$this->prefix}/subscriptions/text=unsubscribe", "Unsubscribe");
		if (! $title ) $title = ( 'subscribe' == $action ) ? apply_filters( "{$this->prefix}/subscriptions/title=subscribe", "Subscribe to Emails") : apply_filters( "{$this->prefix}/subscriptions/title=unsubscribe", "Unsubscribe from Emails");
		$class .= " subscription_update subscription_" . $action;

		$url = apply_filters( "{$this->prefix}/subscriptions/url", $args );
		echo "<a class=\"$class\" href=\"$url\" title=\"$title\">$text</a>";
	}

	/**
	 * Creates a url for updating email subscriptions
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 *
	 * @param	array	$args: an array holding the options
	 *			string	+ action: Action to be taken, either subscribe or unsubscribe
	 *			int		+ user_id: The ID of the user the link is for
	 *			string	+ type: Type of action to take, right now this doesn't matter
	 * @return	void
	 */
	public function update_subscription_url( $args='' )
	{
		$defaults = array(
			'action'  => 'unsubscribe',
			'user_id' => false,
			'type'    => 'all'
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		$user = $this->get_user($user_id);
		$hash = $this->get_hash($user);
		return add_query_arg( array( 'eid' => $hash, 'action' => urlencode($action), 'type' => urlencode($type), 'uid' => $user->ID ), home_url('/'. $this->settings['pages']['subscription']['page_name'] .'/') );
	}

	/**
	 * Creates a hash for links to securely allow users access
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function get_hash( $user=false )
	{
		$user = $this->get_user($user);
		if (! $user ) return false;

		$hash = hash('sha256', site_url() . $user->ID . $user->user_login . $user->data->user_pass);
		return $hash;
	}

	/**
	 * Test hash against user
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function test_hash( $submitted_hash, $user_id )
	{
		$user = get_user_by( 'id', $user_id );
		if (! $user ) return false;

		$hash = hash('sha256', site_url() . $user->ID . $user->user_login . $user->data->user_pass);
		if ( $hash == $submitted_hash ) return true;

		return false;
	}

	/**
	 * Get the user
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function get_user( $user_id=false )
	{
		$user = false;
		if ( $user_id && is_numeric($user_id) ) {
			$user = get_user_by( 'id', $user_id );
		} elseif ( is_object($user_id) ) {
			$user = $user_id;
		} elseif (! empty($_REQUEST['eid']) && ! empty($_REQUEST['uid']) && $this->test_hash($_REQUEST['eid'], $_REQUEST['uid']) ) {
			$user = get_user_by( 'id', $_REQUEST['uid'] );
		} else {
			$user = wp_get_current_user();
		}
		return $user;
	}

	/**
	 * Add this plugin's notification messages to the sewn_notifications plugin.
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	array $queries The modified queries for the frontend_notification plugin
	 */
	public function add_notification_messages( $queries )
	{
		$queries = array_merge($queries, $this->settings['messages']);
		return $queries;
	}

	/**
	 * See if not register post exists and add it dynamically if not
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	object $posts Modified $posts with the new register post
	 */
	public function add_post( $posts )
	{
		global $wp, $wp_query;

		// Check if the requested page matches our target, and no posts have been retrieved
		if (! $posts && array_key_exists(strtolower($wp->request), $this->settings['pages']) )
		{
			// Add the fake post
			$posts   = array();
			$posts[] = $this->create_post( strtolower($wp->request) );

			$wp_query->is_page     = true;
			$wp_query->is_singular = true;
			$wp_query->is_home     = false;
			$wp_query->is_archive  = false;
			$wp_query->is_category = false;
			//Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
			unset($wp_query->query["error"]);
			$wp_query->query_vars["error"]="";
			$wp_query->is_404=false;
		}
		return $posts;
	}

	/**
	 * Create a dynamic post on-the-fly for the register page.
	 *
	 * source: http://scott.sherrillmix.com/blog/blogger/creating-a-better-fake-post-with-a-wordpress-plugin/
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	object $post Dynamically created post
	 */
	public function create_post( $type )
	{
		// Create a fake post.
		$post = new stdClass();
		$post->ID                    = -1;
		$post->post_author           = 1;
		$post->post_date             = current_time('mysql');
		$post->post_date_gmt         = current_time('mysql', 1);
		$post->post_content          = $this->settings['pages'][$type]['page_content'];
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
	 * Make sure that the fake page uses the page templates and not single
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @param	string $template Template file path
	 * @return	array $template
	 */
	public function template_include( $template )
	{
		global $wp, $wp_query;

		if ( is_page() && array_key_exists(strtolower($wp->request), $this->settings['pages']) )
		{
			acf_form_head();
			$new_template = locate_template( array( 'page-' . $wp->request . '.php', 'page.php' ) );
			if ( $new_template ) {
				return $new_template;
			}
		}
		return $template;
	}

	/**
	 * Adds a support for unsubscribe links
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function template_redirect()
	{
		if ( is_page($this->settings['pages']['subscription']['page_name']) )
		{
			if (! is_user_logged_in() && (empty($_GET['eid']) || empty($_GET['uid']) || ! $this->test_hash($_GET['eid'], $_GET['uid'])) )
			{
				wp_redirect( home_url('/') );
				die;
			}

			if (! empty($_GET['action']) )
			{
				$user_id = esc_sql( $_GET['uid'] );
				$action  = $_GET['action'];

				$current = get_metadata( 'user', $user_id, $this->plugin_name, true );
				if (! $current ) { $current = array(); }

				$types   = (! empty($_REQUEST['type']) ) ? array( esc_sql($_REQUEST['type']) ) : apply_filters( "{$this->prefix}/subscriptions/types", $this->settings['types'] );

				foreach ( $types as $type ) {
					if ( in_array($type, $current) && 'subscribe' == $action ) {
						$key = array_search($type, $current);
						unset($current[$key]);
					} elseif ( 'unsubscribe' == $action ) {
						$current[] = $type;
					}
				}
				update_user_meta( $user_id, $this->plugin_name, $current );
/** /
				if ( 'unsubscribe' == $action ) {
					do_action( "{$this->prefix}/notifications/add", $this->settings['strings']['notification_unsubscribe'], '' );
				} elseif ( 'subscribe' == $action ) {
					do_action( "{$this->prefix}/notifications/add", $this->settings['strings']['notification_subscribe'], '' );
				}
/**/
			}
		}
	}

	/**
	 * Adds custom content
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	string $content The post content for login page with the login form a
	 */
	public function the_content( $content )
	{
		if ( is_page($this->settings['pages']['subscription']['page_name']) && is_main_query() && in_the_loop() )
		{
			$messages = $footer = '';
			$args = false;
			if (! empty($_GET['action']) )
			{
				$action = $_GET['action'];
				if (! apply_filters( "{$this->prefix}/subscriptions/use_sewn_notifications", true ) && ! empty($this->settings['messages'][$action]['message']) ) {
					$messages = "<p class=\"{$this->plugin_name}_messages alert alert-success\">" . $this->settings['messages'][$action]['message'] . '</p>';
				}
			}

			$field_groups = apply_filters( "{$this->prefix}/subscriptions/field_groups", array("acf_{$this->plugin_name}") );
			$user = $this->get_user();
			ob_start(); ?>
			<form role="form" id="<?php echo $this->plugin_name; ?>_form" method="post">
				<?php
				// ACF Form
				acf_form( array(
					'post_id'      => 'user_'. $user->ID,
					'form'         => false,
					'field_groups' => $field_groups,
					'return'       => add_query_arg( 'action', 'subscription_update', get_permalink() ),
				) ); ?>
				<button type="submit" class="btn btn-default"><?php echo $this->settings['strings']['button_profile']; ?></button>
			</form>
			<?php
			$content .= ob_get_clean();
		}

		return $content;
	}

	/**
	 * Adds a dropdown to the admin profile to subscribe/unsubscribe user
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function user_profile( $user )
	{
		$key    = 'status';
		$status = get_user_meta( $user->ID, "{$this->plugin_name}_$key", true );
		$types  = apply_filters( "{$this->prefix}/subscriptions/types", $this->settings['types'] ); ?>
		<table class="form-table">
			<tr id="<?php echo $this->plugin_name; ?>-<?php echo $key; ?>" class="form-field field field_type-checkbox">
				<th valign="top" scope="row"><label for="<?php echo $this->plugin_name; ?>[<?php echo $key; ?>]"><?php echo $this->settings['strings']['label_unsubscribe']; ?></label></th>
				<td>
					<ul class="acf-checkbox-list checkbox vertical list-group">
					<?php foreach ( $types as $type_key => $type_title ) : ?>
						<li class="list-group-item"><label>
							<input id="<?php echo $key; ?>-<?php echo esc_attr($type_key); ?>" type="checkbox" class="checkbox" name="<?php echo $this->plugin_name; ?>[<?php echo $key; ?>][]" value="<?php echo esc_attr($type_key); ?>"<?php if ( is_array($status) && in_array($type_key, $status) ) echo ' checked="checked"'; ?>>
							<?php echo esc_html($type_title); ?>
						</label></li>
					<?php endforeach; ?>
					</ul>
					<span class="description"><?php echo $this->settings['strings']['label_checkbox']; ?></span>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Process the profile dropdown
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function save_user_profile( $user_id )
	{
		if (! current_user_can('edit_user', $user_id) ) { return false; }

		$key = 'status';
		if ( isset($_POST[$this->plugin_name][$key]) ) {
			update_user_meta( $user_id, "{$this->plugin_name}_$key", $_POST[$this->plugin_name][$key] );
		} else {
			delete_user_meta( $user_id, "{$this->plugin_name}_$key" );
		}
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
		if ( function_exists("register_field_group") )
		{
			$args = array(
				'id'              => "acf_{$this->plugin_name}",
				'title'           => $this->settings['strings']['label_update'],
				'fields'          => array (),
				'location'        => array (
					array (
						array (
							'param'        => 'ef_user',
							'operator'     => '==',
							'value'        => 'all',
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
				'menu_order'      => 100,
			);

			$args['fields'][] = array(
				'key'           => 'field_534e753f410f0',
				'label'         => $this->settings['strings']['label_unsubscribe'],
				'name'          => $this->plugin_name,
				'type'          => 'checkbox',
				'instructions'  => $this->settings['strings']['label_checkbox'],
				'choices'       => apply_filters( "{$this->prefix}/subscriptions/types", $this->settings['types'] ),
				'default_value' => '',
				'layout'        => 'vertical',
			);

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