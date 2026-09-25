<?php
/**
 * Team: users with CRM access and their CRM role.
 *
 * @package LeadFlow\CRM
 *
 * @var array{users: \WP_User[], roles: array<string, string>, assignable: string[]} $data
 */

use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_names = wp_roles()->get_names();
$leadflow_self  = get_current_user_id();
$leadflow_admin = current_user_can( 'manage_options' );
?>
<div class="lf-card">
	<h2 class="lf-card__title"><?php esc_html_e( 'Add someone to the CRM', 'wp-leadflow-crm' ); ?></h2>
	<p class="lf-muted"><?php esc_html_e( 'The person needs a WordPress account on this site. Their other WordPress roles are kept.', 'wp-leadflow-crm' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-inline-form">
		<input type="hidden" name="action" value="leadflow_crm_assign_crm_role" />
		<?php Nonce::field( 'assign_crm_role' ); ?>
		<label for="lf-team-user"><?php esc_html_e( 'Username or email', 'wp-leadflow-crm' ); ?></label>
		<input type="text" id="lf-team-user" name="user" class="regular-text" required autocomplete="off" />
		<label for="lf-team-role"><?php esc_html_e( 'CRM role', 'wp-leadflow-crm' ); ?></label>
		<select id="lf-team-role" name="role">
			<?php foreach ( $data['roles'] as $leadflow_slug => $leadflow_name ) : ?>
				<?php if ( in_array( $leadflow_slug, $data['assignable'], true ) ) : ?>
					<option value="<?php echo esc_attr( $leadflow_slug ); ?>"><?php echo esc_html( $leadflow_name ); ?></option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="button button-primary"><?php esc_html_e( 'Add', 'wp-leadflow-crm' ); ?></button>
	</form>
</div>

<div class="lf-card lf-team">
	<h2 class="lf-card__title"><?php esc_html_e( 'People with CRM access', 'wp-leadflow-crm' ); ?></h2>
	<div class="lf-table-scroll">
		<table class="widefat striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Name', 'wp-leadflow-crm' ); ?></th>
					<th scope="col"><?php esc_html_e( 'WordPress roles', 'wp-leadflow-crm' ); ?></th>
					<th scope="col"><?php esc_html_e( 'CRM role', 'wp-leadflow-crm' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['users'] as $leadflow_user ) : ?>
					<?php
					$leadflow_roles_of = (array) $leadflow_user->roles;
					$leadflow_crm_role = (string) ( array_values( array_intersect( $leadflow_roles_of, array_keys( $data['roles'] ) ) )[0] ?? '' );
					$leadflow_other    = array_map( static fn( string $r ): string => translate_user_role( $leadflow_names[ $r ] ?? $r ), array_diff( $leadflow_roles_of, array_keys( $data['roles'] ) ) );
					$leadflow_locked   = $leadflow_user->ID === $leadflow_self || ( user_can( $leadflow_user, 'manage_options' ) && ! $leadflow_admin ) || ( '' !== $leadflow_crm_role && ! in_array( $leadflow_crm_role, $data['assignable'], true ) );
					?>
					<tr>
						<td>
							<strong><?php echo esc_html( $leadflow_user->display_name ); ?></strong>
							<br /><span class="lf-muted"><?php echo esc_html( $leadflow_user->user_email ); ?></span>
						</td>
						<td><?php echo esc_html( implode( ', ', $leadflow_other ) ?: '—' ); ?></td>
						<td>
							<?php if ( $leadflow_locked ) : ?>
								<?php echo esc_html( '' !== $leadflow_crm_role ? $data['roles'][ $leadflow_crm_role ] : ( user_can( $leadflow_user, 'manage_options' ) ? __( 'Full access (administrator)', 'wp-leadflow-crm' ) : __( 'Through their WordPress role', 'wp-leadflow-crm' ) ) ); ?>
								<?php if ( $leadflow_user->ID === $leadflow_self ) : ?>
									<span class="lf-muted"><?php esc_html_e( '(you)', 'wp-leadflow-crm' ); ?></span>
								<?php endif; ?>
							<?php else : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-inline-form">
									<input type="hidden" name="action" value="leadflow_crm_assign_crm_role" />
									<input type="hidden" name="user" value="<?php echo esc_attr( (string) $leadflow_user->ID ); ?>" />
									<?php Nonce::field( 'assign_crm_role' ); ?>
									<label class="screen-reader-text" for="lf-role-<?php echo esc_attr( (string) $leadflow_user->ID ); ?>"><?php echo esc_html( sprintf( /* translators: %s: User name. */ __( 'CRM role of %s', 'wp-leadflow-crm' ), $leadflow_user->display_name ) ); ?></label>
									<select id="lf-role-<?php echo esc_attr( (string) $leadflow_user->ID ); ?>" name="role">
										<option value=""><?php esc_html_e( 'No CRM role', 'wp-leadflow-crm' ); ?></option>
										<?php foreach ( $data['roles'] as $leadflow_slug => $leadflow_name ) : ?>
											<?php if ( in_array( $leadflow_slug, $data['assignable'], true ) ) : ?>
												<option value="<?php echo esc_attr( $leadflow_slug ); ?>" <?php selected( $leadflow_crm_role, $leadflow_slug ); ?>><?php echo esc_html( $leadflow_name ); ?></option>
											<?php endif; ?>
										<?php endforeach; ?>
									</select>
									<button type="submit" class="button"><?php esc_html_e( 'Change', 'wp-leadflow-crm' ); ?></button>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
