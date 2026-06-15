<?php
/** Per-section permissions: restricted party role is blocked from hidden sections. */
function r( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$x = rest_do_request( $q );
	return array( $x->get_status(), $x->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// A subcontractor restricted to Field + Change Management only.
$sid = ( get_user_by( 'email', 'sec-sub@example.com' ) ?: null );
$sid = $sid ? $sid->ID : wp_insert_user( array( 'user_login' => 'sec_sub', 'user_email' => 'sec-sub@example.com', 'user_pass' => wp_generate_password(), 'role' => 'em_editor' ) );
update_user_meta( $sid, 'em_party_role', 'subcontractor' );
update_option( 'em_section_access', array( 'subcontractor' => array( 'field', 'change-management' ) ) );

wp_set_current_user( $sid );

// Boot registry should exclude Cost (not allowed) and include Field.
list( $s, $boot ) = r( 'GET', '/em/v1/boot' );
$secs = array_map( fn( $x ) => $x['id'], $boot['registry'] ?? array() );
line( 'Restricted boot excludes Cost', ! in_array( 'cost', $secs, true ), 'sections: ' . implode( ',', $secs ) );
line( 'Restricted boot includes Field', in_array( 'field', $secs, true ) );

// Direct REST access to a Cost module is forbidden (403).
list( $s, $d ) = r( 'GET', '/em/v1/modules/change-orders/records' );
line( 'Direct access to hidden Cost module blocked', 403 === $s, 'status=' . $s );

// An allowed module (Field daily-reports) still works.
list( $s2, $d2 ) = r( 'GET', '/em/v1/modules/daily-reports/records' );
line( 'Allowed Field module accessible', 200 === $s2, 'status=' . $s2 );

// Admin is never restricted.
wp_set_current_user( 1 );
list( $s3 ) = r( 'GET', '/em/v1/modules/change-orders/records' );
line( 'Administrator bypasses restriction', 200 === $s3, 'status=' . $s3 );

delete_option( 'em_section_access' );
echo "DONE\n";
