<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/jupitercow/sewn-in-email-templates
 * @since      1.1.0
 *
 * @package    Sewn_Email_Templates
 * @subpackage Sewn_Email_Templates/admin
 * @author     Jake Snyder <jake@jcow.com>
 */
class Sewn_Email_Templates_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $settings       The array used for settings.
	 */
	protected $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $settings )
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->settings    = $settings;
		$this->settings['dir'] .= basename( __DIR__ ) . '/';
		$this->settings['path'] = plugin_dir_path( __FILE__ );
	}

	/**
	 * Add admin settings page to allow you to test email template
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return 	void
	 */
	public function admin_menu()
	{
		add_options_page(
			$this->settings['strings']['title_email_test'],
			$this->settings['strings']['title_email_test'],
			'manage_options',
			$this->plugin_name . '_options',
			array($this, 'admin_page')
		);
	}

	/**
	 * Build admin page
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return 	void
	 */
	public function admin_page()
	{
		?>
		<div class="wrap">
			<h2><?php _e( "Email settings", $this->plugin_name ); ?></h2>

			<h3><?php _e( "Send Preview", $this->plugin_name ); ?></h3>

			<p class="instructions <?php echo $this->plugin_name; ?>_instructions"></p>

			<div id="message"></div>
			<table class="form-table">
				<tr valign="top">

					<th scope="row">
						<label for="<?php echo $this->plugin_name; ?>_email"><?php echo $this->settings['strings']['title_send_preview']; ?></label>
					</th>

					<td>
						<input type="text" id="<?php echo $this->plugin_name; ?>_email" class="regular-text" value="<?php echo esc_attr( get_option('admin_email') ); ?>" />
						<select id="<?php echo $this->plugin_name; ?>_type">
							<option value="registration"><?php echo $this->settings['strings']['opt_registration']; ?></option>
							<option value="registration_admin"><?php echo $this->settings['strings']['opt_registration_admin']; ?></option>
							<option value="lost_password"><?php echo $this->settings['strings']['opt_lost_password']; ?></option>
						</select>
						<a href="#send_preview" class="button" id="<?php echo $this->plugin_name; ?>_send_preview"><?php echo $this->settings['strings']['button_send']; ?></a>
					</td>

				</tr>
			</table>

		</div>
		<?php
	}

	/**
	 * Send an email preview to test the template
	 *
	 * These email messages are just copied from wp-login.php and "wp_new_user_notification()" in wp-includes/pluggable.php, if they change there, they will need to be recopied.
	 *
	 * @author  Jake Snyder
	 * @since 	1.0.0
	 * @return	void
	 */
	public function send_preview()
	{
		if (! current_user_can('manage_options') ) die();

   		// vars
		$options = array(
			'email' => '',
			'type'  => 'registration',
			'nonce' => ''
		);

		// load post options
		$options = array_merge($options, $_POST);

		// test options
		foreach ( $options as $key => $option )
		{
			if (! $option ) {
				die( sprintf($this->settings['strings']['notification_missing'], $key) );
			}
		}

		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], "{$this->plugin_name}/nonce/email_preview") ) {
			die( $this->settings['strings']['notification_refresh'] );
		}

		$email = sanitize_email( $options['email'] );

		if (! is_email($email) ) {
			die( $this->settings['strings']['notification_valid_email'] );
		}

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		// From wp-login.php
		if ( 'lost_password' == $options['type'] )
		{
			$title = sprintf( __('[%s] Password Reset'), $blogname );

			$message  = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
			$message .= network_home_url( '/' ) . "\r\n\r\n";
			$message .= sprintf(__('Username: %s'), '%username%') . "\r\n\r\n";
			$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
			$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
			$message .= '<' . network_site_url("wp-login.php?action=rp&key=%key%&login=%username%", 'login') . ">\r\n";
		}
		// From pluggable.php
		elseif ( 'registration_admin' == $options['type'] )
		{
			$title = sprintf(__('[%s] New User Registration'), $blogname);

			$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
			$message .= sprintf(__('Username: %s'), '%username%') . "\r\n\r\n";
			$message .= sprintf(__('E-mail: %s'), '%email@address.com%') . "\r\n";
		}
		// From pluggable.php
		else
		{
			$title = sprintf(__('[%s] Your username and password'), $blogname);

			$message  = sprintf(__('Username: %s'), '%username%') . "\r\n";
			$message .= sprintf(__('Password: %s'), '%password%') . "\r\n";
			$message .= wp_login_url() . "\r\n";
		}

		// Send the preview email
		if ( wp_mail($email, $title, $message) ) {
			die( sprintf( $this->settings['strings']['notification_success'], $email ) );
		} else {
			die( $this->settings['strings']['notification_error'] );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.1.0
	 */
	public function enqueue_scripts( $handle )
	{
		if ( "settings_page_{$this->plugin_name}_options" != $handle ) { return; }
		// register acf scripts
		wp_enqueue_script( $this->plugin_name, $this->settings['dir'] . "js/{$this->plugin_name}-admin.js", array( 'jquery' ), $this->version, false );
		$args = array(
			'url'     => admin_url( 'admin-ajax.php' ),
			'action'  => 'send_preview',
			'prefix'  => $this->plugin_name,
			'nonce'   => wp_create_nonce( "{$this->plugin_name}/nonce/email_preview" ),
			'spinner' => admin_url( 'images/loading.gif' )
		);
		wp_localize_script( $this->plugin_name, 'email_preview', $args );
	}
}