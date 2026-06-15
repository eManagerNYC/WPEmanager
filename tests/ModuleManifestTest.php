<?php
/**
 * Structural validation of every generated module.json manifest.
 *
 * module.json files are emitted by tools/generate-modules.js; this test is the
 * safety net that fails CI if the generator produces a malformed manifest
 * (missing keys, a transition pointing at an undefined state, an unknown party
 * role, a field with no name/type, etc.).
 *
 * @package eManager
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Validates each module.json against the contract the runtime relies on.
 */
final class ModuleManifestTest extends TestCase {

	/**
	 * Provide every module.json path.
	 *
	 * @return array<string, array{0:string}>
	 */
	public static function manifest_provider(): array {
		$root  = dirname( __DIR__ ) . '/emanager/modules';
		$files = glob( $root . '/*/*/module.json' ) ?: array();
		$cases = array();
		foreach ( $files as $file ) {
			$key           = str_replace( $root . '/', '', $file );
			$cases[ $key ] = array( $file );
		}
		return $cases;
	}

	public function test_at_least_one_manifest_exists(): void {
		$this->assertNotEmpty( self::manifest_provider(), 'No module.json manifests were found.' );
	}

	/**
	 * @dataProvider manifest_provider
	 *
	 * @param string $file Absolute path to a module.json.
	 */
	public function test_manifest_is_well_formed( string $file ): void {
		$raw = file_get_contents( $file );
		$this->assertNotFalse( $raw, "Could not read $file" );

		$module = json_decode( $raw, true );
		$this->assertIsArray( $module, "Invalid JSON in $file: " . json_last_error_msg() );

		foreach ( array( 'id', 'name', 'section', 'table', 'fields' ) as $key ) {
			$this->assertArrayHasKey( $key, $module, "$file is missing required key '$key'" );
		}

		// Table name must survive the runtime's identifier whitelist unchanged.
		$this->assertSame(
			EM_DB::safe_ident( $module['table'] ),
			$module['table'],
			"Table name '{$module['table']}' in $file contains unsafe characters"
		);

		// Every field needs a name and a type.
		$this->assertIsArray( $module['fields'], "$file: 'fields' must be an array" );
		foreach ( $module['fields'] as $i => $field ) {
			$this->assertArrayHasKey( 'name', $field, "$file field #$i has no name" );
			$this->assertArrayHasKey( 'type', $field, "$file field '{$field['name']}' has no type" );
		}
	}

	/**
	 * @dataProvider manifest_provider
	 *
	 * @param string $file Absolute path to a module.json.
	 */
	public function test_workflow_is_internally_consistent( string $file ): void {
		$module = json_decode( (string) file_get_contents( $file ), true );

		if ( empty( $module['workflow']['states'] ) ) {
			$this->assertTrue( true, 'Module has no workflow — nothing to check.' );
			return;
		}

		$states = $module['workflow']['states'];

		// The declared initial state must exist.
		if ( isset( $module['workflow']['initial'] ) ) {
			$this->assertArrayHasKey(
				$module['workflow']['initial'],
				$states,
				"$file: initial state '{$module['workflow']['initial']}' is not defined"
			);
		}

		foreach ( $states as $name => $state ) {
			foreach ( ( $state['transitions'] ?? array() ) as $transition ) {
				// Each transition must target a defined state.
				$this->assertArrayHasKey(
					$transition['to'] ?? '',
					$states,
					"$file: state '$name' transitions to undefined state '" . ( $transition['to'] ?? '' ) . "'"
				);

				// Party gates must reference real party roles.
				foreach ( (array) ( $transition['party'] ?? array() ) as $party ) {
					$this->assertContains(
						$party,
						EM_Roles::PARTY_ROLES,
						"$file: state '$name' uses unknown party role '$party'"
					);
				}
			}
		}
	}
}
