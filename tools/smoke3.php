<?php
/** Field batch: punch list ball-in-court + daily report submit/approve. */
wp_set_current_user( 1 );
function r( $m, $route, $b = null ) {
	$q = new WP_REST_Request( $m, $route );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$res = rest_do_request( $q );
	return array( $res->get_status(), $res->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// Punch list ball-in-court.
list( $s, $p ) = r( 'POST', '/em/v1/modules/punchlist/records', array( 'title' => 'Touch-up paint rm 204', 'description' => 'Scuffs', 'responsible' => 'ABC Painting', 'backcharge' => 250 ) );
$id = $p['id'] ?? 0;
line( 'Punch create (backcharge field)', ( 200 === $s || 201 === $s ), 'status=' . ( $p['status'] ?? '?' ) . ' backcharge=' . ( $p['backcharge'] ?? '?' ) );
$ok = true; $last = $p['status'] ?? '';
foreach ( array( 'Work Required', 'Ready for Review', 'Verified' ) as $to ) {
	list( $st, $rr ) = r( 'POST', "/em/v1/modules/punchlist/records/$id/transition", array( 'to' => $to ) );
	if ( 200 !== $st ) { $ok = false; }
	$last = $rr['status'] ?? $last;
}
line( 'Punch Open→Work Required→Ready→Verified', $ok && 'Verified' === $last, 'final=' . $last );

// Daily report submit/approve.
list( $s, $d ) = r( 'POST', '/em/v1/modules/daily-reports/records', array( 'report_date' => '2026-06-13', 'superintendent' => 'J. Doe', 'work_performed' => 'Poured slab on grade' ) );
$did = $d['id'] ?? 0;
list( $s1 ) = r( 'POST', "/em/v1/modules/daily-reports/records/$did/transition", array( 'to' => 'Submitted' ) );
list( $s2, $da ) = r( 'POST', "/em/v1/modules/daily-reports/records/$did/transition", array( 'to' => 'Approved' ) );
line( 'Daily Draft→Submitted→Approved', 200 === $s1 && 200 === $s2 && 'Approved' === ( $da['status'] ?? '' ), 'final=' . ( $da['status'] ?? '?' ) );

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_punchlist" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_daily_reports" );
echo "DONE\n";
