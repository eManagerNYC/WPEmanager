<?php
/** Auto-numbering: blank number fields get sequential values per module/project. */
wp_set_current_user( 1 );
function r( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$x = rest_do_request( $q );
	return array( $x->get_status(), $x->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_rfis" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_change_orders" );
delete_option( 'em_seq_rfis_default' );
delete_option( 'em_seq_change-orders_default' );

list( $s, $a ) = r( 'POST', '/em/v1/modules/rfis/records', array( 'subject' => 'First', 'question' => 'Q' ) );
list( $s, $b ) = r( 'POST', '/em/v1/modules/rfis/records', array( 'subject' => 'Second', 'question' => 'Q' ) );
line( 'RFIs auto-number sequentially', ( $a['number'] ?? '' ) === 'RFI-001' && ( $b['number'] ?? '' ) === 'RFI-002', ( $a['number'] ?? '?' ) . ', ' . ( $b['number'] ?? '?' ) );

// Explicit number is respected (not overwritten).
list( $s, $c ) = r( 'POST', '/em/v1/modules/rfis/records', array( 'number' => 'RFI-CUSTOM', 'subject' => 'Third', 'question' => 'Q' ) );
line( 'Explicit number preserved', ( $c['number'] ?? '' ) === 'RFI-CUSTOM', $c['number'] ?? '?' );

// Different module has its own sequence + prefix.
list( $s, $co ) = r( 'POST', '/em/v1/modules/change-orders/records', array( 'title' => 'CO one', 'amount' => 1000 ) );
line( 'Change Orders use own prefix/sequence', ( $co['co_no'] ?? '' ) === 'CO-001', $co['co_no'] ?? '?' );

$wpdb->query( "DELETE FROM {$wpdb->prefix}em_rfis" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_change_orders" );
echo "DONE\n";
