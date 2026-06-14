<?php
/** Quality & Safety batch: NCR disposition, inspection→deficiency link, incident→action-item. */
wp_set_current_user( 1 );
function r( $m, $route, $b = null ) {
	$q = new WP_REST_Request( $m, $route );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$res = rest_do_request( $q );
	return array( $res->get_status(), $res->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// NCR with disposition direction.
list( $s, $n ) = r( 'POST', '/em/v1/modules/non-conformance/records', array( 'ncr_no' => 'NCR-01', 'title' => 'Rebar spacing', 'description' => 'Spacing exceeds tol', 'severity' => 'Major' ) );
$nid = $n['id'] ?? 0;
list( $s1, $d ) = r( 'POST', "/em/v1/modules/non-conformance/records/$nid/transition", array( 'to' => 'Dispositioned', 'direction' => 'Rework' ) );
$ok = 200 === $s1 && 'Dispositioned' === ( $d['status'] ?? '' );
foreach ( array( 'Corrective Action', 'Verification', 'Closed' ) as $to ) {
	list( $st, $rr ) = r( 'POST', "/em/v1/modules/non-conformance/records/$nid/transition", array( 'to' => $to ) );
	if ( 200 !== $st ) { $ok = false; } else { $last = $rr['status']; }
}
line( 'NCR disposition→close', $ok && 'Closed' === ( $last ?? '' ), 'disposition=' . ( $d['direction'] ?? '?' ) );

// NCR → action item link.
list( $s, $sp ) = r( 'POST', "/em/v1/modules/non-conformance/records/$nid/spawn", array( 'target' => 'action-items' ) );
line( 'NCR → Action Item', ( 200 === $s || 201 === $s ) && ! empty( $sp['record']['id'] ), 'AI id=' . ( $sp['record']['id'] ?? '?' ) );

// Inspection (fail) → deficiency link.
list( $s, $insp ) = r( 'POST', '/em/v1/modules/inspections/records', array( 'inspection_no' => 'INS-01', 'title' => 'Pre-pour grid C', 'findings' => 'Missing dowels' ) );
$iid = $insp['id'] ?? 0;
list( $s, $sp2 ) = r( 'POST', "/em/v1/modules/inspections/records/$iid/spawn", array( 'target' => 'deficiencies' ) );
line( 'Inspection → Deficiency', ( 200 === $s || 201 === $s ) && ! empty( $sp2['record']['id'] ), 'def id=' . ( $sp2['record']['id'] ?? '?' ) );

// Incident with OSHA fields → action item.
list( $s, $inc ) = r( 'POST', '/em/v1/modules/incidents/records', array( 'incident_no' => 'INC-01', 'title' => 'Hand laceration', 'incident_type' => 'Recordable Injury', 'incident_date' => '2026-06-13T09:00', 'description' => 'Cut on metal stud', 'osha_recordable' => true, 'lost_days' => 2 ) );
$incid = $inc['id'] ?? 0;
list( $s, $sp3 ) = r( 'POST', "/em/v1/modules/incidents/records/$incid/spawn", array( 'target' => 'action-items' ) );
line( 'Incident (OSHA fields) → Action Item', ( 200 === $s || 201 === $s ) && ! empty( $sp3['record']['id'] ), 'recordable=' . ( $inc['osha_recordable'] ? 'yes' : 'no' ) . ' lost_days=' . ( $inc['lost_days'] ?? '?' ) );

// Deficiency ball-in-court.
list( $s, $df ) = r( 'POST', '/em/v1/modules/deficiencies/records', array( 'title' => 'Cracked tile', 'description' => 'Replace' ) );
$dfid = $df['id'] ?? 0; $okd = true;
foreach ( array( 'Work Required', 'Ready for Review', 'Closed' ) as $to ) {
	list( $st, $rr ) = r( 'POST', "/em/v1/modules/deficiencies/records/$dfid/transition", array( 'to' => $to ) );
	if ( 200 !== $st ) { $okd = false; } else { $lastd = $rr['status']; }
}
line( 'Deficiency ball-in-court', $okd && 'Closed' === ( $lastd ?? '' ), 'final=' . ( $lastd ?? '?' ) );

global $wpdb;
foreach ( array( 'non_conformance', 'inspections', 'incidents', 'deficiencies', 'action_items' ) as $t ) {
	$wpdb->query( "DELETE FROM {$wpdb->prefix}em_$t" );
}
echo "DONE\n";
