<?php
/**
 * eManager end-to-end smoke test — run with:
 *   php wp-cli.phar eval-file tools/smoke.php --path=C:\laragon\www\emtest
 * Dispatches real REST requests through rest_do_request() as the admin user.
 */

wp_set_current_user( 1 );

function em_req( $method, $route, $body = null ) {
	$req = new WP_REST_Request( $method, $route );
	if ( null !== $body ) {
		$req->set_header( 'Content-Type', 'application/json' );
		$req->set_body( wp_json_encode( $body ) );
	}
	$res = rest_do_request( $req );
	return array( $res->get_status(), $res->get_data() );
}

function em_line( $label, $ok, $extra = '' ) {
	echo ( $ok ? 'PASS' : 'FAIL' ) . '  ' . $label . ( '' !== $extra ? '  — ' . $extra : '' ) . "\n";
}

// 1. Boot.
list( $s, $boot ) = em_req( 'GET', '/em/v1/boot' );
em_line( 'boot', 200 === $s && ! empty( $boot['registry'] ), 'sections=' . ( is_array( $boot['registry'] ?? null ) ? count( $boot['registry'] ) : 0 ) );

// 2. Create an RFI.
list( $s, $rec ) = em_req( 'POST', '/em/v1/modules/rfis/records', array(
	'number'  => 'RFI-001',
	'subject' => 'Beam clash at grid C-4',
	'question' => 'Confirm beam elevation.',
) );
$id = $rec['id'] ?? 0;
em_line( 'create RFI', 201 === $s || 200 === $s, 'id=' . $id . ' status=' . ( $rec['status'] ?? '?' ) );

// 3. Read it back.
list( $s, $got ) = em_req( 'GET', "/em/v1/modules/rfis/records/$id" );
em_line( 'read RFI', 200 === $s && ( $got['subject'] ?? '' ) === 'Beam clash at grid C-4', 'transitions=' . count( $got['_transitions'] ?? array() ) );

// 4. List with search.
list( $s, $list ) = em_req( 'GET', '/em/v1/modules/rfis/records?search=clash&per_page=5' );
em_line( 'list+search', 200 === $s && ( $list['total'] ?? 0 ) >= 1, 'total=' . ( $list['total'] ?? 0 ) );

// 5. Workflow transition Draft -> Open (admin passes the GC party gate).
list( $s, $tr ) = em_req( 'POST', "/em/v1/modules/rfis/records/$id/transition", array( 'to' => 'Open' ) );
em_line( 'transition Draft->Open', 200 === $s && ( $tr['status'] ?? '' ) === 'Open', 'status=' . ( $tr['status'] ?? '?' ) );

// 6. Activity log recorded the create + transition.
list( $s, $act ) = em_req( 'GET', "/em/v1/modules/rfis/records/$id/activity" );
em_line( 'activity log', 200 === $s && count( (array) $act ) >= 2, 'entries=' . count( (array) $act ) );

// 7. Comment.
list( $s, $cm ) = em_req( 'POST', "/em/v1/modules/rfis/records/$id/comments", array( 'body' => 'Please expedite.' ) );
em_line( 'add comment', ( 200 === $s || 201 === $s ) && ! empty( $cm['id'] ) );

// 8. Spawn / link: change-event -> potential-change.
list( $s, $ce ) = em_req( 'POST', '/em/v1/modules/change-events/records', array( 'event_no' => 'CE-001', 'title' => 'Owner-added canopy', 'rom_cost' => 25000 ) );
$ceid = $ce['id'] ?? 0;
list( $s2, $sp ) = em_req( 'POST', "/em/v1/modules/change-events/records/$ceid/spawn", array( 'target' => 'potential-changes' ) );
em_line( 'spawn linked record', ( 200 === $s2 || 201 === $s2 ) && ! empty( $sp['record']['id'] ), 'new=' . ( $sp['module'] ?? '?' ) . ':' . ( $sp['record']['id'] ?? '?' ) );

// 9. Schedule of Values + cost summary.
em_req( 'POST', '/em/v1/modules/schedule-of-values/records', array( 'item_no' => '1', 'description' => 'General conditions', 'scheduled_value' => 500000, 'previous_completed' => 100000, 'this_period' => 50000 ) );
list( $s, $cs ) = em_req( 'GET', '/em/v1/reports/cost-summary' );
$figs = array();
foreach ( ( $cs['figures'] ?? array() ) as $f ) { $figs[ $f['key'] ] = $f['value']; }
em_line( 'cost summary', 200 === $s && isset( $figs['revised_budget'] ), 'revised=' . ( $figs['revised_budget'] ?? '?' ) );

// 10. Stats roll-up.
list( $s, $stats ) = em_req( 'GET', '/em/v1/reports/stats' );
em_line( 'reports stats', 200 === $s && is_array( $stats ) && count( $stats ) > 50, 'modules=' . ( is_array( $stats ) ? count( $stats ) : 0 ) );

// 11. Delete the RFI (ownership: admin created it).
list( $s, $del ) = em_req( 'DELETE', "/em/v1/modules/rfis/records/$id" );
em_line( 'delete RFI', 200 === $s && ! empty( $del['deleted'] ) );

echo "DONE\n";
