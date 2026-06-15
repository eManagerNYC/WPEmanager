<?php
/**
 * Admin: Settings (Database status + Project information).
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

$em_project = get_option( 'em_project', array() );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status message.
$em_rebuilt = isset( $_GET['em_msg'] ) && 'tables_rebuilt' === $_GET['em_msg'];

$em_module_count = 0;
foreach ( EM_Modules::instance()->all() as $em_m ) {
	if ( empty( $em_m['virtual'] ) ) {
		++$em_module_count;
	}
}
?>
<div class="wrap em-admin">
	<h1><?php esc_html_e( 'eManager Settings', 'emanager' ); ?></h1>

	<?php settings_errors(); ?>
	<?php if ( $em_rebuilt ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Database tables rebuilt.', 'emanager' ); ?></p></div>
	<?php endif; ?>

	<h2 class="title"><?php esc_html_e( 'Database', 'emanager' ); ?></h2>
	<p class="description">
		<?php
		printf(
			/* translators: %d: number of module tables */
			esc_html__( 'eManager stores all data in custom tables in this WordPress database (one table per module, plus shared tables for companies, comments and the activity log). %d module tables are defined. Tables are created on activation and when a module is installed; use Rebuild tables after upgrades or manual changes.', 'emanager' ),
			(int) $em_module_count
		);
		?>
	</p>
	<p>
		<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=em_rebuild_tables' ), 'em_rebuild_tables' ) ); ?>">
			<?php esc_html_e( 'Rebuild tables', 'emanager' ); ?>
		</a>
	</p>

	<form method="post" action="options.php">
		<?php settings_fields( 'em_settings' ); ?>

		<h2 class="title"><?php esc_html_e( 'Notifications', 'emanager' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Email notifications', 'emanager' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="em_notifications" value="1" <?php checked( '0' !== (string) get_option( 'em_notifications', '1' ) ); ?> />
						<?php esc_html_e( 'Email the record owner and the next responsible party when a record advances through its workflow.', 'emanager' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Individual users can opt out under eManager → Users.', 'emanager' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'Project information', 'emanager' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="em-pr-name"><?php esc_html_e( 'Project name', 'emanager' ); ?></label></th>
				<td><input id="em-pr-name" class="regular-text" type="text" name="em_project[name]" value="<?php echo esc_attr( $em_project['name'] ?? '' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="em-pr-id"><?php esc_html_e( 'Project ID (slug)', 'emanager' ); ?></label></th>
				<td><input id="em-pr-id" class="regular-text code" type="text" name="em_project[id]" value="<?php echo esc_attr( $em_project['id'] ?? 'default' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="em-pr-number"><?php esc_html_e( 'Project number', 'emanager' ); ?></label></th>
				<td><input id="em-pr-number" class="regular-text" type="text" name="em_project[number]" value="<?php echo esc_attr( $em_project['number'] ?? '' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="em-pr-address"><?php esc_html_e( 'Site address', 'emanager' ); ?></label></th>
				<td><input id="em-pr-address" class="large-text" type="text" name="em_project[address]" value="<?php echo esc_attr( $em_project['address'] ?? '' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Site coordinates (for weather)', 'emanager' ); ?></th>
				<td>
					<input class="small-text" type="number" step="0.000001" name="em_project[lat]" value="<?php echo esc_attr( $em_project['lat'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Latitude', 'emanager' ); ?>" />
					<input class="small-text" type="number" step="0.000001" name="em_project[lon]" value="<?php echo esc_attr( $em_project['lon'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Longitude', 'emanager' ); ?>" />
					<p class="description"><?php esc_html_e( 'Used by Daily Reports to auto-fill site weather.', 'emanager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Schedule', 'emanager' ); ?></th>
				<td>
					<input type="date" name="em_project[start]" value="<?php echo esc_attr( $em_project['start'] ?? '' ); ?>" />
					<span> — </span>
					<input type="date" name="em_project[finish]" value="<?php echo esc_attr( $em_project['finish'] ?? '' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="em-pr-owner"><?php esc_html_e( 'Owner', 'emanager' ); ?></label></th>
				<td><input id="em-pr-owner" class="regular-text" type="text" name="em_project[owner]" value="<?php echo esc_attr( $em_project['owner'] ?? '' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="em-pr-architect"><?php esc_html_e( 'Architect / Engineer', 'emanager' ); ?></label></th>
				<td><input id="em-pr-architect" class="regular-text" type="text" name="em_project[architect]" value="<?php echo esc_attr( $em_project['architect'] ?? '' ); ?>" /></td>
			</tr>
		</table>

		<p class="submit">
			<?php submit_button( null, 'primary', 'submit', false ); ?>
			<a class="button" href="<?php echo esc_url( get_permalink( (int) get_option( 'em_page_dashboard' ) ) ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Open dashboard ↗', 'emanager' ); ?>
			</a>
		</p>
	</form>
</div>
