<?php
/** Related Records: parent + children surface on both ends of a spawn. */
wp_set_current_user( 1 );
function r( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$x = rest_do_request( $q );
	return array( $x->get_status(), $x->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

list( $s, $p ) = r( 'POST', '/em/v1/modules/potential-changes/records', array( 'pco_no' => 'PCO-77', 'title' => 'Skylight add', 'rom_estimate' => 30000 ) );
$pid = $p['id'] ?? 0;
list( $s, $sp ) = r( 'POST', "/em/v1/modules/potential-changes/records/$pid/spawn", array( 'target' => 'change-orders' ) );
$coid = $sp['record']['id'] ?? 0;

list( $s, $pco ) = r( 'GET', "/em/v1/modules/potential-changes/records/$pid" );
$kids = $pco['_links']['children'] ?? array();
line( 'Parent shows spawned child', count( $kids ) >= 1 && 'change-orders' === $kids[0]['module'], 'child=' . ( $kids[0]['title'] ?? '?' ) . ' (' . ( $kids[0]['module'] ?? '?' ) . ')' );

list( $s, $co ) = r( 'GET', "/em/v1/modules/change-orders/records/$coid" );
$par = $co['_links']['parent'] ?? null;
line( 'Child shows parent', $par && 'potential-changes' === $par['module'], 'parent=' . ( $par['title'] ?? '?' ) . ' (' . ( $par['module'] ?? '?' ) . ')' );

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_potential_changes" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_change_orders" );
echo "DONE\n";
