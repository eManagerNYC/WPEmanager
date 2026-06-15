<?php
/** Saved views: save → get → delete, scoped per user and module. */
wp_set_current_user( 1 );
function r( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$x = rest_do_request( $q );
	return array( $x->get_status(), $x->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

delete_user_meta( 1, 'em_saved_views' );

r( 'POST', '/em/v1/views', array( 'op' => 'save', 'module' => 'rfis', 'name' => 'Open & overdue', 'params' => array( 'status' => 'Open', 'sort' => 'date_required', 'order' => 'asc' ) ) );
r( 'POST', '/em/v1/views', array( 'op' => 'save', 'module' => 'rfis', 'name' => 'All closed', 'params' => array( 'status' => 'Closed' ) ) );

list( $s, $all ) = r( 'GET', '/em/v1/views' );
$rfi = (array) ( $all['rfis'] ?? array() );
line( 'Saved views returned for module', 200 === $s && 2 === count( $rfi ), count( $rfi ) . ' view(s)' );
$names = array_map( fn( $v ) => $v['name'], $rfi );
line( 'View params persisted', in_array( 'Open & overdue', $names, true ) && 'Open' === ( $rfi[0]['params']['status'] ?? '' ), implode( ', ', $names ) );

// Re-saving the same name replaces (no duplicate).
r( 'POST', '/em/v1/views', array( 'op' => 'save', 'module' => 'rfis', 'name' => 'All closed', 'params' => array( 'status' => 'Closed', 'order' => 'desc' ) ) );
list( $s, $all2 ) = r( 'GET', '/em/v1/views' );
line( 'Re-save replaces (no dupe)', 2 === count( (array) $all2['rfis'] ), count( (array) $all2['rfis'] ) . ' view(s)' );

// Delete one.
r( 'POST', '/em/v1/views', array( 'op' => 'delete', 'module' => 'rfis', 'name' => 'All closed' ) );
list( $s, $all3 ) = r( 'GET', '/em/v1/views' );
line( 'Delete removes the view', 1 === count( (array) $all3['rfis'] ), count( (array) $all3['rfis'] ) . ' view(s)' );

delete_user_meta( 1, 'em_saved_views' );
echo "DONE\n";
