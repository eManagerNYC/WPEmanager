<?php
/** Transition data-gating: blocked until required field is filled. */
wp_set_current_user( 1 );
function r( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$x = rest_do_request( $q );
	return array( $x->get_status(), $x->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// RFI: submit, then try to Answer without an answer (should be blocked), then fill + Answer.
list( $s, $rfi ) = r( 'POST', '/em/v1/modules/rfis/records', array( 'number' => 'RFI-G1', 'subject' => 'Beam size', 'question' => 'What size?' ) );
$id = $rfi['id'] ?? 0;
r( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Open' ) );

list( $sb, $blocked ) = r( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Answered' ) );
line( 'Answer blocked without answer (422)', 422 === $sb, 'msg=' . ( is_array( $blocked ) ? ( $blocked['message'] ?? '' ) : '' ) );

// Fill the answer, then it should pass.
r( 'PUT', "/em/v1/modules/rfis/records/$id", array( 'answer' => 'W12x26' ) );
list( $sok, $ans ) = r( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Answered' ) );
line( 'Answer allowed once filled', 200 === $sok && 'Answered' === ( $ans['status'] ?? '' ), 'status=' . ( $ans['status'] ?? '?' ) );

// Change order: submit blocked without amount.
list( $s, $co ) = r( 'POST', '/em/v1/modules/change-orders/records', array( 'co_no' => 'CO-G1', 'title' => 'Extra doors' ) );
$cid = $co['id'] ?? 0;
list( $sc ) = r( 'POST', "/em/v1/modules/change-orders/records/$cid/transition", array( 'to' => 'Submitted' ) );
r( 'PUT', "/em/v1/modules/change-orders/records/$cid", array( 'amount' => 15000 ) );
list( $sc2, $co2 ) = r( 'POST', "/em/v1/modules/change-orders/records/$cid/transition", array( 'to' => 'Submitted' ) );
line( 'CO submit gated on amount', 422 === $sc && 200 === $sc2, 'blocked=' . ( 422 === $sc ? 'yes' : 'no' ) . ' thenOk=' . ( 200 === $sc2 ? 'yes' : 'no' ) );

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_rfis" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_change_orders" );
echo "DONE\n";
