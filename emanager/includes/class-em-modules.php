<?php
/**
 * Module registry.
 *
 * A module is a folder containing module.json plus optional overrides:
 *
 *   modules/<section>/<module-id>/
 *     module.json   (required — id, name, section, icon, table, statuses, fields)
 *     list.html     (optional — overrides modules/_defaults/list.html)
 *     view.html     (optional)
 *     form.html     (optional)
 *     module.js     (optional — loaded on demand when the module is opened)
 *     module.css    (optional)
 *
 * Custom modules installed via ZIP live in wp-content/uploads/emanager-modules/
 * and survive plugin updates.
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Scans, registers and exposes module definitions.
 */
class EM_Modules {

	/**
	 * Singleton instance.
	 *
	 * @var EM_Modules|null
	 */
	private static $instance = null;

	/**
	 * Registered modules.
	 *
	 * @var array<string,array> Module id => definition.
	 */
	private $modules = array();

	/**
	 * Registered sections.
	 *
	 * @var array<string,array> Section id => { id, name, icon, order }.
	 */
	private $sections = array();

	/**
	 * Get the singleton instance.
	 *
	 * @return EM_Modules
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cache key for the scanned-from-disk registry.
	 */
	const CACHE_KEY = 'em_modules_cache';

	/**
	 * Scan built-in and custom module directories.
	 *
	 * Scanning means reading and JSON-decoding 100+ module.json files, so the
	 * raw result is cached in a transient (keyed to the plugin version) and only
	 * re-read when the cache is empty or a module is installed/uninstalled.
	 * Code-registered modules/sections still run through the filters every
	 * request, so dynamic registration is unaffected by the cache.
	 */
	private function __construct() {
		$cached = get_transient( self::CACHE_KEY );
		if ( ! apply_filters( 'em_disable_module_cache', false )
			&& is_array( $cached )
			&& isset( $cached['version'] ) && EM_VERSION === $cached['version'] ) {
			$this->sections = $cached['sections'];
			$this->modules  = $cached['modules'];
		} else {
			$this->load_sections();
			$this->scan( EM_MODULES_DIR, EM_URL . 'modules/' );
			if ( is_dir( EM_CUSTOM_MODULES_DIR ) ) {
				$this->scan( EM_CUSTOM_MODULES_DIR, EM_CUSTOM_MODULES_URL );
			}
			set_transient(
				self::CACHE_KEY,
				array(
					'version'  => EM_VERSION,
					'sections' => $this->sections,
					'modules'  => $this->modules,
				),
				DAY_IN_SECONDS
			);
		}

		/**
		 * Filter the registered modules (allows code-registered modules too).
		 *
		 * @param array $modules id => definition.
		 */
		$this->modules = apply_filters( 'em_modules', $this->modules );

		/**
		 * Filter the registered sections.
		 *
		 * @param array $sections id => section.
		 */
		$this->sections = apply_filters( 'em_sections', $this->sections );
	}

	/**
	 * Clear the scanned-registry cache (called on module install/uninstall and
	 * plugin activation).
	 */
	public static function flush_cache() {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Load sections from sections.json (raw; filters applied in the constructor).
	 */
	private function load_sections() {
		$file = EM_MODULES_DIR . 'sections.json';
		if ( file_exists( $file ) ) {
			$json = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
			if ( is_array( $json ) ) {
				foreach ( $json as $i => $section ) {
					$section['order']                 = $i;
					$this->sections[ $section['id'] ] = $section;
				}
			}
		}
	}

	/**
	 * Scan a base directory for section/module folders.
	 *
	 * @param string $base_dir Absolute directory to scan.
	 * @param string $base_url Public URL corresponding to $base_dir.
	 */
	private function scan( $base_dir, $base_url ) {
		$manifests = glob( $base_dir . '*/*/module.json' );
		if ( ! $manifests ) {
			return;
		}
		foreach ( $manifests as $file ) {
			$def = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
			if ( ! is_array( $def ) || empty( $def['id'] ) || empty( $def['table'] ) ) {
				continue;
			}
			$dir = trailingslashit( dirname( $file ) );
			$rel = str_replace( $base_dir, '', $dir );

			$def['path'] = $dir;
			$def['url']  = $base_url . $rel;

			// Resolve which templates / assets exist so the front-end can lazy-load them.
			$def['assets'] = array();
			foreach ( array( 'list.html', 'view.html', 'form.html', 'module.js', 'module.css' ) as $asset ) {
				if ( file_exists( $dir . $asset ) ) {
					$def['assets'][ $asset ] = $def['url'] . $asset;
				}
			}

			$this->modules[ $def['id'] ] = $def;
		}
	}

	/**
	 * Get one module definition.
	 *
	 * @param string $id Module id.
	 * @return array|null
	 */
	public function get( $id ) {
		return $this->modules[ $id ] ?? null;
	}

	/**
	 * All registered modules.
	 *
	 * @return array<string,array>
	 */
	public function all() {
		return $this->modules;
	}

	/**
	 * Registry payload sent to the browser: sections with their modules,
	 * sorted by section order — server paths stripped.
	 *
	 * @return array
	 */
	public function registry() {
		$out = array();
		foreach ( $this->sections as $section ) {
			$out[ $section['id'] ] = array(
				'id'      => $section['id'],
				'name'    => $section['name'],
				'icon'    => $section['icon'] ?? 'bi-folder',
				'modules' => array(),
			);
		}
		foreach ( $this->modules as $module ) {
			$section = $module['section'] ?? 'other';
			if ( ! isset( $out[ $section ] ) ) {
				$out[ $section ] = array(
					'id'      => $section,
					'name'    => ucwords( str_replace( '-', ' ', $section ) ),
					'icon'    => 'bi-folder',
					'modules' => array(),
				);
			}
			$public = $module;
			unset( $public['path'] ); // Never leak filesystem paths.
			$out[ $section ]['modules'][] = $public;
		}
		// Drop empty sections, keep order.
		return array_values( array_filter( $out, fn( $s ) => ! empty( $s['modules'] ) ) );
	}

	/**
	 * Validate a sort/filter column against the module's field list.
	 *
	 * @param array  $module Module definition.
	 * @param string $column Column name.
	 * @return bool
	 */
	public static function has_column( $module, $column ) {
		$known = array( 'id', 'status', 'direction', 'created_at', 'updated_at', 'created_by', 'created_by_name', 'company_id', 'project_id', 'linked_module', 'linked_id' );
		foreach ( (array) ( $module['fields'] ?? array() ) as $field ) {
			$known[] = $field['name'];
		}
		return in_array( $column, $known, true );
	}

	/**
	 * Text columns for free-text search.
	 *
	 * @param array $module Module definition.
	 * @return array Column names.
	 */
	public static function search_columns( $module ) {
		$cols = array();
		foreach ( (array) ( $module['fields'] ?? array() ) as $field ) {
			if ( in_array( $field['type'] ?? 'text', array( 'text', 'textarea', 'email' ), true ) ) {
				$cols[] = $field['name'];
			}
		}
		return $cols ? $cols : array( 'status' );
	}
}
