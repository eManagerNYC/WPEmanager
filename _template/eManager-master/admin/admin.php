<?php
/**
 * Make adjustments to the admin
 */

// actions
add_action( 'admin_init',                 'eman_admin_color_scheme' );
add_action( 'admin_enqueue_scripts',      'eman_load_default_css' );
add_filter( 'get_user_option_admin_color','eman_force_admin_color' );
add_action( 'admin_menu',                 'eman_disable_dashboard_widgets' );
add_action( 'admin_head',                 'eman_admin_favicon', 11 );
add_action( 'welcome_panel',              'eman_dashboard_welcome_cleanup' );
add_action( 'admin_menu',                 'eman_remove_menu_pages' );
add_action( 'wp_before_admin_bar_render', 'eman_customize_admin_bar' );
add_action( 'admin_init',                 'eman_dependencies' );
add_action( 'wp_before_admin_bar_render', 'eman_adminbar_titles' );
add_action( 'wp_head', 					  'eman_admin_bar_styles' );

// filters
add_filter( 'show_admin_bar',             'eman_admin_bar_permissions' );
add_filter( 'gettext',                    'eman_replace_howdy', 10, 3 );
add_filter( 'admin_footer_text',          'eman_admin_footer' );
add_filter( 'screen_options_show_screen', 'eman_remove_screen_options' );

/**
 * Register the custom admin color scheme
 */
function eman_admin_color_scheme()
{
	wp_admin_css_color(
		'turner',
		"Turner",
		get_template_directory_uri() . '/admin/assets/css/style.css',
		array( '#00204e', '#999999', '#005bc3', '#cd5806' )
		//array( 'base' => '#f1f2f3', 'focus' => '#fff', 'current' => '#fff' )
	);
}

/**
 * Make sure core's default `colors.css` gets enqueued, since we can't
 * @import it from a plugin stylesheet. Also force-load the default colors
 * on the profile screens, so the JS preview isn't broken-looking.
 */
function eman_load_default_css()
{
	global $wp_styles;

	$color_scheme = get_user_option( 'admin_color' );

	if ( 'turner' === $color_scheme || in_array( get_current_screen()->base, array( 'profile', 'profile-network' ) ) ) {
		$wp_styles->registered[ 'colors' ]->deps[] = 'colors-fresh';
	}
}

/**
 * Force the color choice
 */
function eman_force_admin_color($result) {
    return 'turner';
}


/**
 * Modify the admin bar left label
 */
function eman_adminbar_titles()
{
	$GLOBALS['wp_admin_bar']->add_menu( array(
		'id'    => 'site-name',
		'title' => ( is_admin() ? "Home" : "Admin Area" ),
	) );
}

/**
 * Change admin area left icon to odometer
 */
function eman_admin_bar_styles()
{
	if ( is_user_logged_in() )
	{
	    echo "<style type='text/css'>
	    #wpadminbar #wp-admin-bar-site-name>.ab-item:before {
			content: '\\f226';
			top: 2px;
		}
		</style>";
	}
}

/**
 * Check for dependencies
 */
function eman_dependencies()
{
	if ( ! class_exists('acf') ) {
		add_action( 'admin_notices', 'eman_acf_dependency_message' );
	}
}

/**
 * Add a nag for required dependencies that are missing
 */
function eman_acf_dependency_message()
{
?>
	<div class="update-nag">
		This theme requires the <a href="http://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> plugin to be installed and activated.
	</div>
<?php
}


/**
 * Disable default dashboard widgets.
 */
function eman_disable_dashboard_widgets()
{

	remove_meta_box('dashboard_right_now', 'dashboard', 'core');    	// Right Now Widget
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'core'); 	// Incoming Links Widget
	remove_meta_box('dashboard_plugins', 'dashboard', 'core');			// Plugins Widget
	remove_meta_box('dashboard_quick_press', 'dashboard', 'core');		// Quick Press Widget
	remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core');	// Recent Drafts Widget
	remove_meta_box('dashboard_activity', 'dashboard', 'core');			// Activity Widget
	remove_meta_box('dashboard_primary', 'dashboard', 'core');			// WordPress News Widget

	// removing plugin dashboard boxes
	remove_meta_box('yoast_db_widget', 'dashboard', 'normal');			// Yoast's SEO Plugin Widget
	remove_meta_box('tribe_dashboard_widget', 'dashboard', 'normal');	// Modern Tribe Plugin Widget
	remove_meta_box('rg_forms_dashboard', 'dashboard', 'normal');		// Gravity Forms Plugin Widget
	remove_meta_box('bbp-dashboard-right-now', 'dashboard', 'core');	// bbPress Plugin Widget

}

/**
 * Add a developer favicon
 */
function eman_admin_favicon()
{
	?>
	<link rel="icon" href="<?php echo get_template_directory_uri() . '/admin/assets/img/favicon.png'; ?>">
	<!--[if IE]>
		<link rel="shortcut icon" href="<?php echo get_template_directory_uri() . '/admin/assets/img/favicon.ico'; ?>">
	<![endif]-->
	<?php
}

/**
 * Remove some screen options from the dashboard
 */
function eman_dashboard_welcome_cleanup()
{
	global $pagenow;

	if ( 'index.php' == $pagenow )
	{
		?>
		<style type="text/css">
			.welcome-panel-column h4,
			.welcome-panel-last,
			.hide-if-no-customize {display: none !important;}
		</style>
		<?php
	}
}

/**
 * Remove some admin pages that we never want
 */
function eman_remove_menu_pages()
{
	remove_menu_page('link-manager.php');
	if ( ! current_user_can('manage_options') ) remove_menu_page('tools.php');
}

/**
 * Remove top admin menu items
 *
 * @return	void
 */
function eman_customize_admin_bar()
{
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('search');
	$wp_admin_bar->remove_menu('wp-logo');
	$wp_admin_bar->remove_menu('about');
	$wp_admin_bar->remove_menu('wporg');
	$wp_admin_bar->remove_menu('documentation');
	$wp_admin_bar->remove_menu('support-forums');
	$wp_admin_bar->remove_menu('feedback');
	$wp_admin_bar->remove_menu('view-site');
	$wp_admin_bar->remove_menu('new-content');
	$wp_admin_bar->remove_menu('new-link');
	$wp_admin_bar->remove_menu('new-media');
	$wp_admin_bar->remove_menu('new-user');
	if ( ! is_admin() ) { 
		$wp_admin_bar->remove_menu('my-account'); // removes my account bar from top right
	}
}

/**
 * Force the admin bar on for editors and admins and off for below
 *
 * @return	array Modified settings
 */
function eman_admin_bar_permissions( $content )
{
	return ( current_user_can('edit_others_posts') ) ? true : false;
}

/**
 * Replace howdy in the admin bar
 *
 * @return	string Modified welcome message.
 */
function eman_replace_howdy( $translated, $text, $domain )
{
	if ( false !== strpos($translated, "Howdy") ) {
		return str_replace("Howdy", "Welcome back", $translated);
	}
	return $translated;
}

/**
 * Customize admin footer
 */
function eman_admin_footer()
{
?>
	<span id="footer-thankyou">Crafted with WordPress by <?php echo eman_info('parentcompany', 'link'); ?></span>
<?php
}

/**
 * Remove screen options from the dashboard
 */
function eman_remove_screen_options()
{
	global $pagenow;

	if ( 'index.php' == $pagenow ) return false;
	return true;
}
