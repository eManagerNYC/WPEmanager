<?php
/**
 * ZIP module installer.
 *
 * A module ZIP must contain a single top-level folder with a module.json:
 *
 *   my-module.zip
 *     └── my-module/
 *         ├── module.json
 *         ├── module.js      (optional)
 *         └── ...
 *
 * Installed into wp-content/uploads/emanager-modules/<section>/<id>/.
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Installs and removes ZIP-packaged modules. PHP files are rejected.
 */
class EM_Installer {

	const ALLOWED_EXTENSIONS = array( 'json', 'html', 'js', 'css', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'md', 'sql' );

	/**
	 * Install a module from an uploaded ZIP file.
	 *
	 * @param array $file Entry from $_FILES.
	 * @return array|WP_Error Installed module definition or error.
	 */
	public static function install_from_upload( $file ) {
		if ( ! current_user_can( 'em_manage' ) ) {
			return new WP_Error( 'em_forbidden', __( 'You are not allowed to install modules.', 'emanager' ), array( 'status' => 403 ) );
		}
		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'em_bad_upload', __( 'No file uploaded.', 'emanager' ), array( 'status' => 400 ) );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'zip' => 'application/zip' ) );
		if ( 'zip' !== $check['ext'] ) {
			return new WP_Error( 'em_bad_type', __( 'Module packages must be ZIP files.', 'emanager' ), array( 'status' => 400 ) );
		}

		// Unzip into a temp dir using WP_Filesystem.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$tmp = trailingslashit( get_temp_dir() ) . 'em-module-' . wp_generate_password( 8, false );
		wp_mkdir_p( $tmp );

		$result = unzip_file( $file['tmp_name'], $tmp );
		if ( is_wp_error( $result ) ) {
			$wp_filesystem->rmdir( $tmp, true );
			return $result;
		}

		// Locate module.json (top folder or zip root).
		$manifest = self::find_manifest( $tmp );
		if ( ! $manifest ) {
			$wp_filesystem->rmdir( $tmp, true );
			return new WP_Error( 'em_no_manifest', __( 'The ZIP does not contain a module.json manifest.', 'emanager' ), array( 'status' => 400 ) );
		}

		$def   = json_decode( file_get_contents( $manifest ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local temp file just extracted.
		$valid = self::validate_manifest( $def );
		if ( is_wp_error( $valid ) ) {
			$wp_filesystem->rmdir( $tmp, true );
			return $valid;
		}

		// Reject dangerous file types (no PHP execution from uploads).
		$source = trailingslashit( dirname( $manifest ) );
		$bad    = self::scan_for_disallowed( $source );
		if ( $bad ) {
			$wp_filesystem->rmdir( $tmp, true );
			/* translators: %s: file name */
			return new WP_Error( 'em_bad_file', sprintf( __( 'Disallowed file type in package: %s', 'emanager' ), $bad ), array( 'status' => 400 ) );
		}

		$section = sanitize_key( $def['section'] );
		$id      = sanitize_key( $def['id'] );
		$dest    = EM_CUSTOM_MODULES_DIR . $section . '/' . $id . '/';

		wp_mkdir_p( $dest );
		copy_dir( $source, $dest );
		$wp_filesystem->rmdir( $tmp, true );

		// Create the module's database table (no-op-safe if it already exists).
		if ( empty( $def['virtual'] ) ) {
			$def['table'] = 'em_' . str_replace( '-', '_', $id );
			EM_DB::create_module_table( $def );
		}

		// New module on disk — drop the scanned-registry cache so it appears.
		EM_Modules::flush_cache();

		$def['installed'] = true;
		return $def;
	}

	/**
	 * Uninstall a custom module (built-in modules cannot be removed here).
	 *
	 * @param string $id Module id.
	 * @return true|WP_Error
	 */
	public static function uninstall( $id ) {
		if ( ! current_user_can( 'em_manage' ) ) {
			return new WP_Error( 'em_forbidden', __( 'Not allowed.', 'emanager' ), array( 'status' => 403 ) );
		}
		$module = EM_Modules::instance()->get( sanitize_key( $id ) );
		if ( ! $module ) {
			return new WP_Error( 'em_not_found', __( 'Module not found.', 'emanager' ), array( 'status' => 404 ) );
		}
		if ( strpos( $module['path'], EM_CUSTOM_MODULES_DIR ) !== 0 ) {
			return new WP_Error( 'em_builtin', __( 'Built-in modules cannot be uninstalled.', 'emanager' ), array( 'status' => 400 ) );
		}

		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$wp_filesystem->rmdir( untrailingslashit( $module['path'] ), true );
		EM_Modules::flush_cache();
		return true;
	}

	/**
	 * Find module.json at root or one level deep.
	 *
	 * @param string $dir Extracted temp directory.
	 * @return string|null Manifest path or null.
	 */
	private static function find_manifest( $dir ) {
		if ( file_exists( $dir . '/module.json' ) ) {
			return $dir . '/module.json';
		}
		$candidates = glob( $dir . '/*/module.json' );
		return $candidates ? $candidates[0] : null;
	}

	/**
	 * Validate required manifest keys.
	 *
	 * @param mixed $def Decoded module.json.
	 * @return true|WP_Error
	 */
	private static function validate_manifest( $def ) {
		if ( ! is_array( $def ) ) {
			return new WP_Error( 'em_bad_manifest', __( 'module.json is not valid JSON.', 'emanager' ), array( 'status' => 400 ) );
		}
		foreach ( array( 'id', 'name', 'section', 'table', 'fields' ) as $key ) {
			if ( empty( $def[ $key ] ) ) {
				/* translators: %s: manifest key */
				return new WP_Error( 'em_bad_manifest', sprintf( __( 'module.json is missing required key: %s', 'emanager' ), $key ), array( 'status' => 400 ) );
			}
		}
		if ( EM_Modules::instance()->get( sanitize_key( $def['id'] ) ) && empty( $def['version'] ) ) {
			return new WP_Error( 'em_exists', __( 'A module with this id already exists. Add a "version" to upgrade it.', 'emanager' ), array( 'status' => 409 ) );
		}
		return true;
	}

	/**
	 * Return the first disallowed file found, or null.
	 *
	 * @param string $dir Directory to scan recursively.
	 * @return string|null Offending file name or null.
	 */
	private static function scan_for_disallowed( $dir ) {
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $iterator as $file ) {
			$ext = strtolower( pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) );
			if ( ! in_array( $ext, self::ALLOWED_EXTENSIONS, true ) ) {
				return $file->getFilename();
			}
		}
		return null;
	}
}
