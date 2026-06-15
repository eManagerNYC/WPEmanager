<?php
/**
 * Admin: User management — assign eManager role and company per user.
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- $_GET below is a read-only status message; saves are nonce-checked in EM_Admin::save_user().

$em_roles     = array(
	'em_administrator' => __( 'Administrator (create / read / update / delete)', 'emanager' ),
	'em_editor'        => __( 'Editor (create / read / update)', 'emanager' ),
	'em_contributor'   => __( 'Contributor (create / read)', 'emanager' ),
	'em_viewer'        => __( 'Viewer (read)', 'emanager' ),
	'em_restricted'    => __( 'Restricted (no access)', 'emanager' ),
);
$em_parties   = EM_Roles::party_role_labels();
$em_companies = EM_Admin::companies();
$em_users     = get_users(
	array(
		'number'  => 500,
		'orderby' => 'display_name',
	)
);
?>
<div class="wrap em-admin">
	<h1><?php esc_html_e( 'eManager Users', 'emanager' ); ?></h1>

	<?php if ( isset( $_GET['em_msg'] ) && 'saved' === $_GET['em_msg'] ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'User updated.', 'emanager' ); ?></p></div>
	<?php endif; ?>

	<p>
		<?php esc_html_e( 'Assign each user a eManager role and a company. WordPress administrators always have full access.', 'emanager' ); ?>
		<a href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Add new user', 'emanager' ); ?></a>
	</p>

	<table class="widefat striped em-users-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'User', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'Email', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'eManager role', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'Party role', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'Company', 'emanager' ); ?></th>
				<th><?php esc_html_e( 'Emails', 'emanager' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $em_users as $em_user ) : ?>
			<?php
			$current_role = '';
			foreach ( array_keys( $em_roles ) as $r ) {
				if ( in_array( $r, (array) $em_user->roles, true ) ) {
					$current_role = $r;
					break;
				}
			}
			$is_wp_admin = user_can( $em_user, 'manage_options' );
			?>
			<tr>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<td>
					<strong><?php echo esc_html( $em_user->display_name ); ?></strong>
					<?php if ( $is_wp_admin ) : ?>
						<span class="em-badge"><?php esc_html_e( 'WP Admin', 'emanager' ); ?></span>
					<?php endif; ?>
				</td>
				<td><?php echo esc_html( $em_user->user_email ); ?></td>
				<td>
					<?php if ( $is_wp_admin ) : ?>
						<em><?php esc_html_e( 'Full access', 'emanager' ); ?></em>
					<?php else : ?>
						<select name="em_role">
							<option value=""><?php esc_html_e( '— none —', 'emanager' ); ?></option>
							<?php foreach ( $em_roles as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_role, $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</td>
				<td>
					<select name="em_party_role">
						<option value=""><?php esc_html_e( '— none —', 'emanager' ); ?></option>
						<?php foreach ( $em_parties as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( get_user_meta( $em_user->ID, 'em_party_role', true ), $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td>
					<select name="em_company_id">
						<option value=""><?php esc_html_e( '— none —', 'emanager' ); ?></option>
						<?php foreach ( $em_companies as $company ) : ?>
							<option value="<?php echo esc_attr( $company['id'] ); ?>" <?php selected( get_user_meta( $em_user->ID, 'em_company_id', true ), $company['id'] ); ?>>
								<?php echo esc_html( $company['name'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
				<td>
					<label>
						<input type="checkbox" name="em_email_notify" value="1" <?php checked( '0' !== (string) get_user_meta( $em_user->ID, 'em_email_notify', true ) ); ?> />
						<?php esc_html_e( 'On', 'emanager' ); ?>
					</label>
				</td>
				<td>
					<?php wp_nonce_field( 'em_user' ); ?>
					<input type="hidden" name="action" value="em_save_user" />
					<input type="hidden" name="user_id" value="<?php echo esc_attr( $em_user->ID ); ?>" />
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'emanager' ); ?></button>
				</td>
				</form>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
