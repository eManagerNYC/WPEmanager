<?php
/** Bespoke RFI + Submittal workflow test. */
wp_set_current_user( 1 );

function r( $m, $route, $b = null ) {
	$q = new WP_REST_Request( $m, $route );
	if ( null !== $b ) {
		$q->set_header( 'Content-Type', 'application/json' );
		$q->set_body( wp_json_encode( $b ) );
	}
	$res = rest_do_request( $q );
	return array( $res->get_status(), $res->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// RFI with new fields.
list( $s, $rfi ) = r( 'POST', '/em/v1/modules/rfis/records', array(
	'number' => 'RFI-900', 'subject' => 'Curtain wall anchor', 'discipline' => 'Structural',
	'priority' => 'High', 'question' => 'Embed plate size?', 'cost_impact' => true, 'cost_impact_amount' => 12000,
) );
$id = $rfi['id'] ?? 0;
line( 'RFI create w/ new fields', 201 === $s || 200 === $s, 'discipline=' . ( $rfi['discipline'] ?? '?' ) . ' cost=' . ( $rfi['cost_impact_amount'] ?? '?' ) );

// Walk the ball-in-court lifecycle.
list( $s, $a ) = r( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Open' ) );
list( $s2, $b ) = r( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Answered' ) );
list( $s3, $c ) = r( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Closed' ) );
line( 'RFI Draft→Open→Answered→Closed', 200 === $s && 200 === $s2 && 200 === $s3, 'final=' . ( $c['status'] ?? '?' ) );

// Spawn a change event from an RFI (impact path).
list( $s, $sp ) = r( 'POST', "/em/v1/modules/rfis/records/$id/spawn", array( 'target' => 'change-events' ) );
line( 'RFI → Change Event link', ( 200 === $s || 201 === $s ) && ! empty( $sp['record']['id'] ), 'CE id=' . ( $sp['record']['id'] ?? '?' ) );

// Submittal full disposition path.
list( $s, $sub ) = r( 'POST', '/em/v1/modules/submittals/records', array(
	'number' => 'SUB-050', 'submittal_type' => 'Shop Drawing', 'title' => 'Steel stairs', 'spec_section' => '05 51 00',
) );
$sid = $sub['id'] ?? 0;
$ok = true; $last = $sub['status'] ?? '';
foreach ( array(
	array( 'Submitted', null ), array( 'GC Review', null ), array( 'A/E Review', null ),
	array( 'Returned', 'Approved as Noted' ), array( 'Closed', null ),
) as $step ) {
	$body = array( 'to' => $step[0] );
	if ( $step[1] ) { $body['direction'] = $step[1]; }
	list( $st, $rr ) = r( 'POST', "/em/v1/modules/submittals/records/$sid/transition", $body );
	if ( 200 !== $st ) { $ok = false; }
	$last = $rr['status'] ?? $last;
	if ( isset( $rr['direction'] ) ) { $disp = $rr['direction']; }
}
line( 'Submittal Draft→…→Closed w/ disposition', $ok && 'Closed' === $last, 'disposition=' . ( $disp ?? '?' ) );

// Cleanup.
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_rfis" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_submittals" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_change_events" );
echo "DONE\n";
