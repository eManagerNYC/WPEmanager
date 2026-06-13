<?php

/**
 * Upgrades
 *
 * Use this function to batch update posts. Since some sites have millions of posts, we can't just loop over them without crashing the page load.
 * Using this method, we can make one time updates, when needed, in batches of 5000 posts.
 *
 * @since      3.6.0
 * @author     Jupitercow <info@jcow.com>
 */

// If this file is called directly, abort.
if ( ! defined('WPINC') ) { die; }

// Class name
$class_name = 'Eman_Upgrades';
if ( ! class_exists($class_name) ) :

class Eman_Upgrades
{
	/**
	 * The unique identifier of this plugin.
	 *
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * Plugin settings.
	 *
	 * @access   protected
	 * @var      string    $settings       The array used for settings.
	 */
	protected $settings;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct()
	{
		$this->plugin_name = strtolower(__CLASS__);
		$this->settings    = array(
			'action'        => 'eman_upgrades_action',
			'upgrades'      => array(
				array(
					'title'        => 'BIC Meta Upgrade',
					'function'     => 'bic_to_post_meta',
					'batch_size'   => 5000,
				),
			),
			'admin_pages'    => array(
				array(
					'page_title'    => 'eManager Upgrades',
					'menu_title'    => 'eManager Upgrades',
					'capability'    => 'manage_options',
					'menu_slug'     => $this->plugin_name,
					'screen_prefix' => 'tools_page_',
					'function'      => 'admin_page_main',
					'meta_boxes'    => array(),
				),
			),
		);
	}

	/**
	 * Run the plugin.
	 */
	public function run()
	{
		// Add AJAX actions and set up metaboxes
		$metaboxes = array();
		foreach ( $this->settings['upgrades'] as $upgrade )
		{
			$metaboxes[] = array(
				'id'        => $upgrade['function'],
				'title'     => $upgrade['title'],
				'function'  => 'metabox',
				'post_type' => $this->plugin_name,
				'context'   => 'normal',
				'priority'  => 'high',
				'args'      => $upgrade,
			);
		}

		// Add metabox support
		if ( $this->settings['admin_pages'] ) {
			foreach ( $this->settings['admin_pages'] as $page ) {
				add_action( 'load-' . ( ! empty($page['screen_prefix']) ? $page['screen_prefix'] : '') . $page['menu_slug'], array($this, 'load_metaboxes') );
				add_action( 'admin_footer-' . ( ! empty($page['screen_prefix']) ? $page['screen_prefix'] : '') . $page['menu_slug'], array($this, 'admin_footer') );
			}
		}

		// Add metabox actions for upgrades
		$this->settings['admin_pages'][0]['metaboxes'] = $metaboxes;

		add_action( 'wp_ajax_' . $this->settings['action'],  array($this, $this->settings['action']) );

		add_action( 'admin_menu',                            array($this, 'admin_menu') );
		add_action( 'admin_enqueue_scripts',                 array($this, 'enqueue_scripts') );
	}

	/**
	 * Run the batch
	 *
	 * @author  Jake Snyder
	 * @return 	bool true if successful, false if no posts
	 */
	public function eman_upgrades_action()
	{
		if ( empty($_POST['subaction']) ) { return false; }
		$batch_size = 5000;
		$function   = $_POST['subaction'];
		$status     = get_option( $function );

		// If this is complete, we can just return the status
		if ( $this->is_complete($status) ) {
			echo json_encode( $status );
			die;
		}

		// Run the class method
		if ( method_exists($this, $function) )
		{
			// Get the batch size
			foreach ( $this->settings['upgrades'] as $upgrade_setting ) {
				if ( $upgrade_setting['function'] === $function ) {
					$batch_size = $upgrade_setting['batch_size'];
					break;
				}
			}

			$new_status = call_user_func( array($this, $function), $batch_size, $status );

			// Store the status and return it.
			update_option( $function, $new_status );
			$new_status['output'] = $this->status_output( $new_status, $batch_size );
			echo json_encode( $new_status );
			die;
		}
		echo false;
		die;
	}

	/**
	 * Test if the status is a complete status
	 *
	 * @author  Jake Snyder
	 * @return 	bool true if successful, false if no posts
	 */
	public function is_complete( $status )
	{
		if ( ! empty($status['complete']) ) {
			return true;
		}
		return false;
	}

	/**
	 * Run the batch
	 *
	 * @author  Jake Snyder
	 * @return 	bool true if successful, false if no posts
	 */
	public function bic_to_post_meta( $batch_size, $status )
	{
		if ( ! is_array($status) ) {
			$status = array();
		}

		// Post types that need updated
		$post_types = array(
			'noc',
			'pcod',
			'tickets',
			'letter',
			'rfi',
			'dcr',
			'issue',
		);

		$current_type = $post_types[0];
		$current_page = 0;

		if ( $status )
		{
			$current_status = end($status);
			if ( ! empty($current_status['post_type']) )
			{
				// If the last post type is not done, use it
				if ( empty($current_status['complete']) )
				{
					$current_type = $current_status['post_type'];
					$current_page = ( ! empty($current_status['status']) ) ? $current_status['status'] : 0;
				}
				// Otherwise find the next post type if there is one
				else
				{
					foreach ( $post_types as $key => $post_type )
					{
						if ( $current_status['post_type'] === $post_type )
						{
							// Get the next post type if there is one
							if ( ! empty($post_types[$key+1]) ) {
								$current_type = $post_types[$key+1];
							// Otherwise, we are done...
							} else {
								$current_type = false;
							}
							break;
						}
					}
				}
			}
		}

		// If not finished, start where it left off
		if ( $current_type )
		{
			$items = new WP_Query( array(
				'post_type'      => "em_{$current_type}",
				'posts_per_page' => $batch_size,
				'offset'         => $current_page * $batch_size,
			) );

			// If there are posts, process them
			if ( ! empty($items->posts) )
			{
				foreach ( $items->posts as $item ) {
					$bic_id = emanager_bic::get_bic( $item, 'ID' );
					update_metadata( 'post', $item->ID, 'bic_user', $bic_id );
					//delete_metadata( 'post', $item->ID, 'bic' );
				}
				if ( empty($status[$current_type]['total']) ) {
					$status[$current_type]['total'] = 0;
				}
				$status[$current_type]['total']    += $items->found_posts;
				$status[$current_type]['status']    = $current_page + 1;
			}
			// Set this post type as finished
			else
			{
				#$status[$current_type]['status']    = $current_page - 1;
				$status[$current_type]['complete']  = current_time('timestamp');
			}

			// Set current time & post type
			$status[$current_type]['timestamp'] = current_time('timestamp');
			$status[$current_type]['post_type'] = $current_type;
		}
		// This update is done
		else
		{
			$status['complete'] = current_time('timestamp');
		}

		return $status;
	}

	/**
	 * Register the JavaScript for the admin page.
	 *
	 * @author  Jake Snyder
	 * @return 	void
	 */
	public function enqueue_scripts( $hook )
	{
		if ( ! in_array($hook, array('tools_page_eman_upgrades')) ) { return; } # || ! in_array($GLOBALS['post_type'], $this->post_types())

		//plugin_dir_url( __FILE__ )
		wp_enqueue_script( $this->plugin_name, get_stylesheet_directory_uri() . '/admin/assets/js/' . $this->plugin_name . '-admin.js', array(), '', false );
		$args = array(
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'action'         => $this->settings['action'],
			'prefix'         => $this->plugin_name,
			'spinner'        => admin_url( 'images/spinner.gif' ),
			'disabled_class' => 'disabled',
			'strings'        => array(
				'status_running' => "Running upgrade&hellip;",
				'status_failure' => "Upgrade failed, please contact your administrator."
			),
		);
		wp_localize_script( $this->plugin_name, $this->plugin_name, $args );
	}

	/**
	 * Add admin settings page to allow you to test email template
	 *
	 * @author  Jake Snyder
	 * @return 	void
	 */
	public function admin_menu()
	{
		if ( $this->settings['admin_pages'] )
		{
			foreach( $this->settings['admin_pages'] as $page )
			{
				$page_title = $page['page_title'];
				$menu_title = $page['menu_title'];
				$capability = $page['capability'];
				$menu_slug  = $page['menu_slug'];
				$function   = array($this, $page['function']);
				add_management_page( $page_title, $menu_title, $capability, $menu_slug, $function );
			}
		}
	}

	/**
	 * Build admin page
	 *
	 * @author  Jake Snyder
	 * @return 	void
	 */
	public function admin_page_main( $test )
	{
?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>

			<h3>Run upgrades</h3>

			<div id="poststuff" class="<?php echo $this->plugin_name; ?>_container">
				<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
 
					<div id="post-body-content">
						<p class="instructions">Clicking on a button below will run the upgrade. These can take a while and should be run one at a time&hellip;</p>
					</div>

	                <div id="postbox-container-1" class="postbox-container">
	                    <?php do_meta_boxes( $this->plugin_name, 'side', null ); ?>
	                </div>

	                <div id="postbox-container-2" class="postbox-container">
	                    <?php do_meta_boxes( $this->plugin_name, 'normal', null ); ?>
	                    <?php do_meta_boxes( $this->plugin_name, 'advanced', null ); ?>
	                </div>

				</div>
			</div>
		</div>
<?php
	}

	/**
	 * Set up the metabox(es) for admin pages
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function load_metaboxes()
	{
		// Trigger the add_meta_boxes hooks to allow meta boxes to be added
		do_action( 'add_meta_boxes', $this->plugin_name, null );
		do_action( 'add_meta_boxes_' . $this->plugin_name, null );

		// Enqueue WordPress' script for handling the meta boxes
		wp_enqueue_script( 'postbox' );

		// Add screen option: user can choose between 1 or 2 columns (default 2)
		add_screen_option( 'layout_columns', array('max' => 2, 'default' => 2) );

		if ( $this->settings['admin_pages'] )
		{
			foreach ( $this->settings['admin_pages'] as $page )
			{
				if ( $page['metaboxes'] )
				{
					foreach ( $page['metaboxes'] as $metabox )
					{
						$id            = $metabox['id'];
						$title         = $metabox['title'];
						$callback      = array($this, $metabox['function']);
						$post_type     = $metabox['post_type'];
						$context       = $metabox['context'];
						$priority      = $metabox['priority'];
						$callback_args = $metabox['args'];
						add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
					}
				}
			}
		}
	}

	/**
	 * Set up the metabox
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function metabox( $post, $metabox )
	{
		$upgrade     = $metabox['args'];
		#delete_option( $upgrade['function'] );
		$status      = get_option( $upgrade['function'] );
		$batch_size  = 5000;

		// Get the batch size
		foreach ( $this->settings['upgrades'] as $upgrade_setting ) {
			if ( $upgrade_setting['function'] === $upgrade['function'] ) {
				$batch_size = $upgrade_setting['batch_size'];
				break;
			}
		}
?>
		<div class="<?php echo $upgrade['function']; ?>">
		<?php if ( ! $this->is_complete($status) ) : ?>
			<p class="submit">
				<a href="#javascript_required" class="button <?php echo $this->plugin_name; ?>" data-function="<?php echo $upgrade['function']; ?>" title="Run <?php echo $upgrade['title']; ?>">
					Run <?php echo $upgrade['title']; ?>
				</a>
				<span class="spinner"></span>
			</p>
		<?php endif; ?>

			<h4>Status</h4>
			<ul id="<?php echo $upgrade['function']; ?>_log" class="log">
				<?php echo $this->status_output( $status, $batch_size ); ?>
			</ul>
		</div>
<?php
	}

	public function status_output( $status, $batch_size )
	{
		$date_format = get_option('date_format');

		$output = '';
		if ( is_array($status) )
		{
			$complete = $this->is_complete($status);
			if ( $complete )
			{
				$completed_stamp = $status['complete'];
				unset( $status['complete']);
				$output .= '<li class="status" style="font-weight:bold;">Completed on: ' . date_i18n( $date_format, $completed_stamp ) . '</li>';
			}

			$total = 0;
			foreach( $status as $item ) {
				$total += $item['total'];
				$output .= '<li class="' . $item['post_type'] . '">' . ucwords($item['post_type']) . ': ';
				if ( ! empty($item['complete']) ) {
					$output .= ' completed on: ' . date_i18n( $date_format, $item['complete'] ) . '.';
				}
				if ( ! empty($item['status']) ) {
					$output .= ' ' . $item['total'] . ' posts upgraded.';
				} else {
					$output .= ' No posts found.';
				}

				$output .= '</li>';
			}

			if ( $complete ) {
				$output .= "<li class=\"total\">Total: {$total} upgraded.</li>";
			} else {
				$output .= "<li class=\"status\">Ready&hellip;</li>";
			}
		}
		else
		{
			$output .= "<li class=\"status\">Ready&hellip;</li>";
		}

		return $output;
	}

	/**
	 * Load metabox scripts
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function admin_footer()
	{
?>
		<script>jQuery(document).ready(function(){ postboxes.add_postbox_toggles(pagenow); });</script>
<?php
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;