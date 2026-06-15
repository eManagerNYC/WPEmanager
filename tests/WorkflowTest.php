<?php
/**
 * Unit tests for the workflow state-machine engine (EM_Workflow).
 *
 * @package eManager
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Exercises the pure (no-DB) parts of the workflow engine.
 */
final class WorkflowTest extends TestCase {

	/**
	 * A representative two-state module with a gated transition.
	 *
	 * @var array
	 */
	private array $module;

	protected function setUp(): void {
		$GLOBALS['em_test_caps']      = array();
		$GLOBALS['em_test_uid']       = 1;
		$GLOBALS['em_test_user_meta'] = array();

		$this->module = array(
			'id'       => 'pcos',
			'fields'   => array(
				array(
					'name'  => 'answer',
					'label' => 'Owner answer',
				),
			),
			'workflow' => array(
				'states' => array(
					'Owner Review'     => array(
						'transitions' => array(
							array(
								'to'       => 'Assign Contracts',
								'label'    => 'Approve & assign',
								'party'    => array( 'owner', 'rep' ),
								'requires' => array( 'answer' ),
							),
						),
					),
					'Assign Contracts' => array( 'transitions' => array() ),
				),
			),
		);
	}

	public function test_has_and_get_detect_workflow(): void {
		$this->assertTrue( EM_Workflow::has( $this->module ) );
		$this->assertNotNull( EM_Workflow::get( $this->module ) );

		$plain = array( 'id' => 'notes' );
		$this->assertFalse( EM_Workflow::has( $plain ) );
		$this->assertNull( EM_Workflow::get( $plain ) );
	}

	public function test_transitions_from_known_and_unknown_state(): void {
		$transitions = EM_Workflow::transitions_from( $this->module, 'Owner Review' );
		$this->assertCount( 1, $transitions );
		$this->assertSame( 'Assign Contracts', $transitions[0]['to'] );

		$this->assertSame( array(), EM_Workflow::transitions_from( $this->module, 'Nonexistent' ) );
		$this->assertSame( array(), EM_Workflow::transitions_from( $this->module, 'Assign Contracts' ) );
	}

	public function test_find_transition(): void {
		$found = EM_Workflow::find_transition( $this->module, 'Owner Review', 'Assign Contracts' );
		$this->assertIsArray( $found );
		$this->assertSame( 'Approve & assign', $found['label'] );

		$this->assertNull( EM_Workflow::find_transition( $this->module, 'Owner Review', 'Bogus' ) );
	}

	public function test_missing_requirements_reports_field_label(): void {
		// Empty value -> the human label of the missing field is returned.
		$missing = EM_Workflow::missing_requirements(
			$this->module,
			array( 'answer' => '' ),
			$this->module['workflow']['states']['Owner Review']['transitions'][0]
		);
		$this->assertSame( array( 'Owner answer' ), $missing );

		// Filled value -> requirement satisfied.
		$ok = EM_Workflow::missing_requirements(
			$this->module,
			array( 'answer' => 'Approved' ),
			$this->module['workflow']['states']['Owner Review']['transitions'][0]
		);
		$this->assertSame( array(), $ok );
	}

	public function test_available_transitions_gate_on_party_and_cap(): void {
		$transition = $this->module['workflow']['states']['Owner Review']['transitions'][0];

		// Wrong party + no cap -> blocked, with a human reason.
		$GLOBALS['em_test_caps']                  = array();
		$GLOBALS['em_test_user_meta'][1]['em_party_role'] = 'subcontractor';
		$blocked = EM_Workflow::available_transitions( $this->module, 'Owner Review' );
		$this->assertFalse( $blocked[0]['allowed'] );
		$this->assertStringContainsString( 'Owner', $blocked[0]['reason'] );

		// Correct party + create cap -> allowed.
		$GLOBALS['em_test_caps']                  = array( 'em_create' );
		$GLOBALS['em_test_user_meta'][1]['em_party_role'] = 'owner';
		$allowed = EM_Workflow::available_transitions( $this->module, 'Owner Review' );
		$this->assertTrue( $allowed[0]['allowed'] );

		// Manager bypasses every party gate.
		$GLOBALS['em_test_caps']                  = array( 'em_create', 'em_manage' );
		$GLOBALS['em_test_user_meta'][1]['em_party_role'] = 'subcontractor';
		$manager = EM_Workflow::available_transitions( $this->module, 'Owner Review' );
		$this->assertTrue( $manager[0]['allowed'] );

		// Keep the unused local referenced for clarity.
		$this->assertSame( 'Assign Contracts', $transition['to'] );
	}
}
