<?php
/**
 * WP-Admin settings screens.
 *
 * Sections: Project information · User management · Company management ·
 * Modules (ZIP install). Data lives in custom WordPress tables (see EM_DB).
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin menu, settings registration and admin-post handlers.
 */
class EM_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var EM_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return EM_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook admin screens and handlers.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_em_save_company', array( $this, 'save_company' ) );
		add_action( 'admin_post_em_delete_company', array( $this, 'delete_company' ) );
		add_action( 'admin_post_em_save_user', array( $this, 'save_user' ) );
		add_action( 'admin_post_em_install_module', array( $this, 'install_module' ) );
		add_action( 'admin_post_em_rebuild_tables', array( $this, 'rebuild_tables' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		// "Company" column on WP user screens.
		add_filter( 'manage_users_columns', array( $this, 'user_columns' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'user_column_value' ), 10, 3 );
	}

	/**
	 * Enqueue admin assets on eManager screens only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function assets( $hook ) {
		if ( strpos( $hook, 'emanager' ) === false ) {
			return;
		}
		wp_enqueue_style( 'em-admin', EM_URL . 'admin/css/em-admin.css', array(), EM_VERSION );
		wp_enqueue_script( 'em-admin', EM_URL . 'admin/js/em-admin.js', array(), EM_VERSION, true );
		wp_localize_script(
			'em-admin',
			'EM_ADMIN',
			array(
				'apiRoot' => esc_url_raw( rest_url( 'em/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Register the eManager admin menu and submenus.
	 */
	public function menu() {
		add_menu_page(
			__( 'eManager', 'emanager' ),
			__( 'eManager', 'emanager' ),
			'em_manage',
			'emanager',
			array( $this, 'render_settings' ),
			'dashicons-building',
			3
		);
		add_submenu_page( 'emanager', __( 'Settings', 'emanager' ), __( 'Settings', 'emanager' ), 'em_manage', 'emanager', array( $this, 'render_settings' ) );
		add_submenu_page( 'emanager', __( 'Users', 'emanager' ), __( 'Users', 'emanager' ), 'em_manage', 'emanager-users', array( $this, 'render_users' ) );
		add_submenu_page( 'emanager', __( 'Companies', 'emanager' ), __( 'Companies', 'emanager' ), 'em_manage', 'emanager-companies', array( $this, 'render_companies' ) );
		add_submenu_page( 'emanager', __( 'Modules', 'emanager' ), __( 'Modules', 'emanager' ), 'em_manage', 'emanager-modules', array( $this, 'render_modules' ) );
	}

	/**
	 * Register plugin options (project information).
	 */
	public function register_settings() {
		register_setting(
			'em_settings',
			'em_project',
			array(
				'type'              => 'array',
				'sanitize_callback' => function ( $value ) {
					$value = (array) $value;
					return array(
						'id'        => sanitize_key( $value['id'] ?? 'default' ),
						'name'      => sanitize_text_field( $value['name'] ?? '' ),
						'number'    => sanitize_text_field( $value['number'] ?? '' ),
						'address'   => sanitize_text_field( $value['address'] ?? '' ),
						'lat'       => (float) ( $value['lat'] ?? 0 ),
						'lon'       => (float) ( $value['lon'] ?? 0 ),
						'start'     => sanitize_text_field( $value['start'] ?? '' ),
						'finish'    => sanitize_text_field( $value['finish'] ?? '' ),
						'owner'     => sanitize_text_field( $value['owner'] ?? '' ),
						'architect' => sanitize_text_field( $value['architect'] ?? '' ),
					);
				},
			)
		);
		register_setting(
			'em_settings',
			'em_notifications',
			array(
				'type'              => 'string',
				'default'           => '1',
				'sanitize_callback' => fn( $v ) => $v ? '1' : '0',
			)
		);
	}

	/**
	 * Render the Settings screen.
	 */
	public function render_settings() {
		require EM_DIR . 'admin/views/settings.php';
	}

	/**
	 * Render the Users screen.
	 */
	public function render_users() {
		require EM_DIR . 'admin/views/users.php';
	}

	/**
	 * Render the Companies screen.
	 */
	public function render_companies() {
		require EM_DIR . 'admin/views/companies.php';
	}

	/**
	 * Render the Modules screen.
	 */
	public function render_modules() {
		require EM_DIR . 'admin/views/modules.php';
	}

	// ------------------------------------------------------------------
	// Companies (stored in the em_companies table)
	// ------------------------------------------------------------------

	/**
	 * Create or update a company (admin-post handler).
	 */
	public function save_company() {
		check_admin_referer( 'em_company' );
		if ( ! current_user_can( 'em_manage' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'emanager' ) );
		}

		$data = array(
			'name'    => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'type'    => sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) ),
			'phone'   => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'email'   => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'address' => sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) ),
		);
		$id   = sanitize_text_field( wp_unslash( $_POST['company_id'] ?? '' ) );

		$result = $id
			? EM_DB::update( 'em_companies', $id, $data )
			: EM_DB::insert( 'em_companies', $data );

		$msg = is_wp_error( $result ) ? 'error' : 'saved';
		wp_safe_redirect( admin_url( 'admin.php?page=emanager-companies&em_msg=' . $msg ) );
		exit;
	}

	/**
	 * Delete a company (admin-post handler).
	 */
	public function delete_company() {
		check_admin_referer( 'em_company_delete' );
		if ( ! current_user_can( 'em_manage' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'emanager' ) );
		}
		$id = sanitize_text_field( wp_unslash( $_GET['company_id'] ?? '' ) );
		if ( $id ) {
			EM_DB::delete( 'em_companies', $id );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=emanager-companies&em_msg=deleted' ) );
		exit;
	}

	// ------------------------------------------------------------------
	// Users: assign eManager role and company.
	// ------------------------------------------------------------------

	/**
	 * Save a user's eManager role and company (admin-post handler).
	 */
	public function save_user() {
		check_admin_referer( 'em_user' );
		if ( ! current_user_can( 'em_manage' ) || ! current_user_can( 'edit_users' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'emanager' ) );
		}

		$user_id = absint( wp_unslash( $_POST['user_id'] ?? 0 ) );
		$role    = sanitize_key( $_POST['em_role'] ?? '' );
		$company = sanitize_text_field( wp_unslash( $_POST['em_company_id'] ?? '' ) );
		$party   = sanitize_key( $_POST['em_party_role'] ?? '' );

		$allowed = array( 'em_administrator', 'em_editor', 'em_contributor', 'em_viewer', 'em_restricted' );
		$user    = get_user_by( 'id', $user_id );

		if ( $user && in_array( $role, $allowed, true ) && ! user_can( $user, 'manage_options' ) ) {
			// Replace any existing eManager role; keep non-eManager roles intact.
			foreach ( $allowed as $r ) {
				$user->remove_role( $r );
			}
			$user->add_role( $role );
		}
		if ( $user ) {
			update_user_meta( $user_id, 'em_company_id', $company );
			update_user_meta( $user_id, 'em_party_role', in_array( $party, EM_Roles::PARTY_ROLES, true ) ? $party : '' );
			update_user_meta( $user_id, 'em_email_notify', isset( $_POST['em_email_notify'] ) ? '1' : '0' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=emanager-users&em_msg=saved' ) );
		exit;
	}

	/**
	 * Add the eManager Company column to the WP users table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function user_columns( $columns ) {
		$columns['em_company'] = __( 'eManager Company', 'emanager' );
		return $columns;
	}

	/**
	 * Render the eManager Company column value.
	 *
	 * @param string $value   Current value.
	 * @param string $column  Column key.
	 * @param int    $user_id User id.
	 * @return string
	 */
	public function user_column_value( $value, $column, $user_id ) {
		if ( 'em_company' === $column ) {
			$id = get_user_meta( $user_id, 'em_company_id', true );
			return $id ? esc_html( self::company_name( $id ) ) : '—';
		}
		return $value;
	}

	/**
	 * Cached company list.
	 *
	 * @return array
	 */
	public static function companies() {
		$cached = get_transient( 'em_companies_cache' );
		if ( false !== $cached ) {
			return $cached;
		}
		$result    = EM_DB::select(
			'em_companies',
			array(
				'sort'     => 'name',
				'order'    => 'asc',
				'per_page' => 500,
			)
		);
		$companies = array();
		if ( ! is_wp_error( $result ) && ! empty( $result['data'] ) ) {
			$companies = $result['data'];
		}
		set_transient( 'em_companies_cache', $companies, 5 * MINUTE_IN_SECONDS );
		return $companies;
	}

	/**
	 * Resolve a company name by id.
	 *
	 * @param string $id Company id.
	 * @return string
	 */
	public static function company_name( $id ) {
		foreach ( self::companies() as $company ) {
			if ( (string) $company['id'] === (string) $id ) {
				return $company['name'];
			}
		}
		return $id;
	}

	// ------------------------------------------------------------------
	// Module install (admin form fallback to the REST route).
	// ------------------------------------------------------------------

	/**
	 * Install a module ZIP uploaded through the admin form.
	 */
	public function install_module() {
		check_admin_referer( 'em_install_module' );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- file uploads cannot be text-sanitized; validated by wp_check_filetype_and_ext() and a strict extension whitelist in EM_Installer.
		$result = EM_Installer::install_from_upload( $_FILES['module'] ?? array() );
		$msg    = is_wp_error( $result ) ? 'error&detail=' . rawurlencode( $result->get_error_message() ) : 'installed';
		wp_safe_redirect( admin_url( 'admin.php?page=emanager-modules&em_msg=' . $msg ) );
		exit;
	}

	/**
	 * Re-run schema creation for shared tables and every module (admin-post).
	 * Safe to run anytime — dbDelta only applies differences.
	 */
	public function rebuild_tables() {
		check_admin_referer( 'em_rebuild_tables' );
		if ( ! current_user_can( 'em_manage' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'emanager' ) );
		}
		EM_Modules::flush_cache();
		EM_DB::install_tables();
		wp_safe_redirect( admin_url( 'admin.php?page=emanager&em_msg=tables_rebuilt' ) );
		exit;
	}
}
