<?php

/**
 * @link              https://github.com/jupitercow/sewn-in-email-templates
 * @since             1.1.0
 * @package           Sewn_Email_Templates
 *
 * @wordpress-plugin
 * Plugin Name:       Sewn In Email Templates
 * Plugin URI:        https://wordpress.org/plugins/sewn-in-email-templates/
 * Description:       At the heart of it, this is meant to update emails to be site-specific instead of WordPress-specific, but it also updates them to be nicer looking in HTML format.
 * Version:           1.1.0
 * Author:            Jupitercow
 * Author URI:        http://Jupitercow.com/
 * Contributor:       Jake Snyder
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sewn_email_templates
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'Sewn_Email_Templates';
if (! class_exists($class_name) ) :

class Sewn_Email_Templates
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
	 * Plugin settings.
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
		$this->load_dependencies();
		$this->define_admin_hooks();

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
			'dir'     => $this->get_dir_url( __FILE__ ),
			'path'    => plugin_dir_path( __FILE__ ),
			'strings' => array(
				'social_twitter'           => __( "Follow on Twitter", $this->plugin_name ),
				'social_facebook'          => __( "Friend on Facebook", $this->plugin_name ),
				'social_linkedin'          => __( "Find on LinkedIn", $this->plugin_name ),
				'social_googleplus'        => __( "Follow on Google+", $this->plugin_name ),
				'social_pinterest'         => __( "Follow on Pinterest", $this->plugin_name ),
				'social_instagram'         => __( "Follow on Instagram", $this->plugin_name ),
				'archive_intro'            => __( "Email not displaying correctly?", $this->plugin_name ),
				'archive_text'             => __( "View it in your browser", $this->plugin_name ),
				'unsubscribe'              => __( "unsubscribe from this list", $this->plugin_name ),
				'subscription'             => __( "update subscription preferences", $this->plugin_name ),
				'title_email_test'         => __( "Email Tests", $this->plugin_name ),
				'title_send_preview'       => __( "Send a preview email to&hellip;", $this->plugin_name ),
				'button_send'              => __( "Send", $this->plugin_name ),
				'opt_registration'         => __( "Registration", $this->plugin_name ),
				'opt_registration_admin'   => __( "Registration, Admin", $this->plugin_name ),
				'opt_lost_password'        => __( "Lost Password", $this->plugin_name ),
				'notification_missing'     => __( "missing option: %s", $this->plugin_name ),
				'notification_refresh'     => __( "Refresh.", $this->plugin_name ),
				'notification_valid_email' => __( "Please enter a valid email", $this->plugin_name ),
				'notification_success'     => __( "An email preview has been successfully sent to %s", $this->plugin_name ),
				'notification_error'       => __( "An error occured while sending email. Please check your server configuration.", $this->plugin_name ),
			),
		);
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
		$this->settings = apply_filters( "{$this->prefix}/email_templates/settings", $this->settings );

		// actions
		add_action( 'phpmailer_init',            array($this, 'add_html_template') );

		// filters
		add_filter( 'wp_mail_from_name',         array($this, 'wp_mail_from_name') );
		add_filter( 'wp_mail_from',              array($this, 'wp_mail_from') );
		add_filter( 'wp_mail_content_type',      array($this, 'wp_mail_content_type'), 100 );
		add_filter( 'retrieve_password_message', array($this, 'forgot_password_brackets'), 1, 1); 
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $this->settings['path'] . "admin/class-{$this->plugin_name}-admin.php";
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Sewn_Email_Templates_Admin( $this->get_plugin_name(), $this->get_version(), $this->settings );
		add_action( 'admin_enqueue_scripts',     array($plugin_admin, 'enqueue_scripts') );
		add_action( 'admin_menu',                array($plugin_admin, 'admin_menu') );
		add_action( 'wp_ajax_send_preview',      array($plugin_admin, 'send_preview') );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Replace the email address that emails are sent from. Default: admin_email option.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function wp_mail_from( $from_email )
	{
		return apply_filters( "{$this->prefix}/email_templates/from/email", get_option('admin_email') );
	}

	/**
	 * Replaces name that emails are sent from instead of "WordPress". Default: blogname option.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function wp_mail_from_name( $from_name )
	{
		return apply_filters( "{$this->prefix}/email_templates/from/name", get_option('blogname') );
	}

	/**
	 * Switch to HTML emails by default, so we can use our new templates
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	string $content_type The current content type being used.
	 * @return	string $content_type Modified content type to html or left plaintext depending on filter input.
	 */
	public function wp_mail_content_type( $content_type )
	{
		if ( apply_filters( "{$this->prefix}/email_templates/use_html_template", true ) ) {
			return apply_filters( "{$this->prefix}/email_templates/content_type", 'text/html' );
		}

		return $content_type;
	}

	/**
	 * Add new HTML template
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function add_html_template( $phpmailer )
	{
		if (! apply_filters( "{$this->prefix}/email_templates/use_html_template", true ) ) {
			return;
		}

		/**
		 * Set up variables for templates
		 */
		$social_links = array();
		$default_social_links = array(
			'twitter',
			'facebook',
			'linkedin',
			'googleplus',
			'pinterest',
			'instagram',
		);
		foreach ( $default_social_links as $link_name )
		{
			$url = get_option("{$link_name}_url");
			$link = '';
			if ( $url ) {
				$link_text = ($text = get_option("{$link_name}_email_text") ) ? $text : $this->settings['strings']["social_{$link_name}"];
				$link      = "<a href=\"$url\" title=\"$link_text\">$link_text</a>";
			}
			$social_links[strtoupper($link_name)] = $link;
		}

		$unsubscribe_link = $profile_link = $archive_link = '';
		$unsubscribe_url  = apply_filters( "{$this->prefix}/email_templates/unsubscribe_url",       '' );
		$unsubscribe_text = apply_filters( "{$this->prefix}/email_templates/unsubscribe_text",      $this->settings['strings']['unsubscribe'] );
		if ( $unsubscribe_url ) {
			$unsubscribe_link = "<a href=\"{$unsubscribe_url}\" style=\"-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;color:#606060;font-weight:normal;text-decoration:underline;\">{$unsubscribe_text}</a>";
		}
		$profile_url      = apply_filters( "{$this->prefix}/email_templates/profile_url",           '' );
		$profile_text     = apply_filters( "{$this->prefix}/email_templates/profile_text",          $this->settings['strings']['subscription'] );
		if ( $profile_url ) {
			$profile_link     = "<a href=\"{$profile_url}\" style=\"-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;\">{$profile_text}</a>";
		}
		$archive_url      = apply_filters( "{$this->prefix}/email_templates/archive_url",           '' );
		$archive_text     = apply_filters( "{$this->prefix}/email_templates/archive_text",          $this->settings['strings']['archive_text'] );
		$archive_intro    = apply_filters( "{$this->prefix}/email_templates/archive_intro",         $this->settings['strings']['archive_intro'] );
		if ( $archive_url ) {
			$archive_link     = "$archive_intro<br><a href=\"{$archive_url}\" target=\"_blank\" style=\"-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;\">{$archive_text}</a>";
		}

		$variables = array(
			'SUBJECT'         => $phpmailer->Subject,
			'CURRENT_DATE'    => date_i18n( get_option('date_format') ),
			'CURRENT_YEAR'    => date_i18n('Y'),
			'TEASER'          => apply_filters( "{$this->prefix}/email_templates/teaser", get_option('email_templates_teaser') ),
			'ARCHIVE'         => $archive_link,
			'HEADERIMAGE'     => apply_filters( "{$this->prefix}/email_templates/headerimage", get_option('email_templates_headerimage') ),
			'HEADERIMAGE_ALT' => apply_filters( "{$this->prefix}/email_templates/headerimage_alt", get_option('email_templates_headerimage_alt') ),
			'BLOGINFO'        => array(
				'URL'            => home_url(),
				'NAME'           => get_bloginfo('name'),
				'DESCRIPTION'    => get_bloginfo('description'),
				'RSS'            => get_bloginfo('rss_url'),
				'RSS2'           => get_bloginfo('rss2_url'),
				'ATOM'           => get_bloginfo('atom_url'),
			),
			'SOCIAL'          => $social_links,
			'ADDRESS'         => apply_filters( "{$this->prefix}/email_templates/address", get_option('address') ),
			'UNSUB'           => $unsubscribe_link,
			'UPDATE_PROFILE'  => $profile_link,
			'TITLE'           => '',
			'SUBTITLE'        => '',
			'CONTENT'         => '',
		);
		$variables = apply_filters( "{$this->prefix}/email_templates/variables", $variables );

		/**
		 * Plain Text Template
		 */
		$variables['CONTENT'] = wp_specialchars_decode( $phpmailer->Body, ENT_QUOTES );
		$template_filename    = 'plain.txt';
		$template_file_uri    = $this->locate_template_file( $template_filename );
		if ( $template_file_uri && is_file($template_file_uri) )
		{
			$stream  = fopen($template_file_uri,"r");
			$message = stream_get_contents($stream);
			fclose($stream);

			foreach ( $variables as $key => $value )
			{
				if ( is_array($value) )
				{
					foreach ( $value as $subkey => $subvalue ) {
						$message = $this->_parse_template( "$key:$subkey", $message, $subvalue );
					}
				}
				else
				{
					$message = $this->_parse_template( $key, $message, $value );
				}
			}

			$phpmailer->AltBody = $message;
		}


		/**
		 * HTML Template
		 */
		$content              = $phpmailer->Body;
		if ( apply_filters( "{$this->prefix}/email_templates/the_content", true ) ) {
			#$content = apply_filters( 'the_content', $content );
		}
		$content              = $this->clean_textlinks($content);  // Fix wp footer link
		$content              = nl2br( make_clickable($content) ); // Convert line breaks & make links clickable
		$template_filename    = 'html.html';
		$variables['CONTENT'] = $content;
		$template_file_uri    = $this->locate_template_file( $template_filename );
		if ( $template_file_uri && is_file($template_file_uri) )
		{
			$stream  = fopen($template_file_uri,"r");
			$message = stream_get_contents($stream);
			fclose($stream);

			foreach ( $variables as $key => $value )
			{
				if ( is_array($value) )
				{
					foreach ( $value as $subkey => $subvalue ) {
						$message = $this->_parse_template( "$key:$subkey", $message, $subvalue );
					}
				}
				else
				{
					$message = $this->_parse_template( $key, $message, $value );
				}
			}

			$phpmailer->Body = $message;
		}
/** /
echo '<pre style="font-size:0.7em;text-align:left;">';
print_r($phpmailer->Body);
echo "</pre>\n";
exit;
/**/
	}

	private function _parse_template( $var_name, $content, $value='' )
	{
		$pattern = "#\*\|IF\:$var_name\|\*(.*?)\*\|END\:IF\|\*#si";
#echo ' $pattern = '. $pattern ."<br>\n";
/** /
echo '<pre style="font-size:0.7em;text-align:left;">';
print_r($content);
echo "</pre>\n";
#exit;
/**/
		if ( $value )
		{
			$content = preg_replace($pattern, '$1', $content);
			$content = str_replace("*|$var_name|*", $value, $content);
		}
		else
		{
			$content = preg_replace($pattern, '', $content);
			$content = str_replace("*|$var_name|*", '', $content);
		}
		return $content;
	}

	/**
	 * Load the correct email template file
	 *
	 * Looks in the theme folder first, then loads from the plugin if no theme-override. Can load plaintext or HTML, HTML by default.
	 *
	 * @author  Jake Snyder
	 * @since 	1.0.0
	 * @param 	string $template The name of the template file
	 * @return 	string $file The location of the file to load
	 */
	public function locate_template_file( $template_filename )
	{
		$template_file_uri = '';

		// Check theme folder first
		if ( $theme_file_uri = locate_template( array( str_replace('_','-', $this->plugin_name) . '/' . $template_filename ) ) ) {
			$template_file_uri = $theme_file_uri;
		}
		// Then load from plugin
		elseif ( is_file(plugin_dir_path( __FILE__ ) . 'templates/' . $template_filename) ) {
			$template_file_uri = plugin_dir_path( __FILE__ ) . 'templates/' . $template_filename;
		}

		$template_filename_array = explode('.', $template_filename);

		if ( is_array($template_filename_array) ) {
			$filter = reset($template_filename_array);
			$template_file_uri = apply_filters( "{$this->prefix}/email_templates/template/$filter", $template_file_uri );
		}

		return $template_file_uri;
	}

	/**
	 * Replaces the < & > of the 3.1 email text links
	 *
	 * @since	1.0.0
	 * @param	string $content Content of email
	 * @return	string Content of email with link cleaned up
	 */
	public function clean_textlinks( $content )
	{
		return preg_replace( '#<(https?://[^*]+)>#', '$1', $content );
	}

	/**
	 * Sanitize "forgot password" email
	 *
	 * By default, this email is plaintext, but includes angle brackets. We convert them to square brackets
	 * so that they don't get mangled in HTML processing
	 *
	 * @author  Benjamin Coy
	 * @since	1.0.1
	 * @return	string
	 */
	public function forgot_password_brackets( $message )
	{
		$message = str_replace(array('<', '>'), '', $message);
		return $message;
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