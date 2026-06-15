<?php
/**
 * PHPUnit bootstrap for eManager unit tests.
 *
 * These are pure-logic unit tests: they exercise the framework code that does
 * not touch the database or the live WordPress runtime (workflow state machine,
 * SQL identifier/column helpers, role label maps, module-manifest validity).
 *
 * Rather than booting all of WordPress (which would require a test DB and the
 * wp-phpunit harness — see the live smoke scripts in tools/ for that level),
 * we define just the handful of WP functions the code under test calls. Test
 * state is steered through the $GLOBALS['em_test_*'] knobs reset in setUp().
 *
 * @package eManager
 */

declare( strict_types=1 );

// The plugin guards every file with `defined( 'ABSPATH' ) || exit;`.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

$GLOBALS['em_test_caps']      = array(); // Capabilities the "current user" holds.
$GLOBALS['em_test_uid']       = 0;       // Current user id.
$GLOBALS['em_test_user_meta'] = array(); // [ uid => [ key => value ] ].

if ( ! function_exists( '__' ) ) {
	/**
	 * Translation passthrough.
	 *
	 * @param string $text   Text.
	 * @param string $domain Text domain (ignored).
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	/**
	 * @param string $cap Capability.
	 * @return bool
	 */
	function current_user_can( $cap ) {
		return in_array( $cap, $GLOBALS['em_test_caps'], true );
	}
}

if ( ! function_exists( 'user_can' ) ) {
	/**
	 * @param int    $user_id User id (ignored — single test user).
	 * @param string $cap     Capability.
	 * @return bool
	 */
	function user_can( $user_id, $cap ) {
		return in_array( $cap, $GLOBALS['em_test_caps'], true );
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	/**
	 * @return int
	 */
	function get_current_user_id() {
		return (int) $GLOBALS['em_test_uid'];
	}
}

if ( ! function_exists( 'get_user_meta' ) ) {
	/**
	 * @param int    $user_id User id.
	 * @param string $key     Meta key.
	 * @param bool   $single  Single (assumed true).
	 * @return mixed
	 */
	function get_user_meta( $user_id, $key, $single = false ) {
		return $GLOBALS['em_test_user_meta'][ $user_id ][ $key ] ?? '';
	}
}

require_once __DIR__ . '/../emanager/includes/class-em-roles.php';
require_once __DIR__ . '/../emanager/includes/class-em-workflow.php';
require_once __DIR__ . '/../emanager/includes/class-em-db.php';
