<?php
/**
 * Admin: Company management (companies live in the em_companies table).
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- $_GET values below are read-only display state (edit id, status message); all mutations go through nonce-checked admin-post handlers.

$em_companies = EM_Admin::companies();
delete_transient( 'em_companies_cache' ); // Always show fresh data on this screen.

$em_editing = null;
if ( isset( $_GET['edit'] ) ) {
	$em_edit_id = sanitize_text_field( wp_unslash( $_GET['edit'] ) );
	foreach ( $em_companies as $em_co ) {
		if ( $em_edit_id === (string) $em_co['id'] ) {
			$em_editing = $em_co;
			break;
		}
	}
}
?>
<div class="wrap em-admin">
	<h1><?php esc_html_e( 'Companies', 'emanager' ); ?></h1>

	<?php if ( isset( $_GET['em_msg'] ) ) : ?>
		<?php if ( 'error' === $_GET['em_msg'] ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Operation failed — please try again.', 'emanager' ); ?></p></div>
		<?php else : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Done.', 'emanager' ); ?></p></div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="em-admin-columns">
		<div>
			<h2><?php echo $em_editing ? esc_html__( 'Edit company', 'emanager' ) : esc_html__( 'Add company', 'emanager' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'em_company' ); ?>
				<input type="hidden" name="action" value="em_save_company" />
				<input type="hidden" name="company_id" value="<?php echo esc_attr( $em_editing['id'] ?? '' ); ?>" />
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="em-co-name"><?php esc_html_e( 'Name', 'emanager' ); ?></label></th>
						<td><input id="em-co-name" class="regular-text" type="text" name="name" required value="<?php echo esc_attr( $em_editing['name'] ?? '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="em-co-type"><?php esc_html_e( 'Type', 'emanager' ); ?></label></th>
						<td>
							<select id="em-co-type" name="type">
								<?php foreach ( array( 'General Contractor', 'Subcontractor', 'Owner', 'Architect', 'Engineer', 'Supplier', 'Consultant', 'Other' ) as $em_type ) : ?>
									<option value="<?php echo esc_attr( $em_type ); ?>" <?php selected( $em_editing['type'] ?? '', $em_type ); ?>><?php echo esc_html( $em_type ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="em-co-phone"><?php esc_html_e( 'Phone', 'emanager' ); ?></label></th>
						<td><input id="em-co-phone" class="regular-text" type="tel" name="phone" value="<?php echo esc_attr( $em_editing['phone'] ?? '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="em-co-email"><?php esc_html_e( 'Email', 'emanager' ); ?></label></th>
						<td><input id="em-co-email" class="regular-text" type="email" name="email" value="<?php echo esc_attr( $em_editing['email'] ?? '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="em-co-address"><?php esc_html_e( 'Address', 'emanager' ); ?></label></th>
						<td><input id="em-co-address" class="large-text" type="text" name="address" value="<?php echo esc_attr( $em_editing['address'] ?? '' ); ?>" /></td>
					</tr>
				</table>
				<?php submit_button( $em_editing ? __( 'Update company', 'emanager' ) : __( 'Add company', 'emanager' ) ); ?>
			</form>
		</div>

		<div>
			<h2><?php esc_html_e( 'All companies', 'emanager' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'emanager' ); ?></th>
						<th><?php esc_html_e( 'Type', 'emanager' ); ?></th>
						<th><?php esc_html_e( 'Email', 'emanager' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $em_companies ) ) : ?>
					<tr><td colspan="4"><?php esc_html_e( 'No companies yet — add one on the left.', 'emanager' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $em_companies as $em_co ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $em_co['name'] ); ?></strong></td>
						<td><?php echo esc_html( $em_co['type'] ?? '' ); ?></td>
						<td><?php echo esc_html( $em_co['email'] ?? '' ); ?></td>
						<td>
							<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=emanager-companies&edit=' . $em_co['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'emanager' ); ?></a>
							<a class="button button-small em-confirm" data-confirm="<?php esc_attr_e( 'Delete this company?', 'emanager' ); ?>"
								href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=em_delete_company&company_id=' . $em_co['id'] ), 'em_company_delete' ) ); ?>">
								<?php esc_html_e( 'Delete', 'emanager' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
