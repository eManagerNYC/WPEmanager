<?php
/**
 * Data layer — native WordPress database ($wpdb).
 *
 * Each module gets its own MySQL table ({$wpdb->prefix}em_<module>), created on
 * activation (and when a module ZIP is installed) via dbDelta. Tables carry the
 * indexes needed to stay fast at tens of thousands of rows per module.
 *
 * This class is the single choke point for all SQL: identifiers are whitelisted
 * to [a-z0-9_], every value is bound through $wpdb->prepare(), and the public
 * list endpoint validates sort/filter columns against the module field list
 * before they ever reach here.
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Custom-table query builder and schema installer.
 */
class EM_DB {

	/**
	 * Object-cache group for single-row reads.
	 */
	const CACHE_GROUP = 'emanager';

	/**
	 * Whitelist an identifier (table or column) to safe characters.
	 *
	 * @param string $ident Raw identifier.
	 * @return string
	 */
	public static function safe_ident( $ident ) {
		return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $ident ) );
	}

	/**
	 * Resolve a logical table name (e.g. "em_rfis") to its physical, prefixed
	 * name (e.g. "wp_em_rfis"). Always force an "em_" prefix so this class can
	 * never be pointed at a core WordPress table.
	 *
	 * @param string $table Logical table name.
	 * @return string Physical table name.
	 */
	public static function real( $table ) {
		global $wpdb;
		$table = self::safe_ident( $table );
		if ( 0 !== strpos( $table, 'em_' ) ) {
			$table = 'em_' . $table;
		}
		return $wpdb->prefix . $table;
	}

	/**
	 * Always available — kept for API parity with the old remote backend.
	 *
	 * @return bool
	 */
	public static function is_configured() {
		return true;
	}

	/**
	 * Select rows with optional filters, free-text search, sort and pagination.
	 *
	 * @param string $table  Logical table name (validated by the caller).
	 * @param array  $params {
	 *     Query options.
	 *
	 *     @type array  $filters     Column => value (exact match).
	 *     @type string $search      Free-text term applied to $search_cols.
	 *     @type array  $search_cols Columns to LIKE-search.
	 *     @type string $sort        Column name.
	 *     @type string $order       Sort direction, asc|desc.
	 *     @type int    $page        1-based page.
	 *     @type int    $per_page    Rows per page.
	 * }
	 * @return array|WP_Error { data: rows[], total: int } or error.
	 */
	public static function select( $table, $params = array() ) {
		global $wpdb;
		$physical = self::real( $table );

		$where = array();
		$args  = array();

		foreach ( (array) ( $params['filters'] ?? array() ) as $col => $val ) {
			if ( '' === $val || null === $val || array() === $val ) {
				continue;
			}
			$col = self::safe_ident( $col );
			if ( '' === $col ) {
				continue;
			}
			if ( is_array( $val ) ) {
				// IN (...) — one placeholder per value.
				$place   = implode( ', ', array_fill( 0, count( $val ), '%s' ) );
				$where[] = "`{$col}` IN ( {$place} )";
				foreach ( $val as $v ) {
					$args[] = $v;
				}
			} else {
				$where[] = "`{$col}` = %s";
				$args[]  = $val;
			}
		}

		if ( ! empty( $params['search'] ) && ! empty( $params['search_cols'] ) ) {
			$like = '%' . $wpdb->esc_like( $params['search'] ) . '%';
			$ors  = array();
			foreach ( (array) $params['search_cols'] as $col ) {
				$col = self::safe_ident( $col );
				if ( '' === $col ) {
					continue;
				}
				$ors[]  = "`{$col}` LIKE %s";
				$args[] = $like;
			}
			if ( $ors ) {
				$where[] = '( ' . implode( ' OR ', $ors ) . ' )';
			}
		}

		$where_sql = $where ? ' WHERE ' . implode( ' AND ', $where ) : '';

		$sort = self::safe_ident( $params['sort'] ?? 'created_at' );
		if ( '' === $sort ) {
			$sort = 'created_at';
		}
		$order = ( 'asc' === strtolower( $params['order'] ?? 'desc' ) ) ? 'ASC' : 'DESC';

		$per_page = max( 1, min( 500, (int) ( $params['per_page'] ?? 25 ) ) );
		$page     = max( 1, (int) ( $params['page'] ?? 1 ) );
		$offset   = ( $page - 1 ) * $per_page;

		// Total (uses the same WHERE; indexed columns keep this cheap at scale).
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- $physical/$where are built from whitelisted identifiers; values are bound below.
		$count_sql = "SELECT COUNT(*) FROM `{$physical}`{$where_sql}";
		if ( $args ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- placeholders are in $count_sql; values in $args; custom table.
			$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $args ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- no user values to bind.
			$total = (int) $wpdb->get_var( $count_sql );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- identifiers whitelisted; values bound via prepare().
		$sql       = "SELECT * FROM `{$physical}`{$where_sql} ORDER BY `{$sort}` {$order} LIMIT %d OFFSET %d";
		$page_args = array_merge( $args, array( $per_page, $offset ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- placeholders in $sql; values in $page_args.
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $page_args ), ARRAY_A );

		if ( $wpdb->last_error ) {
			return new WP_Error( 'em_db_error', $wpdb->last_error, array( 'status' => 500 ) );
		}

		return array(
			'data'  => $rows ? $rows : array(),
			'total' => $total,
		);
	}

	/**
	 * Fetch a single row by id.
	 *
	 * @param string $table Logical table name.
	 * @param int    $id    Row id.
	 * @return array|WP_Error
	 */
	public static function get( $table, $id ) {
		global $wpdb;
		$id        = (int) $id;
		$cache_key = self::safe_ident( $table ) . '_' . $id;

		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$physical = self::real( $table );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom plugin table; table name is a whitelisted identifier, id bound via prepare(), result cached below (NoCaching satisfied).
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$physical}` WHERE id = %d", $id ), ARRAY_A );
		if ( $wpdb->last_error ) {
			return new WP_Error( 'em_db_error', $wpdb->last_error, array( 'status' => 500 ) );
		}
		if ( ! $row ) {
			return new WP_Error( 'em_not_found', __( 'Record not found.', 'emanager' ), array( 'status' => 404 ) );
		}
		wp_cache_set( $cache_key, $row, self::CACHE_GROUP, HOUR_IN_SECONDS );
		return $row;
	}

	/**
	 * Invalidate a cached single row after a write.
	 *
	 * @param string $table Logical table name.
	 * @param int    $id    Row id.
	 */
	private static function flush_row( $table, $id ) {
		wp_cache_delete( self::safe_ident( $table ) . '_' . (int) $id, self::CACHE_GROUP );
	}

	/**
	 * Insert a row and return it.
	 *
	 * @param string $table Logical table name.
	 * @param array  $data  Column => value map.
	 * @return array|WP_Error The inserted row.
	 */
	public static function insert( $table, $data ) {
		global $wpdb;
		$physical = self::real( $table );
		$clean    = self::clean_columns( $data );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table; $wpdb->insert escapes values.
		$ok = $wpdb->insert( $physical, $clean );
		if ( false === $ok ) {
			return new WP_Error( 'em_db_error', $wpdb->last_error ? $wpdb->last_error : __( 'Insert failed.', 'emanager' ), array( 'status' => 500 ) );
		}
		return self::get( $table, $wpdb->insert_id );
	}

	/**
	 * Update a row by id and return it.
	 *
	 * @param string $table Logical table name.
	 * @param int    $id    Row id.
	 * @param array  $data  Column => value map.
	 * @return array|WP_Error The updated row.
	 */
	public static function update( $table, $id, $data ) {
		global $wpdb;
		$physical = self::real( $table );
		$clean    = self::clean_columns( $data );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table; $wpdb->update escapes values; cache invalidated below.
		$ok = $wpdb->update( $physical, $clean, array( 'id' => (int) $id ) );
		if ( false === $ok ) {
			return new WP_Error( 'em_db_error', $wpdb->last_error ? $wpdb->last_error : __( 'Update failed.', 'emanager' ), array( 'status' => 500 ) );
		}
		self::flush_row( $table, $id );
		return self::get( $table, $id );
	}

	/**
	 * Delete a row by id.
	 *
	 * @param string $table Logical table name.
	 * @param int    $id    Row id.
	 * @return array|WP_Error
	 */
	public static function delete( $table, $id ) {
		global $wpdb;
		$physical = self::real( $table );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table; id is cast to int; cache invalidated below.
		$ok = $wpdb->delete( $physical, array( 'id' => (int) $id ) );
		if ( false === $ok ) {
			return new WP_Error( 'em_db_error', $wpdb->last_error ? $wpdb->last_error : __( 'Delete failed.', 'emanager' ), array( 'status' => 500 ) );
		}
		self::flush_row( $table, $id );
		return array( 'data' => true );
	}

	/**
	 * Status -> count map for a table (one indexed GROUP BY, cheap at scale).
	 *
	 * @param string $table Logical table name.
	 * @return array<string,int>
	 */
	public static function status_counts( $table ) {
		global $wpdb;
		$physical = self::real( $table );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table name whitelisted; no user input.
		$rows = $wpdb->get_results( "SELECT status, COUNT(*) AS c FROM `{$physical}` GROUP BY status", ARRAY_A );
		$out  = array();
		foreach ( (array) $rows as $row ) {
			$out[ (string) $row['status'] ] = (int) $row['c'];
		}
		return $out;
	}

	/**
	 * SUM a numeric column across a table (indexed, cheap at scale). Used by the
	 * financial roll-up. The column is whitelisted; an unknown column yields 0.
	 *
	 * @param string $table  Logical table name.
	 * @param string $column Numeric column to total.
	 * @return float
	 */
	public static function sum_column( $table, $column ) {
		global $wpdb;
		$physical = self::real( $table );
		$column   = self::safe_ident( $column );
		if ( '' === $column ) {
			return 0.0;
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table + column are whitelisted identifiers; no user values.
		$sum = $wpdb->get_var( "SELECT COALESCE(SUM(`{$column}`),0) FROM `{$physical}`" );
		return (float) $sum;
	}

	/**
	 * Sanitize a data array's keys to safe column identifiers.
	 *
	 * @param array $data Raw column => value map.
	 * @return array
	 */
	private static function clean_columns( $data ) {
		$clean = array();
		foreach ( (array) $data as $col => $val ) {
			$col = self::safe_ident( $col );
			if ( '' !== $col ) {
				$clean[ $col ] = is_bool( $val ) ? (int) $val : $val;
			}
		}
		return $clean;
	}

	// ------------------------------------------------------------------
	// Schema installation (dbDelta).
	// ------------------------------------------------------------------

	/**
	 * Map an eManager field type to a MySQL column type.
	 *
	 * @param string $type Field type.
	 * @return string
	 */
	public static function column_type( $type ) {
		switch ( $type ) {
			case 'textarea':
			case 'richtext':
				return 'text';
			case 'json':
				return 'longtext';
			case 'signature':
				return 'mediumtext';
			case 'number':
			case 'currency':
				return 'decimal(20,4)';
			case 'date':
				return 'date';
			case 'datetime':
				return 'datetime';
			case 'checkbox':
				return 'tinyint(1)';
			default:
				return 'varchar(191)';
		}
	}

	/**
	 * Create or update all tables: shared tables + one per registered module.
	 * Safe to run repeatedly (dbDelta diffs the schema).
	 */
	public static function install_tables() {
		self::install_shared_tables();
		foreach ( EM_Modules::instance()->all() as $module ) {
			if ( empty( $module['virtual'] ) ) {
				self::create_module_table( $module );
			}
		}
	}

	/**
	 * Create the shared tables (companies, comments, activity log).
	 */
	public static function install_shared_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$prefix  = $wpdb->prefix;

		dbDelta(
			"CREATE TABLE {$prefix}em_companies (
				id bigint(20) unsigned NOT NULL auto_increment,
				name varchar(191) DEFAULT NULL,
				type varchar(191) DEFAULT NULL,
				phone varchar(191) DEFAULT NULL,
				email varchar(191) DEFAULT NULL,
				address text DEFAULT NULL,
				status varchar(191) DEFAULT NULL,
				created_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY name (name)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$prefix}em_comments (
				id bigint(20) unsigned NOT NULL auto_increment,
				module_id varchar(191) DEFAULT NULL,
				record_id bigint(20) unsigned DEFAULT NULL,
				body text DEFAULT NULL,
				author_id varchar(20) DEFAULT NULL,
				author_name varchar(191) DEFAULT NULL,
				created_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY record (module_id,record_id)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$prefix}em_activity (
				id bigint(20) unsigned NOT NULL auto_increment,
				module_id varchar(191) DEFAULT NULL,
				record_id bigint(20) unsigned DEFAULT NULL,
				actor_id varchar(20) DEFAULT NULL,
				actor_name varchar(191) DEFAULT NULL,
				action varchar(40) DEFAULT NULL,
				from_status varchar(191) DEFAULT NULL,
				to_status varchar(191) DEFAULT NULL,
				note text DEFAULT NULL,
				created_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY record (module_id,record_id)
			) {$charset};"
		);
	}

	/**
	 * Create or update one module's table from its field definitions.
	 *
	 * @param array $module Module definition (needs table + fields).
	 */
	public static function create_module_table( $module ) {
		global $wpdb;
		if ( empty( $module['table'] ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$physical = self::real( $module['table'] );
		$charset  = $wpdb->get_charset_collate();

		$lines = array(
			'id bigint(20) unsigned NOT NULL auto_increment',
			'project_id varchar(191) DEFAULT NULL',
			'company_id varchar(191) DEFAULT NULL',
			'status varchar(191) DEFAULT NULL',
			'direction varchar(32) DEFAULT NULL',
			'linked_module varchar(191) DEFAULT NULL',
			'linked_id bigint(20) unsigned DEFAULT NULL',
			'created_by varchar(20) DEFAULT NULL',
			'created_by_name varchar(191) DEFAULT NULL',
			'created_at datetime DEFAULT NULL',
			'updated_at datetime DEFAULT NULL',
		);

		// Per-field columns; index list/sortable columns that are indexable.
		$field_indexes = array();
		foreach ( (array) ( $module['fields'] ?? array() ) as $field ) {
			$name = self::safe_ident( $field['name'] ?? '' );
			if ( '' === $name || in_array( $name, array( 'id', 'status', 'direction', 'created_at', 'updated_at', 'created_by', 'created_by_name', 'company_id', 'project_id', 'linked_module', 'linked_id' ), true ) ) {
				continue;
			}
			$type    = self::column_type( $field['type'] ?? 'text' );
			$lines[] = "`{$name}` {$type} DEFAULT NULL";
			if ( ! empty( $field['list'] ) && ! in_array( $type, array( 'text', 'longtext', 'mediumtext' ), true ) ) {
				$field_indexes[] = "KEY `idx_{$name}` (`{$name}`)";
			}
		}

		$lines[] = 'PRIMARY KEY  (id)';
		$lines[] = 'KEY status (status)';
		$lines[] = 'KEY created_at (created_at)';
		$lines[] = 'KEY created_by (created_by)';
		$lines[] = 'KEY project_status (project_id,status)';
		$lines[] = 'KEY linked (linked_module,linked_id)';
		$lines   = array_merge( $lines, $field_indexes );

		$sql = "CREATE TABLE {$physical} (\n\t" . implode( ",\n\t", $lines ) . "\n) {$charset};";
		dbDelta( $sql );

		// dbDelta reliably CREATES tables but does not reliably ADD columns to an
		// existing table when field names are backticked. Sync columns explicitly
		// so plugin upgrades (and module updates) add any newly declared fields.
		self::sync_module_columns( $module );
	}

	/**
	 * Add any module fields missing from an existing table (upgrade-safe ALTER).
	 *
	 * @param array $module Module definition.
	 */
	public static function sync_module_columns( $module ) {
		global $wpdb;
		$physical = self::real( $module['table'] );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table name whitelisted; reading the table's own column list.
		$existing = $wpdb->get_col( "SHOW COLUMNS FROM `{$physical}`" );
		if ( empty( $existing ) ) {
			return;
		}
		$existing = array_map( 'strtolower', $existing );

		// Desired columns: common columns + per-field columns.
		$desired = array(
			'project_id'      => 'varchar(191)',
			'company_id'      => 'varchar(191)',
			'status'          => 'varchar(191)',
			'direction'       => 'varchar(32)',
			'linked_module'   => 'varchar(191)',
			'linked_id'       => 'bigint(20) unsigned',
			'created_by'      => 'varchar(20)',
			'created_by_name' => 'varchar(191)',
			'created_at'      => 'datetime',
			'updated_at'      => 'datetime',
		);
		foreach ( (array) ( $module['fields'] ?? array() ) as $field ) {
			$name = self::safe_ident( $field['name'] ?? '' );
			if ( '' !== $name && ! isset( $desired[ $name ] ) && 'id' !== $name ) {
				$desired[ $name ] = self::column_type( $field['type'] ?? 'text' );
			}
		}

		foreach ( $desired as $col => $type ) {
			if ( in_array( $col, $existing, true ) ) {
				continue;
			}
			// $col is whitelisted by safe_ident()/literal; $type is from a fixed map.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange -- identifiers whitelisted; ALTER cannot use placeholders.
			$wpdb->query( "ALTER TABLE `{$physical}` ADD COLUMN `{$col}` {$type} DEFAULT NULL" );
		}
	}

	/**
	 * Drop every eManager table (used only by uninstall when explicitly enabled).
	 */
	public static function drop_all_tables() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- LIKE pattern escaped; dropping the plugin's own prefixed tables on uninstall.
		$tables = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . 'em_' ) . '%' ) );
		foreach ( (array) $tables as $table ) {
			$safe = preg_replace( '/[^a-zA-Z0-9_]/', '', $table );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange -- table name sanitized to identifier chars; DROP cannot use placeholders; intentional uninstall-time schema change.
			$wpdb->query( "DROP TABLE IF EXISTS `{$safe}`" );
		}
	}
}
