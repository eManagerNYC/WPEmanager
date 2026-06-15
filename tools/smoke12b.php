<?php
/**
 * Verifies the Media Library integration the upload endpoint performs after
 * wp_handle_upload (which itself is core's standard, HTTP-only primitive).
 * Uses wp_handle_sideload (copy semantics) so it runs under WP-CLI.
 */
wp_set_current_user( 1 );
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function line( $l, $ok, $x = '' ) { echo ( $ok ? 'PASS' : 'FAIL' ) . "  $l" . ( $x ? "  — $x" : '' ) . "\n"; }

$png = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/IjPAAAAAElFTkSuQmCC' );
$tmp = wp_tempnam( 'em-upload.png' );
file_put_contents( $tmp, $png );

// Same allowlist the endpoint enforces.
$ref = new ReflectionClass( 'EM_Api' );
$mimes = $ref->getConstant( 'UPLOAD_MIMES' );
line( 'Endpoint declares a mime allowlist (no PHP)', is_array( $mimes ) && ! isset( $mimes['php'] ) && isset( $mimes['pdf'] ), count( $mimes ) . ' types' );

$good = array( 'name' => 'plan.png', 'type' => 'image/png', 'tmp_name' => $tmp, 'error' => 0, 'size' => strlen( $png ) );
$moved = wp_handle_sideload( $good, array( 'test_form' => false, 'mimes' => $mimes ) );
line( 'File stored in uploads', empty( $moved['error'] ) && ! empty( $moved['url'] ), $moved['url'] ?? ( $moved['error'] ?? '?' ) );

$att = wp_insert_attachment(
	array( 'post_mime_type' => $moved['type'], 'post_title' => 'plan.png', 'post_status' => 'inherit' ),
	$moved['file']
);
wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $moved['file'] ) );
line( 'Registered as Media attachment', is_int( $att ) && $att > 0 && 'attachment' === get_post_type( $att ), 'id=' . $att . ' url=' . wp_get_attachment_url( $att ) );

// A disallowed type (php) is rejected by the same allowlist.
$bad = wp_tempnam( 'em-bad.php' );
file_put_contents( $bad, '<?php echo 1;' );
$badfile = array( 'name' => 'evil.php', 'type' => 'application/x-php', 'tmp_name' => $bad, 'error' => 0, 'size' => 12 );
$badmove = wp_handle_sideload( $badfile, array( 'test_form' => false, 'mimes' => $mimes ) );
line( 'Disallowed .php upload rejected', ! empty( $badmove['error'] ), $badmove['error'] ?? 'NOT REJECTED' );

wp_delete_attachment( $att, true );
@unlink( $bad );
echo "DONE\n";
