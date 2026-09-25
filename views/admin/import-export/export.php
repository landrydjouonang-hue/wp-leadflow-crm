<?php
/**
 * Export tab.
 *
 * @package LeadFlow\CRM
 *
 * @var array<string, mixed> $data See page.php.
 */

use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_statuses = array(
	'leads'     => array(
		'open' => __( 'Open', 'wp-leadflow-crm' ),
		'won'  => __( 'Won', 'wp-leadflow-crm' ),
		'lost' => __( 'Lost', 'wp-leadflow-crm' ),
	),
	'contacts'  => array(
		'active'   => __( 'Active', 'wp-leadflow-crm' ),
		'inactive' => __( 'Inactive', 'wp-leadflow-crm' ),
	),
	'companies' => array(
		'prospect' => __( 'Prospect', 'wp-leadflow-crm' ),
		'customer' => __( 'Customer', 'wp-leadflow-crm' ),
		'partner'  => __( 'Partner', 'wp-leadflow-crm' ),
		'inactive' => __( 'Inactive', 'wp-leadflow-crm' ),
	),
);
?>
<p><?php esc_html_e( 'Download a CSV file you can open in Excel, Numbers or Google Sheets. Exported files can be imported again as they are.', 'wp-leadflow-crm' ); ?></p>

<div class="lf-export-grid">
	<?php foreach ( $data['exportable'] as $leadflow_entity => $leadflow_label ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-export-card">
			<input type="hidden" name="action" value="leadflow_crm_export" />
			<input type="hidden" name="entity" value="<?php echo esc_attr( $leadflow_entity ); ?>" />
			<?php Nonce::field( 'export' ); ?>
			<h3 class="lf-card__title"><?php echo esc_html( $leadflow_label ); ?></h3>
			<p>
				<label for="lf-export-status-<?php echo esc_attr( $leadflow_entity ); ?>"><?php esc_html_e( 'Status', 'wp-leadflow-crm' ); ?></label><br />
				<select id="lf-export-status-<?php echo esc_attr( $leadflow_entity ); ?>" name="status">
					<option value=""><?php esc_html_e( 'All', 'wp-leadflow-crm' ); ?></option>
					<?php foreach ( $leadflow_statuses[ $leadflow_entity ] ?? array() as $leadflow_value => $leadflow_status ) : ?>
						<option value="<?php echo esc_attr( $leadflow_value ); ?>"><?php echo esc_html( $leadflow_status ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label><input type="checkbox" name="owner" value="mine" /> <?php esc_html_e( 'Only records assigned to me', 'wp-leadflow-crm' ); ?></label>
			</p>
			<p>
				<button type="submit" class="button button-primary">
					<span class="dashicons dashicons-download" aria-hidden="true"></span>
					<?php echo esc_html( sprintf( /* translators: %s: Entity label. */ __( 'Export %s', 'wp-leadflow-crm' ), mb_strtolower( $leadflow_label ) ) ); ?>
				</button>
			</p>
		</form>
	<?php endforeach; ?>
</div>

<?php if ( current_user_can( 'leadflow_manage_settings' ) ) : ?>
	<hr />
	<h3 class="lf-subheading"><?php esc_html_e( 'Complete backup', 'wp-leadflow-crm' ); ?></h3>
	<p class="lf-muted"><?php esc_html_e( 'One JSON file with every CRM record, including trashed ones, notes, tasks, activities, emails and your settings. Keep it somewhere safe: it contains personal data.', 'wp-leadflow-crm' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="leadflow_crm_export_all" />
		<?php Nonce::field( 'export_all' ); ?>
		<button type="submit" class="button" data-lf-busy="<?php esc_attr_e( 'Preparing the file…', 'wp-leadflow-crm' ); ?>">
			<span class="dashicons dashicons-database-export" aria-hidden="true"></span>
			<?php esc_html_e( 'Download complete backup (JSON)', 'wp-leadflow-crm' ); ?>
		</button>
	</form>
<?php endif; ?>
