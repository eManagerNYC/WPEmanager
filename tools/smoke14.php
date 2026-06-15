<?php
/** First-class lookups: /users endpoint + combo fields store the chosen value. */
wp_set_current_user( 1 );
function r( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	$x = rest_do_request( $q );
	return array( $x->get_status(), $x->get_data() );
}
function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

// /users endpoint returns the admin.
list( $s, $users ) = r( 'GET', '/em/v1/users' );
$has_admin = false;
foreach ( (array) $users as $u ) { if ( 1 === $u['id'] ) { $has_admin = true; } }
line( '/users returns project users', 200 === $s && $has_admin, count( (array) $users ) . ' user(s)' );

// Seed a company; combo source is the companies module.
r( 'POST', '/em/v1/modules/companies/records', array( 'name' => 'BoardCo Drywall', 'type' => 'Subcontractor' ) );
list( $s, $cos ) = r( 'GET', '/em/v1/modules/companies/records?per_page=50' );
$names = array_map( fn( $c ) => $c['name'], $cos['records'] ?? array() );
line( 'Companies available for combo source', in_array( 'BoardCo Drywall', $names, true ), implode( ', ', $names ) );

// Combo field accepts and stores the chosen value (free text still works server-side).
list( $s, $sub ) = r( 'POST', '/em/v1/modules/submittals/records', array( 'title' => 'Stairs', 'subcontractor' => 'BoardCo Drywall' ) );
line( 'Combo value stored on record', 'BoardCo Drywall' === ( $sub['subcontractor'] ?? '' ), 'subcontractor=' . ( $sub['subcontractor'] ?? '?' ) );

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_submittals" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_companies" );
echo "DONE\n";
