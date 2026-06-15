<?php
/**
 * Unit tests for the SQL-safety helpers in EM_DB.
 *
 * These guard the single choke point for all custom-table SQL: identifiers are
 * whitelisted and field types map to a fixed set of column types.
 *
 * @package eManager
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Exercises the pure helpers EM_DB::safe_ident() and EM_DB::column_type().
 */
final class DbHelpersTest extends TestCase {

	public function test_safe_ident_strips_unsafe_characters(): void {
		$this->assertSame( 'em_rfis', EM_DB::safe_ident( 'em_rfis' ) );
		$this->assertSame( 'em_rfis', EM_DB::safe_ident( 'EM_RFIs' ) );
		// Quote/semicolon injection attempts are stripped to harmless chars.
		$this->assertSame( 'droptableusers', EM_DB::safe_ident( 'DROP TABLE users;' ) );
		$this->assertSame( 'abc123', EM_DB::safe_ident( 'abc-`123`' ) );
		$this->assertSame( 'a_b_c', EM_DB::safe_ident( 'a_b_c' ) );
		$this->assertSame( '', EM_DB::safe_ident( '"; --' ) );
	}

	/**
	 * @dataProvider column_type_cases
	 *
	 * @param string $field_type eManager field type.
	 * @param string $expected   Expected MySQL column type.
	 */
	public function test_column_type_maps_field_types( string $field_type, string $expected ): void {
		$this->assertSame( $expected, EM_DB::column_type( $field_type ) );
	}

	/**
	 * @return array<string, array{0:string,1:string}>
	 */
	public static function column_type_cases(): array {
		return array(
			'textarea -> text'        => array( 'textarea', 'text' ),
			'richtext -> text'        => array( 'richtext', 'text' ),
			'json -> longtext'        => array( 'json', 'longtext' ),
			'signature -> mediumtext' => array( 'signature', 'mediumtext' ),
			'number -> decimal'       => array( 'number', 'decimal(20,4)' ),
			'currency -> decimal'     => array( 'currency', 'decimal(20,4)' ),
			'date -> date'            => array( 'date', 'date' ),
			'datetime -> datetime'    => array( 'datetime', 'datetime' ),
			'checkbox -> tinyint'     => array( 'checkbox', 'tinyint(1)' ),
			'text -> varchar default' => array( 'text', 'varchar(191)' ),
			'unknown -> varchar'      => array( 'something-else', 'varchar(191)' ),
		);
	}
}
