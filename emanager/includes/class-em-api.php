<?php
/**
 * REST API (namespace em/v1).
 *
 * Routes:
 *   GET    /boot                                  app bootstrap (registry, user, caps)
 *   GET    /modules/{module}/records              list (sort, filter, search, paginate)
 *   POST   /modules/{module}/records              create
 *   GET    /modules/{module}/records/{id}         read
 *   PUT    /modules/{module}/records/{id}         update
 *   DELETE /modules/{module}/records/{id}         delete (own records, or em_delete)
 *   GET    /modules/{module}/records/{id}/comments
 *   POST   /modules/{module}/records/{id}/comments
 *   GET    /weather?lat=&lon=                     Open-Meteo proxy for Daily Reports
 *   GET    /reports/stats                         per-module record/status counts
 *   POST   /modules/install                       ZIP module install (admin)
 *   DELETE /modules/{module}                      uninstall custom module (admin)
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST controller for all eManager routes.
 */
class EM_Api {

	const NS = 'em/v1';

	/**
	 * Maximum accepted length for a signature data URL (~220 KB PNG).
	 */
	const SIGNATURE_MAX_LENGTH = 300000;

	/**
	 * Register all REST routes.
	 */
	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/boot',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'boot' ),
				'permission_callback' => fn() => is_user_logged_in(),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/(?P<module>[a-z0-9\-_]+)/records',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'list_records' ),
					'permission_callback' => fn() => current_user_can( 'em_read' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'create_record' ),
					'permission_callback' => fn() => current_user_can( 'em_create' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/(?P<module>[a-z0-9\-_]+)/records/(?P<id>[a-zA-Z0-9\-]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'get_record' ),
					'permission_callback' => fn() => current_user_can( 'em_read' ),
				),
				array(
					'methods'             => 'PUT,PATCH',
					'callback'            => array( __CLASS__, 'update_record' ),
					'permission_callback' => fn() => current_user_can( 'em_update' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( __CLASS__, 'delete_record' ),
					'permission_callback' => fn() => is_user_logged_in(), // Ownership checked in callback.
				),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/(?P<module>[a-z0-9\-_]+)/records/(?P<id>[a-zA-Z0-9\-]+)/comments',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'list_comments' ),
					'permission_callback' => fn() => current_user_can( 'em_read' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'create_comment' ),
					'permission_callback' => fn() => current_user_can( 'em_read' ), // Any reader may comment.
				),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/(?P<module>[a-z0-9\-_]+)/records/(?P<id>[a-zA-Z0-9\-]+)/transition',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'transition_record' ),
				'permission_callback' => fn() => current_user_can( 'em_read' ), // Party/cap gate enforced in callback.
			)
		);

		register_rest_route(
			self::NS,
			'/modules/(?P<module>[a-z0-9\-_]+)/records/(?P<id>[a-zA-Z0-9\-]+)/activity',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'record_activity' ),
				'permission_callback' => fn() => current_user_can( 'em_read' ),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/(?P<module>[a-z0-9\-_]+)/records/(?P<id>[a-zA-Z0-9\-]+)/spawn',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'spawn_linked' ),
				'permission_callback' => fn() => current_user_can( 'em_create' ),
			)
		);

		register_rest_route(
			self::NS,
			'/weather',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'weather' ),
				'permission_callback' => fn() => current_user_can( 'em_read' ),
				'args'                => array(
					'lat' => array(
						'required'          => true,
						'sanitize_callback' => 'floatval',
					),
					'lon' => array(
						'required'          => true,
						'sanitize_callback' => 'floatval',
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/reports/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'stats' ),
				'permission_callback' => fn() => current_user_can( 'em_read' ),
			)
		);

		register_rest_route(
			self::NS,
			'/reports/cost-summary',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'cost_summary' ),
				'permission_callback' => fn() => current_user_can( 'em_read' ),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/install',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'install_module' ),
				'permission_callback' => fn() => current_user_can( 'em_manage' ),
			)
		);

		register_rest_route(
			self::NS,
			'/modules/(?P<module>[a-z0-9\-_]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'uninstall_module' ),
				'permission_callback' => fn() => current_user_can( 'em_manage' ),
			)
		);
	}

	// ------------------------------------------------------------------
	// Bootstrap
	// ------------------------------------------------------------------

	/**
	 * App bootstrap payload: module registry, current user, capabilities, project.
	 *
	 * @return WP_REST_Response
	 */
	public static function boot() {
		$user    = wp_get_current_user();
		$company = get_user_meta( $user->ID, 'em_company_id', true );

		return rest_ensure_response(
			array(
				'registry'   => EM_Modules::instance()->registry(),
				'user'       => array(
					'id'         => $user->ID,
					'name'       => $user->display_name,
					'email'      => $user->user_email,
					'company_id' => $company ? $company : null,
					'roles'      => $user->roles,
					'party_role' => EM_Roles::party_role( $user->ID ),
				),
				'caps'       => EM_Roles::current_user_caps(),
				'project'    => get_option( 'em_project', array() ),
				'configured' => EM_DB::is_configured(),
				'flags'      => array(
					/**
					 * Enable the optional in-browser IFC 3D viewer. OFF by default
					 * because it loads three.js / web-ifc from a CDN; bundle those
					 * libraries locally for a fully self-hosted build.
					 *
					 * @param bool $enabled Whether the IFC viewer is enabled.
					 */
					'ifc_viewer' => (bool) apply_filters( 'em_enable_ifc_viewer', false ),
				),
			)
		);
	}

	// ------------------------------------------------------------------
	// CRUD
	// ------------------------------------------------------------------

	/**
	 * Resolve the module for a request or return a 404 error.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return array|WP_Error Module definition or error.
	 */
	private static function module_or_error( $request ) {
		$module = EM_Modules::instance()->get( sanitize_key( $request['module'] ) );
		if ( ! $module ) {
			return new WP_Error( 'em_unknown_module', __( 'Unknown module.', 'emanager' ), array( 'status' => 404 ) );
		}
		return $module;
	}

	/**
	 * List records: sorting, filtering, search and pagination.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function list_records( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}

		$filters = array();
		$raw     = (array) $request->get_param( 'filters' );
		foreach ( $raw as $col => $val ) {
			if ( EM_Modules::has_column( $module, $col ) ) {
				$filters[ $col ] = sanitize_text_field( $val );
			}
		}
		$status = $request->get_param( 'status' );
		if ( $status ) {
			$filters['status'] = sanitize_text_field( $status );
		}

		$sort_param = $request->get_param( 'sort' );
		$sort       = sanitize_key( $sort_param ? $sort_param : 'created_at' );
		if ( ! EM_Modules::has_column( $module, $sort ) ) {
			$sort = 'created_at';
		}

		$order    = $request->get_param( 'order' );
		$page     = (int) $request->get_param( 'page' );
		$per_page = (int) $request->get_param( 'per_page' );

		$result = EM_DB::select(
			$module['table'],
			array(
				'filters'     => $filters,
				'search'      => sanitize_text_field( $request->get_param( 'search' ) ?? '' ),
				'search_cols' => EM_Modules::search_columns( $module ),
				'sort'        => $sort,
				'order'       => $order ? $order : 'desc',
				'page'        => $page ? $page : 1,
				'per_page'    => $per_page ? $per_page : 25,
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return rest_ensure_response(
			array(
				'records' => $result['data'],
				'total'   => $result['total'],
			)
		);
	}

	/**
	 * Read one record.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_record( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}
		$record = EM_DB::get( $module['table'], sanitize_text_field( $request['id'] ) );
		if ( is_wp_error( $record ) ) {
			return $record;
		}
		$record['_can_delete'] = EM_Roles::can_delete_record( $record['created_by'] ?? '' );

		// Workflow: which transitions can this user perform from the current state?
		if ( EM_Workflow::has( $module ) ) {
			$record['_transitions'] = EM_Workflow::available_transitions( $module, $record['status'] ?? '' );
		}

		// Related records: the record this was spawned from, and records spawned from it.
		$record['_links'] = self::build_links( $module, $record );

		return rest_ensure_response( $record );
	}

	/**
	 * Display title for a row (first declared field, falling back to id).
	 *
	 * @param array $module Module definition.
	 * @param array $row    Record row.
	 * @return string
	 */
	private static function record_title( $module, $row ) {
		// Prefer a human title field; fall back to the first field, then the id.
		foreach ( array( 'title', 'subject', 'name' ) as $key ) {
			if ( ! empty( $row[ $key ] ) ) {
				return wp_strip_all_tags( (string) $row[ $key ] );
			}
		}
		$first = $module['fields'][0]['name'] ?? '';
		$title = $first && ! empty( $row[ $first ] ) ? $row[ $first ] : ( '#' . ( $row['id'] ?? '' ) );
		return wp_strip_all_tags( (string) $title );
	}

	/**
	 * Build the related-records graph for a record: its parent (the record it was
	 * spawned from) and its children (records spawned from it). Children are only
	 * searched in this module's declared spawn targets, so it stays cheap.
	 *
	 * @param array $module Module definition.
	 * @param array $record Current record.
	 * @return array { parent: object|null, children: array }
	 */
	private static function build_links( $module, $record ) {
		$links = array(
			'parent'   => null,
			'children' => array(),
		);

		// Parent — the source record carried in linked_module / linked_id.
		if ( ! empty( $record['linked_module'] ) && ! empty( $record['linked_id'] ) ) {
			$parent_mod = EM_Modules::instance()->get( $record['linked_module'] );
			if ( $parent_mod ) {
				$parent_row = EM_DB::get( $parent_mod['table'], $record['linked_id'] );
				if ( ! is_wp_error( $parent_row ) ) {
					$links['parent'] = array(
						'module'  => $parent_mod['id'],
						'section' => $parent_mod['section'],
						'name'    => $parent_mod['name'],
						'id'      => $parent_row['id'],
						'title'   => self::record_title( $parent_mod, $parent_row ),
						'status'  => $parent_row['status'] ?? '',
					);
				}
			}
		}

		// Children — records spawned into this module's declared relation targets.
		foreach ( (array) ( $module['relations'] ?? array() ) as $rel ) {
			$target = EM_Modules::instance()->get( $rel['spawn'] ?? '' );
			if ( ! $target ) {
				continue;
			}
			$res = EM_DB::select(
				$target['table'],
				array(
					'filters'  => array(
						'linked_module' => $module['id'],
						'linked_id'     => (int) $record['id'],
					),
					'per_page' => 50,
				)
			);
			if ( is_wp_error( $res ) ) {
				continue;
			}
			foreach ( $res['data'] as $row ) {
				$links['children'][] = array(
					'module'  => $target['id'],
					'section' => $target['section'],
					'name'    => $target['name'],
					'id'      => $row['id'],
					'title'   => self::record_title( $target, $row ),
					'status'  => $row['status'] ?? '',
				);
			}
		}

		return $links;
	}

	/**
	 * Create a record. Server stamps owner, company, project and initial status.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function create_record( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}

		$data = self::sanitize_fields( $module, $request->get_json_params() );
		$user = wp_get_current_user();

		$company                 = get_user_meta( $user->ID, 'em_company_id', true );
		$project                 = get_option( 'em_project', array() );
		$data['created_by']      = (string) $user->ID;
		$data['created_by_name'] = $user->display_name;
		$data['company_id']      = $company ? $company : null;
		$data['project_id']      = $project['id'] ?? 'default';

		if ( empty( $data['status'] ) && ! empty( $module['statuses'] ) ) {
			$data['status'] = $module['statuses'][0];
		}

		$record = EM_DB::insert( $module['table'], $data );
		if ( is_wp_error( $record ) ) {
			return $record;
		}

		// Seed the activity timeline for workflow-driven modules.
		if ( EM_Workflow::has( $module ) ) {
			EM_Workflow::log( $module['id'], $record['id'], 'created', '', $record['status'] ?? '' );
		}

		/**
		 * Fires after a record is created.
		 *
		 * @param array $record The new record.
		 * @param array $module Module definition.
		 */
		do_action( 'em_record_created', $record, $module );

		return rest_ensure_response( $record );
	}

	/**
	 * Update a record.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function update_record( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}

		$data               = self::sanitize_fields( $module, $request->get_json_params() );
		$data['updated_at'] = gmdate( 'c' );

		$record = EM_DB::update( $module['table'], sanitize_text_field( $request['id'] ), $data );
		if ( is_wp_error( $record ) ) {
			return $record;
		}

		/** This action is documented in includes/class-em-api.php */
		do_action( 'em_record_updated', $record, $module );
		return rest_ensure_response( $record );
	}

	/**
	 * Delete a record. Owners may delete their own; em_delete deletes any.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function delete_record( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}

		$id     = sanitize_text_field( $request['id'] );
		$record = EM_DB::get( $module['table'], $id );
		if ( is_wp_error( $record ) ) {
			return $record;
		}

		if ( ! EM_Roles::can_delete_record( $record['created_by'] ?? '' ) ) {
			return new WP_Error( 'em_forbidden', __( 'You may only delete your own records.', 'emanager' ), array( 'status' => 403 ) );
		}

		$result = EM_DB::delete( $module['table'], $id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		/** This action is documented in includes/class-em-api.php */
		do_action( 'em_record_deleted', $record, $module );
		return rest_ensure_response( array( 'deleted' => true ) );
	}

	// ------------------------------------------------------------------
	// Workflow: status transitions, activity log, record linking.
	// ------------------------------------------------------------------

	/**
	 * Advance a record through its workflow.
	 *
	 * Body: { to: <target status>, direction?: <P&P|PO|DNP>, note?: <string> }.
	 * The transition must be declared from the record's current status and the
	 * acting user must satisfy its party-role and capability gates. The change
	 * and any direction are written to the record and to the activity log.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function transition_record( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}
		if ( ! EM_Workflow::has( $module ) ) {
			return new WP_Error( 'em_no_workflow', __( 'This module has no workflow.', 'emanager' ), array( 'status' => 400 ) );
		}

		$id     = sanitize_text_field( $request['id'] );
		$record = EM_DB::get( $module['table'], $id );
		if ( is_wp_error( $record ) ) {
			return $record;
		}

		$body = (array) $request->get_json_params();
		$to   = sanitize_text_field( $body['to'] ?? '' );
		$from = $record['status'] ?? '';

		$transition = EM_Workflow::find_transition( $module, $from, $to );
		if ( ! $transition ) {
			return new WP_Error( 'em_bad_transition', __( 'That status change is not allowed from the current state.', 'emanager' ), array( 'status' => 409 ) );
		}

		$cap = $transition['cap'] ?? EM_Workflow::DEFAULT_CAP;
		if ( ! current_user_can( $cap ) || ! EM_Roles::user_has_party( $transition['party'] ?? array() ) ) {
			return new WP_Error( 'em_forbidden', __( 'Your role may not perform this step.', 'emanager' ), array( 'status' => 403 ) );
		}

		// Data gating: required fields must be filled before this step.
		$missing = EM_Workflow::missing_requirements( $module, $record, $transition );
		if ( $missing ) {
			return new WP_Error(
				'em_missing_fields',
				/* translators: %s: comma-separated field labels */
				sprintf( __( 'Complete these fields before this step: %s', 'emanager' ), implode( ', ', $missing ) ),
				array( 'status' => 422 )
			);
		}

		$update = array(
			'status'     => $to,
			'updated_at' => gmdate( 'c' ),
		);

		// Optional direction (Proceed & Pricing / Pricing Only / Do Not Proceed).
		$note = sanitize_text_field( $body['note'] ?? '' );
		if ( ! empty( $transition['directions'] ) ) {
			$direction = sanitize_text_field( $body['direction'] ?? '' );
			if ( ! in_array( $direction, $transition['directions'], true ) ) {
				return new WP_Error( 'em_bad_direction', __( 'A valid direction is required for this step.', 'emanager' ), array( 'status' => 400 ) );
			}
			if ( EM_Modules::has_column( $module, 'direction' ) ) {
				$update['direction'] = $direction;
			}
			$note = trim( $direction . ( $note ? ' — ' . $note : '' ) );
		}

		$record = EM_DB::update( $module['table'], $id, $update );
		if ( is_wp_error( $record ) ) {
			return $record;
		}

		EM_Workflow::log( $module['id'], $id, 'transition', $from, $to, $note );

		/**
		 * Fires after a workflow transition.
		 *
		 * @param array  $record     The updated record.
		 * @param array  $module     Module definition.
		 * @param array  $transition The transition definition.
		 */
		do_action( 'em_record_transitioned', $record, $module, $transition );

		$record['_can_delete'] = EM_Roles::can_delete_record( $record['created_by'] ?? '' );
		return rest_ensure_response( $record );
	}

	/**
	 * Return the activity timeline for a record.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function record_activity( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}
		$activity = EM_Workflow::activity( $module['id'], sanitize_text_field( $request['id'] ) );
		if ( is_wp_error( $activity ) ) {
			return $activity;
		}
		return rest_ensure_response( $activity );
	}

	/**
	 * Spawn a linked record in another module (the patent's "convert to NOC",
	 * "convert PCO into Work Directive" one-click chaining).
	 *
	 * Body: { target: <module id> }. The source module must declare a matching
	 * relation in module.json under `relations`. Mapped fields are carried
	 * forward and the new record back-references the source.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function spawn_linked( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}

		$target_id = sanitize_key( ( (array) $request->get_json_params() )['target'] ?? '' );
		$relation  = null;
		foreach ( (array) ( $module['relations'] ?? array() ) as $rel ) {
			if ( ( $rel['spawn'] ?? '' ) === $target_id ) {
				$relation = $rel;
				break;
			}
		}
		if ( ! $relation ) {
			return new WP_Error( 'em_no_relation', __( 'This record cannot be converted to that type.', 'emanager' ), array( 'status' => 400 ) );
		}

		$target = EM_Modules::instance()->get( $target_id );
		if ( ! $target ) {
			return new WP_Error( 'em_unknown_module', __( 'Unknown target module.', 'emanager' ), array( 'status' => 404 ) );
		}

		$id     = sanitize_text_field( $request['id'] );
		$source = EM_DB::get( $module['table'], $id );
		if ( is_wp_error( $source ) ) {
			return $source;
		}

		// Carry mapped fields forward: target_field => source_field.
		$data = array();
		foreach ( (array) ( $relation['map'] ?? array() ) as $target_field => $source_field ) {
			if ( EM_Modules::has_column( $target, $target_field ) && isset( $source[ $source_field ] ) ) {
				$data[ $target_field ] = $source[ $source_field ];
			}
		}

		$user                    = wp_get_current_user();
		$project                 = get_option( 'em_project', array() );
		$company                 = get_user_meta( $user->ID, 'em_company_id', true );
		$data['created_by']      = (string) $user->ID;
		$data['created_by_name'] = $user->display_name;
		$data['company_id']      = $company ? $company : null;
		$data['project_id']      = $project['id'] ?? 'default';
		if ( EM_Modules::has_column( $target, 'linked_module' ) ) {
			$data['linked_module'] = $module['id'];
			$data['linked_id']     = $id;
		}
		if ( empty( $data['status'] ) && ! empty( $target['statuses'] ) ) {
			$data['status'] = $target['statuses'][0];
		}

		$new = EM_DB::insert( $target['table'], $data );
		if ( is_wp_error( $new ) ) {
			return $new;
		}

		// Audit both ends of the link.
		$label = $target['name'];
		EM_Workflow::log( $module['id'], $id, 'linked', '', '', sprintf( /* translators: %s: target module name */ __( 'Converted to %s', 'emanager' ), $label ) );
		EM_Workflow::log( $target['id'], $new['id'], 'created', '', $new['status'] ?? '', sprintf( /* translators: %s: source module name */ __( 'Created from %s', 'emanager' ), $module['name'] ) );

		return rest_ensure_response(
			array(
				'module'  => $target['id'],
				'section' => $target['section'],
				'record'  => $new,
			)
		);
	}

	/**
	 * Sanitize input against the module's declared fields. Unknown keys are dropped.
	 *
	 * @param array $module Module definition.
	 * @param array $input  Raw JSON body.
	 * @return array Sanitized data keyed by field name.
	 */
	private static function sanitize_fields( $module, $input ) {
		$out   = array();
		$input = (array) $input;

		foreach ( (array) ( $module['fields'] ?? array() ) as $field ) {
			$name = $field['name'];
			if ( ! array_key_exists( $name, $input ) ) {
				continue;
			}
			$value = $input[ $name ];
			switch ( $field['type'] ?? 'text' ) {
				case 'textarea':
				case 'richtext':
					$out[ $name ] = wp_kses_post( (string) $value );
					break;
				case 'number':
				case 'currency':
					$out[ $name ] = is_numeric( $value ) ? $value + 0 : null;
					break;
				case 'checkbox':
					$out[ $name ] = (bool) $value;
					break;
				case 'email':
					$out[ $name ] = sanitize_email( (string) $value );
					break;
				case 'json':
					$out[ $name ] = is_array( $value ) ? $value : json_decode( (string) $value, true );
					break;
				case 'signature':
					$out[ $name ] = self::sanitize_signature( $value );
					break;
				default:
					$out[ $name ] = sanitize_text_field( (string) $value );
			}
		}

		// Status must be one of the module's declared statuses.
		if ( isset( $input['status'] ) && ! empty( $module['statuses'] ) ) {
			$status = sanitize_text_field( $input['status'] );
			if ( in_array( $status, $module['statuses'], true ) ) {
				$out['status'] = $status;
			}
		}

		return $out;
	}

	/**
	 * Validate an electronic signature value: must be a PNG data URL with
	 * valid base64 payload, within the size cap. Anything else becomes null.
	 *
	 * @param mixed $value Raw signature value.
	 * @return string|null
	 */
	private static function sanitize_signature( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return null;
		}
		if ( strlen( $value ) > self::SIGNATURE_MAX_LENGTH ) {
			return null;
		}
		if ( ! preg_match( '#^data:image/png;base64,([A-Za-z0-9+/]+={0,2})$#', $value, $m ) ) {
			return null;
		}
		// Must decode to a real PNG (magic bytes check).
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- decoding user-submitted signature image data to validate it is a genuine PNG; nothing is executed.
		$binary = base64_decode( $m[1], true );
		if ( false === $binary || "\x89PNG" !== substr( $binary, 0, 4 ) ) {
			return null;
		}
		return $value;
	}

	// ------------------------------------------------------------------
	// Comments — stored in one shared table (em_comments).
	// ------------------------------------------------------------------

	/**
	 * List comments for a record.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function list_comments( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}
		$result = EM_DB::select(
			'em_comments',
			array(
				'filters'  => array(
					'module_id' => $module['id'],
					'record_id' => (int) $request['id'],
				),
				'sort'     => 'created_at',
				'order'    => 'asc',
				'per_page' => 500,
			)
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return rest_ensure_response( $result['data'] );
	}

	/**
	 * Add a comment to a record.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function create_comment( WP_REST_Request $request ) {
		$module = self::module_or_error( $request );
		if ( is_wp_error( $module ) ) {
			return $module;
		}
		$body = trim( wp_kses_post( (string) ( $request->get_json_params()['body'] ?? '' ) ) );
		if ( '' === $body ) {
			return new WP_Error( 'em_empty_comment', __( 'Comment cannot be empty.', 'emanager' ), array( 'status' => 400 ) );
		}

		$user    = wp_get_current_user();
		$comment = EM_DB::insert(
			'em_comments',
			array(
				'module_id'   => $module['id'],
				'record_id'   => sanitize_text_field( $request['id'] ),
				'body'        => $body,
				'author_id'   => (string) $user->ID,
				'author_name' => $user->display_name,
			)
		);
		if ( is_wp_error( $comment ) ) {
			return $comment;
		}
		return rest_ensure_response( $comment );
	}

	// ------------------------------------------------------------------
	// Weather proxy (Open-Meteo, no API key required).
	// ------------------------------------------------------------------

	/**
	 * Hourly-cached weather lookup used by the Daily Reports module.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function weather( WP_REST_Request $request ) {
		$lat = (float) $request['lat'];
		$lon = (float) $request['lon'];
		$key = 'em_weather_' . md5( $lat . ',' . $lon . gmdate( 'YmdH' ) );

		$cached = get_transient( $key );
		if ( $cached ) {
			return rest_ensure_response( $cached );
		}

		$url = add_query_arg(
			array(
				'latitude'           => $lat,
				'longitude'          => $lon,
				'current'            => 'temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m,weather_code',
				'daily'              => 'temperature_2m_max,temperature_2m_min,precipitation_sum',
				'forecast_days'      => 1,
				'temperature_unit'   => 'fahrenheit',
				'wind_speed_unit'    => 'mph',
				'precipitation_unit' => 'inch',
			),
			'https://api.open-meteo.com/v1/forecast'
		);

		$response = wp_remote_get( $url, array( 'timeout' => 10 ) );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		set_transient( $key, $data, HOUR_IN_SECONDS );
		return rest_ensure_response( $data );
	}

	// ------------------------------------------------------------------
	// Reports — record + status counts per module.
	// ------------------------------------------------------------------

	/**
	 * Record and status counts for every module.
	 *
	 * @return WP_REST_Response
	 */
	public static function stats() {
		$stats = array();
		foreach ( EM_Modules::instance()->all() as $module ) {
			if ( ! empty( $module['virtual'] ) ) {
				continue;
			}
			$counts = array(
				'total'     => 0,
				'by_status' => array(),
			);

			// One indexed GROUP BY per table — scales to tens of thousands of rows.
			foreach ( EM_DB::status_counts( $module['table'] ) as $status => $count ) {
				$label                         = '' === $status ? __( 'None', 'emanager' ) : $status;
				$counts['by_status'][ $label ] = $count;
				$counts['total']              += $count;
			}

			$stats[] = array(
				'id'      => $module['id'],
				'name'    => $module['name'],
				'section' => $module['section'],
				'counts'  => $counts,
			);
		}
		return rest_ensure_response( $stats );
	}

	/**
	 * Financial roll-up across the cost modules (Procore "Budget tool" parity):
	 * original budget, approved changes, revised budget, committed cost, actual
	 * cost to date, forecast at completion and projected over/under.
	 *
	 * Each figure is a single indexed SUM, so it stays fast at scale. Missing
	 * modules simply contribute 0.
	 *
	 * @return WP_REST_Response
	 */
	public static function cost_summary() {
		$sum = static function ( $module_id, $column ) {
			$module = EM_Modules::instance()->get( $module_id );
			return $module ? EM_DB::sum_column( $module['table'], $column ) : 0.0;
		};

		$original_budget  = $sum( 'budget-forecast', 'original_budget' );
		$approved_changes = $sum( 'change-orders', 'amount' );
		$revised_budget   = $original_budget + $approved_changes;

		$committed = $sum( 'commitments', 'value' ) + $sum( 'subcontracts', 'value' );

		// Actual cost to date = direct costs + approved sub invoices + T&M tickets.
		$actual = $sum( 'direct-costs', 'amount' )
			+ $sum( 'subcontractor-invoices', 'approved_amount' )
			+ $sum( 'tm-tickets', 'total' );

		$forecast = $sum( 'budget-forecast', 'forecast_final' );
		if ( $forecast <= 0 ) {
			$forecast = max( $revised_budget, $committed, $actual );
		}

		$owner_billed = $sum( 'owner-invoices', 'amount_due' ) + $sum( 'invoicing', 'current_due' );
		$pending      = $sum( 'potential-changes', 'rom_estimate' ) + $sum( 'change-events', 'rom_cost' );

		return rest_ensure_response(
			array(
				'currency' => 'USD',
				'figures'  => array(
					array(
						'key'   => 'original_budget',
						'label' => __( 'Original budget', 'emanager' ),
						'value' => $original_budget,
					),
					array(
						'key'   => 'approved_changes',
						'label' => __( 'Approved changes', 'emanager' ),
						'value' => $approved_changes,
					),
					array(
						'key'   => 'revised_budget',
						'label' => __( 'Revised budget', 'emanager' ),
						'value' => $revised_budget,
					),
					array(
						'key'   => 'committed',
						'label' => __( 'Committed cost', 'emanager' ),
						'value' => $committed,
					),
					array(
						'key'   => 'actual',
						'label' => __( 'Actual cost to date', 'emanager' ),
						'value' => $actual,
					),
					array(
						'key'   => 'forecast',
						'label' => __( 'Forecast at completion', 'emanager' ),
						'value' => $forecast,
					),
					array(
						'key'   => 'variance',
						'label' => __( 'Projected over / (under)', 'emanager' ),
						'value' => $forecast - $revised_budget,
					),
					array(
						'key'   => 'pending_changes',
						'label' => __( 'Pending changes (ROM)', 'emanager' ),
						'value' => $pending,
					),
					array(
						'key'   => 'owner_billed',
						'label' => __( 'Billed to owner', 'emanager' ),
						'value' => $owner_billed,
					),
				),
			)
		);
	}

	// ------------------------------------------------------------------
	// Module management
	// ------------------------------------------------------------------

	/**
	 * Install a module from an uploaded ZIP (multipart field "module").
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function install_module( WP_REST_Request $request ) {
		$files = $request->get_file_params();
		if ( empty( $files['module'] ) ) {
			return new WP_Error( 'em_no_file', __( 'Upload a ZIP file in the "module" field.', 'emanager' ), array( 'status' => 400 ) );
		}
		$result = EM_Installer::install_from_upload( $files['module'] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return rest_ensure_response( $result );
	}

	/**
	 * Uninstall a custom (uploads-based) module.
	 *
	 * @param WP_REST_Request $request Current request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function uninstall_module( WP_REST_Request $request ) {
		$result = EM_Installer::uninstall( $request['module'] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return rest_ensure_response( array( 'uninstalled' => true ) );
	}
}
