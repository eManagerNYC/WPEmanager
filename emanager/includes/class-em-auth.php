<?php
/**
 * Authentication: front-end login & registration pages, access control.
 *
 * Shortcodes:
 *   [emanager_login]     Bootstrap login form
 *   [emanager_register]  Bootstrap registration form (creates em_viewer users,
 *                       pending role assignment by an administrator)
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end auth pages and dashboard access guard.
 */
class EM_Auth {

	/**
	 * Singleton instance.
	 *
	 * @var EM_Auth|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return EM_Auth
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook shortcodes, form handlers and the dashboard guard.
	 */
	private function __construct() {
		add_shortcode( 'emanager_login', array( $this, 'login_form' ) );
		add_shortcode( 'emanager_register', array( $this, 'register_form' ) );
		add_action( 'init', array( $this, 'handle_forms' ) );
		add_action( 'template_redirect', array( $this, 'guard_dashboard' ) );
	}

	/**
	 * Create the Dashboard / Login / Register pages on activation.
	 */
	public static function create_pages() {
		$pages = array(
			'em_page_dashboard' => array( 'eManager Dashboard', '[emanager]' ),
			'em_page_login'     => array( 'eManager Login', '[emanager_login]' ),
			'em_page_register'  => array( 'eManager Register', '[emanager_register]' ),
		);
		foreach ( $pages as $option => $page ) {
			list( $title, $shortcode ) = $page;

			$existing = get_option( $option );
			if ( $existing && get_post_status( $existing ) ) {
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => $shortcode,
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_option( $option, $id );
			}
		}
	}

	/**
	 * Redirect logged-out (or restricted) users away from the dashboard page.
	 */
	public function guard_dashboard() {
		$dashboard = (int) get_option( 'em_page_dashboard' );
		if ( ! $dashboard || ! is_page( $dashboard ) ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			$login_url = get_permalink( (int) get_option( 'em_page_login' ) );
			wp_safe_redirect( $login_url ? $login_url : wp_login_url() );
			exit;
		}
		if ( ! current_user_can( 'em_read' ) ) {
			wp_die(
				esc_html__( 'Your account does not have access to this dashboard. Contact a project administrator.', 'emanager' ),
				esc_html__( 'Access restricted', 'emanager' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Process login / registration POSTs (nonces verified here, before dispatch).
	 */
	public function handle_forms() {
		if ( isset( $_POST['em_login_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['em_login_nonce'] ), 'em_login' ) ) {
			$this->process_login();
		}
		if ( isset( $_POST['em_register_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['em_register_nonce'] ), 'em_register' ) ) {
			$this->process_register();
		}
	}

	/**
	 * Sign the user in. Nonce already verified in handle_forms().
	 */
	private function process_login() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in handle_forms() before this is called.
		$creds = array(
			'user_login'    => sanitize_user( wp_unslash( $_POST['em_email'] ?? '' ) ),
			'user_password' => $_POST['em_password'] ?? '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- passwords must not be altered.
			'remember'      => ! empty( $_POST['em_remember'] ),
		);
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$user = wp_signon( $creds, is_ssl() );
		if ( is_wp_error( $user ) ) {
			$this->redirect_with( 'login_failed' );
		}
		wp_safe_redirect( get_permalink( (int) get_option( 'em_page_dashboard' ) ) );
		exit;
	}

	/**
	 * Register a new account (least-privilege em_viewer role).
	 * Nonce already verified in handle_forms().
	 */
	private function process_register() {
		if ( ! get_option( 'users_can_register' ) && ! apply_filters( 'em_allow_registration', true ) ) {
			$this->redirect_with( 'registration_closed' );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in handle_forms() before this is called.
		$email   = sanitize_email( wp_unslash( $_POST['em_email'] ?? '' ) );
		$name    = sanitize_text_field( wp_unslash( $_POST['em_name'] ?? '' ) );
		$company = sanitize_text_field( wp_unslash( $_POST['em_company'] ?? '' ) );
		$pass    = $_POST['em_password'] ?? ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- passwords must not be altered.
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! is_email( $email ) || strlen( $pass ) < 10 ) {
			$this->redirect_with( 'invalid_input' );
		}
		if ( email_exists( $email ) ) {
			$this->redirect_with( 'email_exists' );
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $email,
				'user_email'   => $email,
				'user_pass'    => $pass,
				'display_name' => $name ? $name : $email,
				'role'         => 'em_viewer', // Least privilege; admin upgrades later.
			)
		);
		if ( is_wp_error( $user_id ) ) {
			$this->redirect_with( 'registration_failed' );
		}

		if ( $company ) {
			update_user_meta( $user_id, 'em_company_name', $company );
		}

		wp_new_user_notification( $user_id, null, 'both' );
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, false, is_ssl() );
		wp_safe_redirect( get_permalink( (int) get_option( 'em_page_dashboard' ) ) );
		exit;
	}

	/**
	 * Redirect back to the referring page with a message code.
	 *
	 * @param string $code Message code for the banner.
	 */
	private function redirect_with( $code ) {
		$referer = wp_get_referer();
		wp_safe_redirect( add_query_arg( 'em_msg', $code, $referer ? $referer : home_url() ) );
		exit;
	}

	/**
	 * Render a partial template with {{placeholder}} replacement.
	 *
	 * @param string $file Partial file name inside public/partials/.
	 * @param array  $vars Placeholder => replacement map.
	 * @return string
	 */
	private function render_partial( $file, $vars = array() ) {
		$path = EM_DIR . 'public/partials/' . $file;
		if ( ! file_exists( $path ) ) {
			return '';
		}
		$html = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin template, not a remote URL.
		foreach ( $vars as $key => $value ) {
			$html = str_replace( '{{' . $key . '}}', $value, $html );
		}
		return $html;
	}

	/**
	 * Error banner for the ?em_msg= query arg.
	 *
	 * @return string Alert HTML or empty string.
	 */
	private function message_banner() {
		$messages = array(
			'login_failed'        => __( 'Invalid email or password.', 'emanager' ),
			'invalid_input'       => __( 'Enter a valid email and a password of at least 10 characters.', 'emanager' ),
			'email_exists'        => __( 'An account with this email already exists.', 'emanager' ),
			'registration_failed' => __( 'Registration failed. Please try again.', 'emanager' ),
			'registration_closed' => __( 'Registration is currently closed.', 'emanager' ),
		);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display message.
		$code = isset( $_GET['em_msg'] ) ? sanitize_key( $_GET['em_msg'] ) : '';
		if ( $code && isset( $messages[ $code ] ) ) {
			return '<div class="alert alert-danger" role="alert">' . esc_html( $messages[ $code ] ) . '</div>';
		}
		return '';
	}

	/**
	 * Render the [emanager_login] shortcode.
	 *
	 * @return string
	 */
	public function login_form() {
		if ( is_user_logged_in() ) {
			return '<div class="alert alert-info">' . esc_html__( 'You are already signed in.', 'emanager' ) .
				' <a href="' . esc_url( get_permalink( (int) get_option( 'em_page_dashboard' ) ) ) . '">' . esc_html__( 'Open dashboard', 'emanager' ) . '</a></div>';
		}
		EM_Public::instance()->enqueue_auth_assets();
		return $this->render_partial(
			'login.html',
			array(
				'banner'       => $this->message_banner(),
				'nonce'        => wp_nonce_field( 'em_login', 'em_login_nonce', true, false ),
				'register_url' => esc_url( get_permalink( (int) get_option( 'em_page_register' ) ) ),
				'lost_url'     => esc_url( wp_lostpassword_url() ),
			)
		);
	}

	/**
	 * Render the [emanager_register] shortcode.
	 *
	 * @return string
	 */
	public function register_form() {
		if ( is_user_logged_in() ) {
			return '<div class="alert alert-info">' . esc_html__( 'You are already signed in.', 'emanager' ) . '</div>';
		}
		EM_Public::instance()->enqueue_auth_assets();
		return $this->render_partial(
			'register.html',
			array(
				'banner'    => $this->message_banner(),
				'nonce'     => wp_nonce_field( 'em_register', 'em_register_nonce', true, false ),
				'login_url' => esc_url( get_permalink( (int) get_option( 'em_page_login' ) ) ),
			)
		);
	}
}
