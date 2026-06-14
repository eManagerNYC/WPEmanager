<?php
/** Preconstruction batch: bid pipeline → award → commitment; estimate → budget. */
wp_set_current_user( 1 );
function r( $m, $route, $b = null ) {
	$q = new WP_REST_Request( $m, $route );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$res = rest_do_request( $q );
	return array( $res->get_status(), $res->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// Bid package → spawn solicitation.
list( $s, $bp ) = r( 'POST', '/em/v1/modules/bid-packages/records', array( 'number' => 'BP-09', 'title' => 'Drywall package', 'scope' => 'GWB + framing', 'estimate' => 500000 ) );
$bpid = $bp['id'] ?? 0;
list( $s1 ) = r( 'POST', "/em/v1/modules/bid-packages/records/$bpid/transition", array( 'to' => 'Out to Bid' ) );
list( $s, $sol ) = r( 'POST', "/em/v1/modules/bid-packages/records/$bpid/spawn", array( 'target' => 'bid-solicitations' ) );
line( 'Bid Package → ITB (out to bid)', 200 === $s1 && ! empty( $sol['record']['id'] ), 'pkg=' . ( $sol['record']['bid_package'] ?? '?' ) );

// Bid submission → award → commitment.
list( $s, $sub ) = r( 'POST', '/em/v1/modules/bid-submissions/records', array( 'bidder' => 'BoardCo', 'bid_package' => 'BP-09', 'base_bid' => 480000 ) );
$sid = $sub['id'] ?? 0; $ok = true;
foreach ( array( 'Submitted', 'Shortlisted', 'Awarded' ) as $to ) {
	list( $st, $rr ) = r( 'POST', "/em/v1/modules/bid-submissions/records/$sid/transition", array( 'to' => $to ) );
	if ( 200 !== $st ) { $ok = false; } else { $last = $rr['status']; }
}
line( 'Bid Invited→Submitted→Shortlisted→Awarded', $ok && 'Awarded' === ( $last ?? '' ), 'final=' . ( $last ?? '?' ) );
list( $s, $cm ) = r( 'POST', "/em/v1/modules/bid-submissions/records/$sid/spawn", array( 'target' => 'commitments' ) );
line( 'Award → Subcontract commitment', ( 200 === $s || 201 === $s ) && ! empty( $cm['record']['id'] ), 'vendor=' . ( $cm['record']['vendor'] ?? '?' ) . ' value=' . ( $cm['record']['value'] ?? '?' ) );

// Estimate → budget line.
list( $s, $est ) = r( 'POST', '/em/v1/modules/estimates/records', array( 'estimate_no' => 'EST-1', 'title' => 'Sitework', 'total' => 1200000 ) );
$eid = $est['id'] ?? 0;
list( $s, $bud ) = r( 'POST', "/em/v1/modules/estimates/records/$eid/spawn", array( 'target' => 'budget-forecast' ) );
line( 'Estimate → Budget line (seed)', ( 200 === $s || 201 === $s ) && ! empty( $bud['record']['id'] ), 'budget=' . ( $bud['record']['original_budget'] ?? '?' ) );

global $wpdb;
foreach ( array( 'bid_packages', 'bid_solicitations', 'bid_submissions', 'commitments', 'estimates', 'budget_forecast' ) as $t ) {
	$wpdb->query( "DELETE FROM {$wpdb->prefix}em_$t" );
}
echo "DONE\n";
