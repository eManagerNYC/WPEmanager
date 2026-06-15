<?php
wp_set_current_user( 1 );
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_companies" );

function rq( $m, $rt, $b = null ) {
	$q = new WP_REST_Request( $m, $rt );
	if ( null !== $b ) { $q->set_header( 'Content-Type', 'application/json' ); $q->set_body( wp_json_encode( $b ) ); }
	return rest_do_request( $q );
}
$c = rq( 'POST', '/em/v1/modules/companies/records', array( 'name' => 'BoardCo Drywall', 'type' => 'Subcontractor' ) );
echo 'create status: ' . $c->get_status() . ' id: ' . ( $c->get_data()['id'] ?? '?' ) . "\n";
$l = rq( 'GET', '/em/v1/modules/companies/records' );
$d = $l->get_data();
echo 'list total: ' . ( $d['total'] ?? '?' ) . "\n";
foreach ( ( $d['records'] ?? array() ) as $row ) {
	echo ' - ' . $row['name'] . "\n";
}
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_companies" );
echo "DONE\n";
