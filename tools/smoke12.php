<?php
/** Attachments: upload a file via the endpoint → Media Library URL returned. */
wp_set_current_user( 1 );

// Make a tiny real PNG in the system temp dir and feed it as an upload.
$png = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/IjPAAAAAElFTkSuQmCC' );
$tmp = wp_tempnam( 'em-test.png' );
file_put_contents( $tmp, $png );

$_FILES = array();
$file = array(
	'name'     => 'site-photo.png',
	'type'     => 'image/png',
	'tmp_name' => $tmp,
	'error'    => 0,
	'size'     => strlen( $png ),
);

// Let wp_handle_upload accept our non-HTTP test file.
add_filter( 'wp_handle_upload_overrides', function ( $o ) { $o['action'] = 'wp_handle_sideload'; return $o; } );

$req = new WP_REST_Request( 'POST', '/em/v1/upload' );
$req->set_file_params( array( 'file' => $file ) );
$res = rest_do_request( $req );
$data = $res->get_data();

function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

$ok = $res->get_status() < 300 && ! empty( $data['url'] ) && ! empty( $data['id'] );
line( 'Upload returns Media Library URL', $ok, ( $ok ? $data['url'] : wp_json_encode( $data ) ) );

if ( $ok ) {
	$is_attachment = 'attachment' === get_post_type( $data['id'] );
	line( 'File registered as a Media attachment', $is_attachment, 'attachment_id=' . $data['id'] );
	wp_delete_attachment( $data['id'], true );
}

echo "DONE\n";
