<?php
/** Cost batch: PCO→CO chain, commitment→sub-invoice, direct-cost approval. */
wp_set_current_user( 1 );
function r( $m, $route, $b = null ) {
	$q = new WP_REST_Request( $m, $route );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$res = rest_do_request( $q );
	return array( $res->get_status(), $res->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// PCO pipeline → convert to CO.
list( $s, $p ) = r( 'POST', '/em/v1/modules/potential-changes/records', array( 'pco_no' => 'PCO-01', 'title' => 'Added canopy', 'rom_estimate' => 18000 ) );
$pid = $p['id'] ?? 0; $ok = true;
foreach ( array( 'Pricing', 'Submitted', 'Approved', 'Converted to CO' ) as $to ) {
	list( $st, $rr ) = r( 'POST', "/em/v1/modules/potential-changes/records/$pid/transition", array( 'to' => $to ) );
	if ( 200 !== $st ) { $ok = false; } else { $last = $rr['status']; }
}
line( 'PCO Identified→…→Converted to CO', $ok && 'Converted to CO' === ( $last ?? '' ), 'final=' . ( $last ?? '?' ) );
list( $s, $sp ) = r( 'POST', "/em/v1/modules/potential-changes/records/$pid/spawn", array( 'target' => 'change-orders' ) );
line( 'PCO → Change Order link', ( 200 === $s || 201 === $s ) && ! empty( $sp['record']['id'] ), 'CO amount=' . ( $sp['record']['amount'] ?? '?' ) );

// Direct cost approval.
list( $s, $dc ) = r( 'POST', '/em/v1/modules/direct-costs/records', array( 'cost_type' => 'Invoice', 'vendor' => 'Acme Concrete', 'amount' => 9500 ) );
$dcid = $dc['id'] ?? 0;
list( $s1 ) = r( 'POST', "/em/v1/modules/direct-costs/records/$dcid/transition", array( 'to' => 'Approved' ) );
list( $s2, $dd ) = r( 'POST', "/em/v1/modules/direct-costs/records/$dcid/transition", array( 'to' => 'Paid' ) );
line( 'Direct cost Pending→Approved→Paid', 200 === $s1 && 200 === $s2 && 'Paid' === ( $dd['status'] ?? '' ), 'final=' . ( $dd['status'] ?? '?' ) );

// Commitment → subcontractor invoice link.
list( $s, $cm ) = r( 'POST', '/em/v1/modules/commitments/records', array( 'commitment_no' => 'SC-100', 'title' => 'Drywall', 'commitment_type' => 'Subcontract', 'vendor' => 'BoardCo', 'value' => 240000, 'cost_code' => '09 20 00' ) );
$cmid = $cm['id'] ?? 0;
list( $s, $sp2 ) = r( 'POST', "/em/v1/modules/commitments/records/$cmid/spawn", array( 'target' => 'subcontractor-invoices' ) );
line( 'Commitment → Sub Invoice (coded)', ( 200 === $s || 201 === $s ) && ! empty( $sp2['record']['id'] ), 'cost_code=' . ( $cm['cost_code'] ?? '?' ) . ' inv.commitment=' . ( $sp2['record']['commitment_no'] ?? '?' ) );

global $wpdb;
foreach ( array( 'potential_changes', 'change_orders', 'direct_costs', 'commitments', 'subcontractor_invoices' ) as $t ) {
	$wpdb->query( "DELETE FROM {$wpdb->prefix}em_$t" );
}
echo "DONE\n";
