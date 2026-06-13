<?php
wp_set_current_user( 1 );
global $wpdb;

// Clean + seed two rows directly through the data layer.
EM_DB::insert( 'em_rfis', array( 'number' => 'RFI-100', 'subject' => 'Beam clash at grid C-4', 'question' => 'Confirm elevation', 'status' => 'Draft' ) );
EM_DB::insert( 'em_rfis', array( 'number' => 'RFI-101', 'subject' => 'Door hardware', 'question' => 'Spec?', 'status' => 'Open' ) );

$raw = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}em_rfis" );
echo "raw count wp_em_rfis = $raw\n";

$all = EM_DB::select( 'em_rfis', array() );
echo 'select(no params) total=' . ( is_wp_error( $all ) ? $all->get_error_message() : $all['total'] ) . ' rows=' . ( is_wp_error( $all ) ? 0 : count( $all['data'] ) ) . "\n";

$cols = EM_Modules::search_columns( EM_Modules::instance()->get( 'rfis' ) );
echo 'search_cols = ' . implode( ',', $cols ) . "\n";

$srch = EM_DB::select( 'em_rfis', array( 'search' => 'clash', 'search_cols' => $cols ) );
echo 'select(search=clash) total=' . ( is_wp_error( $srch ) ? $srch->get_error_message() : $srch['total'] ) . "\n";
if ( $wpdb->last_error ) {
	echo 'LAST ERROR: ' . $wpdb->last_error . "\n";
}

// Now via the REST collection using set_query_params (the correct way).
$req = new WP_REST_Request( 'GET', '/em/v1/modules/rfis/records' );
$req->set_query_params( array( 'search' => 'clash', 'per_page' => 5 ) );
$res = rest_do_request( $req );
echo 'REST list (set_query_params) status=' . $res->get_status() . ' total=' . ( $res->get_data()['total'] ?? 'n/a' ) . "\n";

// Cleanup.
$wpdb->query( "DELETE FROM {$wpdb->prefix}em_rfis" );
echo "DONE\n";
