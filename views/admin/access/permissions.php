<?php
/**
 * Roles & permissions matrix.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     groups: array<string, array{label: string, description: string, caps: array<string, string>}>,
 *     roles: array<string, string>,
 *     map: array<string, string[]>,
 *     defaults: array<string, string[]>,
 *     can_reset: bool
 * } $data
 */

use LeadFlow\CRM\Security\Nonce;
use LeadFlow\CRM\Security\Permissions;

defined( 'ABSPATH' ) || exit;

$leadflow_roles = $data['roles'];
?>
<div class="lf-card lf-permissions">
	<p class="lf-muted">
		<?php esc_html_e( 'Tick what each role may do. Administrators always have every permission. Changes apply immediately to everyone with the role, in the admin and in the REST API. A dot marks a permission that differs from the default.', 'wp-leadflow-crm' ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="leadflow_crm_save_permissions" />
		<?php Nonce::field( 'save_permissions' ); ?>

		<div class="lf-table-scroll">
			<table class="widefat lf-permissions__table">
				<caption class="screen-reader-text"><?php esc_html_e( 'Permissions by role', 'wp-leadflow-crm' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Permission', 'wp-leadflow-crm' ); ?></th>
						<th scope="col" class="lf-permissions__role"><?php echo esc_html( translate_user_role( 'Administrator' ) ); ?></th>
						<?php foreach ( $leadflow_roles as $leadflow_role => $leadflow_role_name ) : ?>
							<th scope="col" class="lf-permissions__role"><?php echo esc_html( $leadflow_role_name ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<?php foreach ( $data['groups'] as $leadflow_group_key => $leadflow_group ) : ?>
					<tbody>
						<tr class="lf-permissions__group">
							<th scope="rowgroup" colspan="<?php echo esc_attr( (string) ( count( $leadflow_roles ) + 2 ) ); ?>">
								<?php echo esc_html( $leadflow_group['label'] ); ?>
								<span class="lf-muted"><?php echo esc_html( $leadflow_group['description'] ); ?></span>
							</th>
						</tr>
						<?php foreach ( $leadflow_group['caps'] as $leadflow_cap => $leadflow_label ) : ?>
							<tr>
								<th scope="row">
									<?php echo esc_html( $leadflow_label ); ?>
									<code class="lf-permissions__cap"><?php echo esc_html( $leadflow_cap ); ?></code>
								</th>
								<td class="lf-permissions__cell">
									<input type="checkbox" checked disabled aria-label="<?php echo esc_attr( sprintf( /* translators: 1: Permission, 2: Role. */ __( '%1$s for %2$s', 'wp-leadflow-crm' ), $leadflow_label, translate_user_role( 'Administrator' ) ) ); ?>" title="<?php echo esc_attr( Permissions::lock_reason( 'administrator', $leadflow_cap ) ); ?>" />
								</td>
								<?php foreach ( $leadflow_roles as $leadflow_role => $leadflow_role_name ) : ?>
									<?php
									$leadflow_on      = in_array( $leadflow_role, $data['map'][ $leadflow_cap ] ?? array(), true );
									$leadflow_default = in_array( $leadflow_role, $data['defaults'][ $leadflow_cap ] ?? array(), true );
									$leadflow_lock    = Permissions::lock_reason( $leadflow_role, $leadflow_cap );
									?>
									<td class="lf-permissions__cell<?php echo $leadflow_on !== $leadflow_default ? ' is-changed' : ''; ?>">
										<input
											type="checkbox"
											name="caps[<?php echo esc_attr( $leadflow_role ); ?>][]"
											value="<?php echo esc_attr( $leadflow_cap ); ?>"
											<?php checked( $leadflow_on ); ?>
											<?php disabled( '' !== $leadflow_lock ); ?>
											aria-label="<?php echo esc_attr( sprintf( /* translators: 1: Permission, 2: Role. */ __( '%1$s for %2$s', 'wp-leadflow-crm' ), $leadflow_label, $leadflow_role_name ) ); ?>"
											<?php echo '' !== $leadflow_lock ? 'title="' . esc_attr( $leadflow_lock ) . '"' : ''; ?>
										/>
										<?php if ( $leadflow_on !== $leadflow_default ) : ?>
											<span class="lf-permissions__changed" title="<?php esc_attr_e( 'Changed from the default', 'wp-leadflow-crm' ); ?>"><span class="screen-reader-text"><?php esc_html_e( '(changed from the default)', 'wp-leadflow-crm' ); ?></span></span>
										<?php endif; ?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				<?php endforeach; ?>
			</table>
		</div>

		<p class="lf-form-actions">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save permissions', 'wp-leadflow-crm' ); ?></button>
		</p>
	</form>

	<?php if ( $data['can_reset'] ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-permissions__reset">
			<input type="hidden" name="action" value="leadflow_crm_reset_permissions" />
			<?php Nonce::field( 'reset_permissions' ); ?>
			<button type="submit" class="button" data-lf-confirm="<?php esc_attr_e( 'Restore the default permissions for every role?', 'wp-leadflow-crm' ); ?>"><?php esc_html_e( 'Restore defaults', 'wp-leadflow-crm' ); ?></button>
		</form>
	<?php endif; ?>
</div>
