<?php
/** In my court: records awaiting the current user's party action surface; others don't. */
function r( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$x = rest_do_request( $q );
	return array( $x->get_status(), $x->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_rfis" );

// A consultant (RFIs are answered by consultants).
$cid = ( get_user_by( 'email', 'court-consultant@example.com' ) ?: null );
$cid = $cid ? $cid->ID : wp_insert_user( array( 'user_login' => 'court_consultant', 'user_email' => 'court-consultant@example.com', 'user_pass' => wp_generate_password(), 'role' => 'em_editor' ) );
update_user_meta( $cid, 'em_party_role', 'consultant' );
// A subcontractor (should NOT see RFIs in their court).
$sid = ( get_user_by( 'email', 'court-sub@example.com' ) ?: null );
$sid = $sid ? $sid->ID : wp_insert_user( array( 'user_login' => 'court_sub', 'user_email' => 'court-sub@example.com', 'user_pass' => wp_generate_password(), 'role' => 'em_editor' ) );
update_user_meta( $sid, 'em_party_role', 'subcontractor' );

// Admin (GC) creates an RFI and submits it → status Open (consultant's court).
wp_set_current_user( 1 );
list( $s, $rfi ) = r( 'POST', '/em/v1/modules/rfis/records', array( 'subject' => 'Court test', 'question' => 'Q' ) );
$id = $rfi['id'] ?? 0;
r( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Open' ) );

// Consultant's court should include this Open RFI.
wp_set_current_user( $cid );
list( $s, $court ) = r( 'GET', '/em/v1/my-court' );
$found = false;
foreach ( (array) $court as $it ) { if ( 'rfis' === $it['module'] && (int) $it['id'] === (int) $id ) { $found = true; } }
line( 'Consultant sees Open RFI in their court', $found, count( (array) $court ) . ' item(s)' );

// Subcontractor's court should NOT include it (RFIs have no sub steps).
wp_set_current_user( $sid );
list( $s, $subcourt ) = r( 'GET', '/em/v1/my-court' );
$leaked = false;
foreach ( (array) $subcourt as $it ) { if ( 'rfis' === $it['module'] ) { $leaked = true; } }
line( 'Subcontractor does NOT see RFI', ! $leaked, count( (array) $subcourt ) . ' item(s)' );

$wpdb->query( "DELETE FROM {$wpdb->prefix}em_rfis" );
echo "DONE\n";
