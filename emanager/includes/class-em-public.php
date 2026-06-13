<?php
/**
 * Public front-end: the [emanager] dashboard shortcode and asset loading.
 *
 * The dashboard is a small single-page app. PHP outputs only a root element;
 * the JS router fetches HTML partials (header / navbar / sidebar / footer)
 * and module templates on demand, so pages stay tiny and cacheable.
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end shortcode and asset registration.
 */
class EM_Public {

	/**
	 * Singleton instance.
	 *
	 * @var EM_Public|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return EM_Public
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook shortcode and asset registration.
	 */
	private function __construct() {
		add_shortcode( 'emanager', array( $this, 'render_app' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register (not enqueue) all assets; they load only on eManager pages.
	 * All third-party libraries are bundled locally (no CDN), per the
	 * WordPress.org plugin guidelines.
	 */
	public function register_assets() {
		$vendor = EM_URL . 'public/vendor/';

		wp_register_style( 'em-bootstrap', $vendor . 'bootstrap/bootstrap.min.css', array(), '5.3.3' );
		wp_register_style( 'em-bootstrap-icons', $vendor . 'bootstrap-icons/bootstrap-icons.min.css', array(), '1.11.3' );
		wp_register_script( 'em-bootstrap', $vendor . 'bootstrap/bootstrap.bundle.min.js', array(), '5.3.3', true );
		wp_register_script( 'em-jspdf', $vendor . 'jspdf/jspdf.umd.min.js', array(), '2.5.2', true );
		wp_register_script( 'em-jspdf-autotable', $vendor . 'jspdf/jspdf.plugin.autotable.min.js', array( 'em-jspdf' ), '3.8.4', true );
		wp_register_script( 'em-chartjs', $vendor . 'chartjs/chart.umd.js', array(), '4.4.4', true );

		// Plugin assets.
		wp_register_style( 'emanager', EM_URL . 'public/css/emanager.css', array( 'em-bootstrap', 'em-bootstrap-icons' ), EM_VERSION );
		wp_register_style( 'em-auth', EM_URL . 'public/css/em-auth.css', array( 'em-bootstrap' ), EM_VERSION );

		$deps = array( 'em-bootstrap', 'em-jspdf-autotable', 'em-chartjs' );
		wp_register_script( 'em-api', EM_URL . 'public/js/em-api.js', array(), EM_VERSION, true );
		wp_register_script( 'em-templates', EM_URL . 'public/js/em-templates.js', array( 'em-api' ), EM_VERSION, true );
		wp_register_script( 'em-table', EM_URL . 'public/js/em-table.js', array( 'em-templates' ), EM_VERSION, true );
		wp_register_script( 'em-form', EM_URL . 'public/js/em-form.js', array( 'em-templates' ), EM_VERSION, true );
		wp_register_script( 'em-view', EM_URL . 'public/js/em-view.js', array( 'em-templates' ), EM_VERSION, true );
		wp_register_script( 'em-pdf', EM_URL . 'public/js/em-pdf.js', array( 'em-jspdf-autotable' ), EM_VERSION, true );
		wp_register_script( 'em-app', EM_URL . 'public/js/em-app.js', array_merge( $deps, array( 'em-api', 'em-templates', 'em-table', 'em-form', 'em-view', 'em-pdf' ) ), EM_VERSION, true );
	}

	/**
	 * Assets for login / registration pages only.
	 */
	public function enqueue_auth_assets() {
		wp_enqueue_style( 'em-auth' );
	}

	/**
	 * Render the [emanager] app shell and enqueue the app bundle.
	 *
	 * @return string Shell markup.
	 */
	public function render_app() {
		if ( ! is_user_logged_in() || ! current_user_can( 'em_read' ) ) {
			return '<div class="alert alert-warning">' . esc_html__( 'Please sign in to access the dashboard.', 'emanager' ) . '</div>';
		}

		wp_enqueue_style( 'emanager' );
		wp_enqueue_script( 'em-app' );

		wp_localize_script(
			'em-api',
			'EM_CONFIG',
			array(
				'apiRoot'     => esc_url_raw( rest_url( 'em/v1' ) ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'partialsUrl' => EM_URL . 'public/partials/',
				'defaultsUrl' => EM_URL . 'modules/_defaults/',
				'logoutUrl'   => wp_logout_url( get_permalink( (int) get_option( 'em_page_login' ) ) ),
				'siteName'    => get_bloginfo( 'name' ),
			)
		);

		return '<div id="em-app" class="em-app" data-loading="true">'
			. '<div class="em-boot-spinner"><div class="spinner-border text-primary" role="status"></div><p>' . esc_html__( 'Loading eManager…', 'emanager' ) . '</p></div>'
			. '</div>';
	}
}
