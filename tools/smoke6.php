<?php
/** Closeout batch: completion→warranty, Cx→deficiency, O&M ball-in-court, asset→warranty. */
wp_set_current_user( 1 );
function r( $m, $route, $b = null ) {
	$q = new WP_REST_Request( $m, $route );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$res = rest_do_request( $q );
	return array( $res->get_status(), $res->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// Completion certificate → execute → start warranty.
list( $s, $c ) = r( 'POST', '/em/v1/modules/completion-certificates/records', array( 'cert_no' => 'SC-1', 'cert_type' => 'Substantial Completion', 'description' => 'Building shell', 'issue_date' => '2026-06-13' ) );
$cid = $c['id'] ?? 0;
list( $s1 ) = r( 'POST', "/em/v1/modules/completion-certificates/records/$cid/transition", array( 'to' => 'Issued' ) );
list( $s2, $ce ) = r( 'POST', "/em/v1/modules/completion-certificates/records/$cid/transition", array( 'to' => 'Executed' ) );
line( 'Cert Draft→Issued→Executed', 200 === $s1 && 200 === $s2 && 'Executed' === ( $ce['status'] ?? '' ) );
list( $s, $sp ) = r( 'POST', "/em/v1/modules/completion-certificates/records/$cid/spawn", array( 'target' => 'warranties' ) );
line( 'Completion → Warranty (start trigger)', ( 200 === $s || 201 === $s ) && ! empty( $sp['record']['id'] ), 'warranty start=' . ( $sp['record']['start_date'] ?? '?' ) );

// O&M ball-in-court.
list( $s, $om ) = r( 'POST', '/em/v1/modules/om-manuals/records', array( 'title' => 'AHU O&M' ) );
$omid = $om['id'] ?? 0; $ok = true;
foreach ( array( 'Received', 'Under Review', 'Accepted', 'Transmitted to Owner' ) as $to ) {
	list( $st, $rr ) = r( 'POST', "/em/v1/modules/om-manuals/records/$omid/transition", array( 'to' => $to ) );
	if ( 200 !== $st ) { $ok = false; } else { $last = $rr['status']; }
}
line( 'O&M Requested→…→Transmitted', $ok && 'Transmitted to Owner' === ( $last ?? '' ), 'final=' . ( $last ?? '?' ) );

// Commissioning issue → deficiency.
list( $s, $cx ) = r( 'POST', '/em/v1/modules/commissioning/records', array( 'system' => 'Chilled water', 'findings' => 'Pump fails to ramp' ) );
$cxid = $cx['id'] ?? 0;
list( $s, $sp2 ) = r( 'POST', "/em/v1/modules/commissioning/records/$cxid/spawn", array( 'target' => 'deficiencies' ) );
line( 'Commissioning → Deficiency', ( 200 === $s || 201 === $s ) && ! empty( $sp2['record']['id'] ), 'def id=' . ( $sp2['record']['id'] ?? '?' ) );

// Asset → warranty.
list( $s, $a ) = r( 'POST', '/em/v1/modules/asset-register/records', array( 'asset_tag' => 'AHU-1', 'asset_name' => 'Air handler 1', 'manufacturer' => 'Trane', 'warranty_end' => '2028-06-13' ) );
$aid = $a['id'] ?? 0;
list( $s, $sp3 ) = r( 'POST', "/em/v1/modules/asset-register/records/$aid/spawn", array( 'target' => 'warranties' ) );
line( 'Asset → Warranty (register)', ( 200 === $s || 201 === $s ) && ! empty( $sp3['record']['id'] ), 'warrantor=' . ( $sp3['record']['company'] ?? '?' ) );

global $wpdb;
foreach ( array( 'completion_certificates', 'warranties', 'om_manuals', 'commissioning', 'deficiencies', 'asset_register' ) as $t ) {
	$wpdb->query( "DELETE FROM {$wpdb->prefix}em_$t" );
}
echo "DONE\n";
