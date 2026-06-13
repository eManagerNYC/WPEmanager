<?php
/**
 * Workflow state-machine engine.
 *
 * Implements the patent's "logic gates [that] start at a user's role and go on
 * through the construction process." A module may declare a `workflow` in its
 * module.json:
 *
 *   "workflow": {
 *     "states": {
 *       "Owner Review": {
 *         "transitions": [
 *           { "to": "Assign Contracts", "label": "Approve & assign", "party": ["owner","rep"], "directions": ["P&P","PO","DNP"] }
 *         ]
 *       },
 *       ...
 *     }
 *   }
 *
 * Each transition is gated by the acting user's party role (Owner, GC, Sub,
 * Consultant, Rep) and an optional capability floor. Every transition is
 * recorded in the shared em_activity audit table, giving each record a full
 * status history — the electronic replacement for paper routing slips.
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolves workflow definitions, validates transitions and logs activity.
 */
class EM_Workflow {

	const ACTIVITY_TABLE = 'em_activity';

	/**
	 * Capability floor required to act on a workflow transition.
	 */
	const DEFAULT_CAP = 'em_create';

	/**
	 * Get a module's workflow definition.
	 *
	 * @param array $module Module definition.
	 * @return array|null
	 */
	public static function get( $module ) {
		return ! empty( $module['workflow']['states'] ) ? $module['workflow'] : null;
	}

	/**
	 * Does this module have a workflow?
	 *
	 * @param array $module Module definition.
	 * @return bool
	 */
	public static function has( $module ) {
		return null !== self::get( $module );
	}

	/**
	 * All transitions declared from a given state.
	 *
	 * @param array  $module Module definition.
	 * @param string $status Current status.
	 * @return array List of transition definitions.
	 */
	public static function transitions_from( $module, $status ) {
		$workflow = self::get( $module );
		if ( ! $workflow ) {
			return array();
		}
		return $workflow['states'][ $status ]['transitions'] ?? array();
	}

	/**
	 * Transitions the current user is allowed to perform from a state,
	 * annotated with an `allowed` flag and a human reason when blocked.
	 *
	 * @param array  $module Module definition.
	 * @param string $status Current status.
	 * @return array
	 */
	public static function available_transitions( $module, $status ) {
		$out = array();
		foreach ( self::transitions_from( $module, $status ) as $transition ) {
			$cap     = $transition['cap'] ?? self::DEFAULT_CAP;
			$parties = $transition['party'] ?? array();
			$allowed = current_user_can( $cap ) && EM_Roles::user_has_party( $parties );

			$transition['allowed'] = $allowed;
			if ( ! $allowed && $parties ) {
				$labels               = EM_Roles::party_role_labels();
				$names                = array_map(
					fn( $p ) => $labels[ $p ] ?? $p,
					(array) $parties
				);
				$transition['reason'] = sprintf(
					/* translators: %s: list of party roles */
					__( 'Restricted to: %s', 'emanager' ),
					implode( ', ', $names )
				);
			}
			$out[] = $transition;
		}
		return $out;
	}

	/**
	 * Find a specific transition definition (status -> $to) or null.
	 *
	 * @param array  $module Module definition.
	 * @param string $status Current status.
	 * @param string $to     Target status.
	 * @return array|null
	 */
	public static function find_transition( $module, $status, $to ) {
		foreach ( self::transitions_from( $module, $status ) as $transition ) {
			if ( ( $transition['to'] ?? '' ) === $to ) {
				return $transition;
			}
		}
		return null;
	}

	/**
	 * Record a workflow event in the activity table. Best-effort: a logging
	 * failure never blocks the underlying status change.
	 *
	 * @param string $module_id Module id.
	 * @param string $record_id Record id.
	 * @param string $action    Short action label (e.g. "transition", "created", "linked").
	 * @param string $from      Previous status.
	 * @param string $to        New status.
	 * @param string $note      Optional note.
	 * @return void
	 */
	public static function log( $module_id, $record_id, $action, $from = '', $to = '', $note = '' ) {
		$user = wp_get_current_user();
		EM_DB::insert(
			self::ACTIVITY_TABLE,
			array(
				'module_id'   => $module_id,
				'record_id'   => $record_id,
				'actor_id'    => (string) $user->ID,
				'actor_name'  => $user->display_name,
				'action'      => $action,
				'from_status' => $from,
				'to_status'   => $to,
				'note'        => $note,
			)
		);
	}

	/**
	 * Read the activity timeline for a record (oldest first).
	 *
	 * @param string $module_id Module id.
	 * @param string $record_id Record id.
	 * @return array|WP_Error
	 */
	public static function activity( $module_id, $record_id ) {
		$result = EM_DB::select(
			self::ACTIVITY_TABLE,
			array(
				'filters'  => array(
					'module_id' => $module_id,
					'record_id' => (int) $record_id,
				),
				'sort'     => 'created_at',
				'order'    => 'asc',
				'per_page' => 500,
			)
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return $result['data'];
	}
}
