<?php
/**
 * Unit tests for the role/party helpers in EM_Roles.
 *
 * @package eManager
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Exercises the pure party-role logic.
 */
final class RolesTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['em_test_caps']      = array();
		$GLOBALS['em_test_uid']       = 1;
		$GLOBALS['em_test_user_meta'] = array();
	}

	public function test_party_role_labels_cover_every_party_role(): void {
		$labels = EM_Roles::party_role_labels();
		foreach ( EM_Roles::PARTY_ROLES as $role ) {
			$this->assertArrayHasKey( $role, $labels );
			$this->assertNotSame( '', $labels[ $role ] );
		}
	}

	public function test_party_role_reads_stored_meta(): void {
		$GLOBALS['em_test_user_meta'][1]['em_party_role'] = 'subcontractor';
		$this->assertSame( 'subcontractor', EM_Roles::party_role() );
	}

	public function test_party_role_falls_back_to_gc_for_managers(): void {
		$GLOBALS['em_test_caps'] = array( 'em_manage' );
		$this->assertSame( 'gc', EM_Roles::party_role() );
	}

	public function test_party_role_empty_when_unassigned_and_not_manager(): void {
		$this->assertSame( '', EM_Roles::party_role() );
	}

	public function test_invalid_stored_role_is_ignored(): void {
		$GLOBALS['em_test_user_meta'][1]['em_party_role'] = 'not-a-real-role';
		$this->assertSame( '', EM_Roles::party_role() );
	}

	public function test_user_has_party_rules(): void {
		// No restriction -> always allowed.
		$this->assertTrue( EM_Roles::user_has_party( array() ) );

		// Managers pass every gate.
		$GLOBALS['em_test_caps'] = array( 'em_manage' );
		$this->assertTrue( EM_Roles::user_has_party( array( 'owner' ) ) );

		// Match on stored role.
		$GLOBALS['em_test_caps']                           = array();
		$GLOBALS['em_test_user_meta'][1]['em_party_role']  = 'owner';
		$this->assertTrue( EM_Roles::user_has_party( array( 'owner', 'rep' ) ) );
		$this->assertFalse( EM_Roles::user_has_party( array( 'subcontractor' ) ) );
	}
}
