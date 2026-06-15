<?php
/** Notifications: a transition emails the owner + ball-in-court party (capturing wp_mail). */
wp_set_current_user( 1 );

// Create a consultant user who holds the RFI "Open" ball-in-court.
$consultant = get_user_by( 'email', 'consultant@example.com' );
if ( ! $consultant ) {
	$cid = wp_insert_user( array( 'user_login' => 'rfi_consultant', 'user_email' => 'consultant@example.com', 'user_pass' => wp_generate_password(), 'role' => 'em_editor' ) );
} else {
	$cid = $consultant->ID;
}
update_user_meta( $cid, 'em_party_role', 'consultant' );
update_user_meta( $cid, 'em_email_notify', '1' );

// Capture outgoing mail instead of sending.
$GLOBALS['em_caught'] = array();
add_filter( 'pre_wp_mail', function ( $null, $atts ) {
	$GLOBALS['em_caught'][] = is_array( $atts['to'] ) ? implode( ',', $atts['to'] ) : $atts['to'];
	return true; // Short-circuit actual send.
}, 10, 2 );

function r( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$x = rest_do_request( $q );
	return array( $x->get_status(), $x->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// Admin (user 1) creates an RFI and submits it → status becomes Open (consultant's court).
list( $s, $rfi ) = r( 'POST', '/em/v1/modules/rfis/records', array( 'number' => 'RFI-N1', 'subject' => 'Notify test', 'question' => 'Q?' ) );
$id = $rfi['id'] ?? 0;
$GLOBALS['em_caught'] = array();
list( $st ) = r( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Open' ) );

$caught = implode( ' | ', $GLOBALS['em_caught'] );
$consultant_notified = false !== strpos( $caught, 'consultant@example.com' );
line( 'Transition emails ball-in-court consultant', 200 === $st && $consultant_notified, 'sent to: ' . ( $caught ?: '(none)' ) );

// Recipients helper excludes the actor (admin acted, admin owns it → not emailed).
$mod = EM_Modules::instance()->get( 'rfis' );
$rec = EM_DB::get( 'em_rfis', $id );
$recips = EM_Notify::recipients_for( $rec, $mod );
$has_actor = isset( $recips[ 1 ] );
line( 'Actor excluded from own notification', ! $has_actor, 'recipient ids: ' . implode( ',', array_keys( $recips ) ) );

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_rfis" );
echo "DONE\n";
