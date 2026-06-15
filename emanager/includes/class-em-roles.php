<?php
/**
 * Roles & capabilities for eManager.
 *
 * Role matrix:
 *  - em_administrator : create / read / update / delete (any record) + manage settings
 *  - em_editor        : create / read / update (delete own records only)
 *  - em_contributor   : create / read        (delete own records only)
 *  - em_viewer        : read
 *  - em_restricted    : no access
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers eManager roles/capabilities and enforces ownership rules.
 */
class EM_Roles {

	const CAPS = array( 'em_create', 'em_read', 'em_update', 'em_delete', 'em_manage' );

	/**
	 * Project "party" roles (org-chart position), distinct from CRUD capabilities.
	 *
	 * The patent's logic gates begin at a user's party role: an Owner only sees
	 * owner workflows (e.g. reviewing NOCs), a Subcontractor only the sub steps
	 * (proposals, tickets, DCRs), and so on. Stored in user meta em_party_role.
	 */
	const PARTY_ROLES = array( 'gc', 'owner', 'rep', 'consultant', 'subcontractor' );

	/**
	 * Register eManager roles and grant all caps to WP administrators.
	 */
	public static function add_roles() {
		add_role(
			'em_administrator',
			__( 'eManager Administrator', 'emanager' ),
			array(
				'read'      => true,
				'em_read'   => true,
				'em_create' => true,
				'em_update' => true,
				'em_delete' => true,
				'em_manage' => true,
			)
		);
		add_role(
			'em_editor',
			__( 'eManager Editor', 'emanager' ),
			array(
				'read'      => true,
				'em_read'   => true,
				'em_create' => true,
				'em_update' => true,
			)
		);
		add_role(
			'em_contributor',
			__( 'eManager Contributor', 'emanager' ),
			array(
				'read'      => true,
				'em_read'   => true,
				'em_create' => true,
			)
		);
		add_role(
			'em_viewer',
			__( 'eManager Viewer', 'emanager' ),
			array(
				'read'    => true,
				'em_read' => true,
			)
		);
		add_role(
			'em_restricted',
			__( 'eManager Restricted', 'emanager' ),
			array(
				'read' => true,
			)
		);

		// Site administrators get every eManager capability.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( self::CAPS as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove eManager roles (used by uninstall).
	 */
	public static function remove_roles() {
		foreach ( array( 'em_administrator', 'em_editor', 'em_contributor', 'em_viewer', 'em_restricted' ) as $role ) {
			remove_role( $role );
		}
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( self::CAPS as $cap ) {
				$admin->remove_cap( $cap );
			}
		}
	}

	/**
	 * Can the current user delete a given record?
	 * Admins delete anything; anyone who can create may delete records they own.
	 *
	 * @param string $record_owner WP user ID stored on the record (created_by).
	 * @return bool
	 */
	public static function can_delete_record( $record_owner ) {
		if ( current_user_can( 'em_delete' ) ) {
			return true;
		}
		return current_user_can( 'em_create' ) && (string) get_current_user_id() === (string) $record_owner;
	}

	/**
	 * Capability set for the current user, exposed to the front-end app.
	 *
	 * @return array
	 */
	public static function current_user_caps() {
		$caps = array();
		foreach ( self::CAPS as $cap ) {
			$caps[ $cap ] = current_user_can( $cap );
		}
		return $caps;
	}

	/**
	 * Human-readable labels for the party roles.
	 *
	 * @return array<string,string>
	 */
	public static function party_role_labels() {
		return array(
			'gc'            => __( 'General Contractor', 'emanager' ),
			'owner'         => __( 'Owner', 'emanager' ),
			'rep'           => __( "Owner's Representative", 'emanager' ),
			'consultant'    => __( 'Consultant / Architect / Engineer', 'emanager' ),
			'subcontractor' => __( 'Subcontractor', 'emanager' ),
		);
	}

	/**
	 * The current user's party role (org-chart position).
	 * WordPress administrators and eManager administrators act as GC by default
	 * so they can drive every step, but a stored value always wins.
	 *
	 * @param int|null $user_id Optional user ID; defaults to the current user.
	 * @return string One of PARTY_ROLES, or '' when unassigned.
	 */
	public static function party_role( $user_id = null ) {
		$user_id = $user_id ? $user_id : get_current_user_id();
		$role    = get_user_meta( $user_id, 'em_party_role', true );
		if ( $role && in_array( $role, self::PARTY_ROLES, true ) ) {
			return $role;
		}
		if ( user_can( $user_id, 'em_manage' ) ) {
			return 'gc';
		}
		return '';
	}

	/**
	 * Does the current user hold one of the given party roles?
	 * Managers (GC admins) pass every party gate so the workflow never stalls.
	 *
	 * @param array $roles Allowed party roles for a workflow transition.
	 * @return bool
	 */
	public static function user_has_party( $roles ) {
		if ( empty( $roles ) ) {
			return true; // No party restriction on this transition.
		}
		if ( current_user_can( 'em_manage' ) ) {
			return true;
		}
		return in_array( self::party_role(), (array) $roles, true );
	}

	/**
	 * Can the current user access a given section?
	 *
	 * Access is controlled by the `em_section_access` option, a map of
	 * party role => allowed section ids. A party role with NO entry is
	 * unrestricted (backwards-compatible default); managers always pass.
	 *
	 * @param string $section Section id.
	 * @return bool
	 */
	public static function can_access_section( $section ) {
		if ( current_user_can( 'em_manage' ) ) {
			return true;
		}
		$party = self::party_role();
		if ( '' === $party ) {
			return true; // Unassigned party: don't lock anyone out by default.
		}
		$map = get_option( 'em_section_access', array() );
		if ( ! isset( $map[ $party ] ) ) {
			return true; // This role is unrestricted.
		}
		return in_array( $section, (array) $map[ $party ], true );
	}
}
