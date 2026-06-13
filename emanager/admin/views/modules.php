<?php
/**
 * Admin: Modules — list installed modules, install from ZIP.
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- $_GET values below are read-only status messages; the install itself is nonce-checked in EM_Admin::install_module().

$em_registry = EM_Modules::instance()->registry();
$em_all      = EM_Modules::instance()->all();
?>
<div class="wrap em-admin">
	<h1><?php esc_html_e( 'eManager Modules', 'emanager' ); ?></h1>

	<?php if ( isset( $_GET['em_msg'] ) ) : ?>
		<?php if ( 'installed' === $_GET['em_msg'] ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Module installed and its database table created automatically.', 'emanager' ); ?></p></div>
		<?php elseif ( 'error' === $_GET['em_msg'] ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['detail'] ?? __( 'Install failed.', 'emanager' ) ) ) ); ?></p></div>
		<?php endif; ?>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Install module from ZIP', 'emanager' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'A module ZIP contains one folder with a module.json manifest (see docs/MODULE-DEVELOPMENT.md). PHP files are not allowed in module packages.', 'emanager' ); ?>
	</p>
	<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'em_install_module' ); ?>
		<input type="hidden" name="action" value="em_install_module" />
		<input type="file" name="module" accept=".zip" required />
		<?php submit_button( __( 'Install module', 'emanager' ), 'primary', 'submit', false ); ?>
	</form>

	<hr />

	<h2><?php esc_html_e( 'Installed modules', 'emanager' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Module', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'Section', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'Database table', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'Statuses', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'Source', 'emanager' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $em_registry as $section ) : ?>
			<?php foreach ( $section['modules'] as $module ) : ?>
				<?php $is_custom = isset( $em_all[ $module['id'] ] ) && strpos( $em_all[ $module['id'] ]['path'], EM_CUSTOM_MODULES_DIR ) === 0; ?>
				<tr>
					<td><strong><?php echo esc_html( $module['name'] ); ?></strong> <code><?php echo esc_html( $module['id'] ); ?></code></td>
					<td><?php echo esc_html( $section['name'] ); ?></td>
					<td><code><?php echo esc_html( $module['table'] ); ?></code></td>
					<td><?php echo esc_html( implode( ', ', $module['statuses'] ?? array() ) ); ?></td>
					<td><?php echo $is_custom ? esc_html__( 'Custom (uploads)', 'emanager' ) : esc_html__( 'Built-in', 'emanager' ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
