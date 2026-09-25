<?php
/**
 * Import step 3: preview (and step 4: running).
 *
 * @package LeadFlow\CRM
 *
 * @var array<string, mixed> $data See page.php.
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Modules\ImportExport\EntityFields;
use LeadFlow\CRM\Modules\ImportExport\Importer;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_session = $data['session'];
$leadflow_preview = (array) ( $leadflow_session->data['preview'] ?? array() );
$leadflow_counts  = (array) ( $leadflow_preview['counts'] ?? array() );
$leadflow_token   = $leadflow_session->token();
$leadflow_running = 'running' === $leadflow_session->data['step'];
$leadflow_total   = min( (int) $leadflow_session->data['total'], Importer::MAX_ROWS );
$leadflow_labels  = array(
	'create' => __( 'New', 'wp-leadflow-crm' ),
	'update' => __( 'Update', 'wp-leadflow-crm' ),
	'skip'   => __( 'Skip', 'wp-leadflow-crm' ),
	'error'  => __( 'Error', 'wp-leadflow-crm' ),
);
$leadflow_pills   = array(
	'create' => 'completed',
	'update' => 'in_progress',
	'skip'   => 'cancelled',
	'error'  => 'overdue',
);
$leadflow_entity  = EntityFields::get( (string) $leadflow_session->data['entity'] )['label'];
?>
<?php if ( $leadflow_running ) : ?>
	<div class="notice notice-info inline"><p><?php esc_html_e( 'This import was interrupted. Continue to import the remaining rows.', 'wp-leadflow-crm' ); ?></p></div>
<?php endif; ?>

<ul class="lf-import-stats">
	<li><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( (int) ( $leadflow_counts['create'] ?? 0 ) ) ); ?></span> <?php esc_html_e( 'new records', 'wp-leadflow-crm' ); ?></li>
	<li><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( (int) ( $leadflow_counts['update'] ?? 0 ) ) ); ?></span> <?php esc_html_e( 'updates', 'wp-leadflow-crm' ); ?></li>
	<li><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( (int) ( $leadflow_counts['skip'] ?? 0 ) ) ); ?></span> <?php esc_html_e( 'duplicates skipped', 'wp-leadflow-crm' ); ?></li>
	<li class="<?php echo ! empty( $leadflow_counts['error'] ) ? 'has-errors' : ''; ?>"><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( (int) ( $leadflow_counts['error'] ?? 0 ) ) ); ?></span> <?php esc_html_e( 'rows with errors', 'wp-leadflow-crm' ); ?></li>
	<li><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( (int) ( $leadflow_counts['warning'] ?? 0 ) ) ); ?></span> <?php esc_html_e( 'rows with warnings', 'wp-leadflow-crm' ); ?></li>
</ul>

<?php if ( ! empty( $leadflow_counts['error'] ) ) : ?>
	<div class="notice notice-warning inline">
		<p><?php esc_html_e( 'Rows with errors will not be imported. You can fix them in your file and upload it again, or import the valid rows now and download an error report afterwards.', 'wp-leadflow-crm' ); ?></p>
	</div>
<?php endif; ?>

<h3 class="lf-subheading">
	<?php
	/* translators: %d: Number of rows shown. */
	echo esc_html( sprintf( __( 'First %d rows', 'wp-leadflow-crm' ), count( (array) ( $leadflow_preview['rows'] ?? array() ) ) ) );
	?>
</h3>
<div class="lf-table-scroll">
	<table class="widefat striped lf-preview-table">
		<caption class="screen-reader-text"><?php esc_html_e( 'Import preview', 'wp-leadflow-crm' ); ?></caption>
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Row', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Result', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Data', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Messages', 'wp-leadflow-crm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( (array) ( $leadflow_preview['rows'] ?? array() ) as $leadflow_row ) : ?>
				<tr class="is-<?php echo esc_attr( $leadflow_row['action'] ); ?>">
					<td><?php echo esc_html( (string) $leadflow_row['row'] ); ?></td>
					<td><?php echo UI::badge( $leadflow_pills[ $leadflow_row['action'] ] ?? '', $leadflow_labels[ $leadflow_row['action'] ] ?? $leadflow_row['action'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					<td>
						<dl class="lf-preview-values">
							<?php foreach ( (array) $leadflow_row['values'] as $leadflow_label => $leadflow_value ) : ?>
								<?php if ( '' !== (string) $leadflow_value ) : ?>
									<div><dt><?php echo esc_html( (string) $leadflow_label ); ?></dt><dd><?php echo esc_html( mb_strimwidth( (string) $leadflow_value, 0, 60, '…' ) ); ?></dd></div>
								<?php endif; ?>
							<?php endforeach; ?>
						</dl>
					</td>
					<td>
						<?php if ( ! empty( $leadflow_row['messages'] ) ) : ?>
							<ul class="lf-messages">
								<?php foreach ( (array) $leadflow_row['messages'] as $leadflow_message ) : ?>
									<li><?php echo esc_html( (string) $leadflow_message ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php if ( ! empty( $leadflow_preview['errors'] ) ) : ?>
	<details class="lf-import-errors">
		<summary><?php echo esc_html( sprintf( /* translators: %d: Number of rows. */ _n( 'Show %d row with errors', 'Show all %d rows with errors', count( $leadflow_preview['errors'] ), 'wp-leadflow-crm' ), count( $leadflow_preview['errors'] ) ) ); ?></summary>
		<ul class="lf-messages">
			<?php foreach ( $leadflow_preview['errors'] as $leadflow_error ) : ?>
				<li>
					<strong><?php echo esc_html( sprintf( /* translators: %d: Row number. */ __( 'Row %d:', 'wp-leadflow-crm' ), (int) $leadflow_error['row'] ) ); ?></strong>
					<?php echo esc_html( implode( ' ', (array) $leadflow_error['messages'] ) ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</details>
<?php endif; ?>

<div class="lf-import-progress" data-lf-import-progress hidden>
	<p><strong data-lf-import-status><?php esc_html_e( 'Importing…', 'wp-leadflow-crm' ); ?></strong></p>
	<progress max="<?php echo esc_attr( (string) $leadflow_total ); ?>" value="<?php echo esc_attr( (string) (int) ( $leadflow_session->data['offset'] ?? 0 ) ); ?>" data-lf-import-bar></progress>
</div>

<div class="lf-form-actions">
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-lf-import-run data-token="<?php echo esc_attr( $leadflow_token ); ?>">
		<input type="hidden" name="action" value="leadflow_crm_import_run" />
		<input type="hidden" name="import" value="<?php echo esc_attr( $leadflow_token ); ?>" />
		<?php Nonce::field( 'import_' . $leadflow_token ); ?>
		<button type="submit" class="button button-primary button-large" <?php disabled( 0 === (int) ( $leadflow_counts['create'] ?? 0 ) + (int) ( $leadflow_counts['update'] ?? 0 ) && ! $leadflow_running ); ?>>
			<?php
			echo esc_html(
				$leadflow_running
					? __( 'Continue import', 'wp-leadflow-crm' )
					/* translators: %s: Entity label, e.g. "Leads". */
					: sprintf( __( 'Import %s', 'wp-leadflow-crm' ), mb_strtolower( $leadflow_entity ) )
			);
			?>
		</button>
	</form>
	<?php if ( ! $leadflow_running ) : ?>
		<a class="button button-large" href="<?php echo esc_url( add_query_arg( 'step', 'map' ) ); ?>" data-lf-back-to-map><?php esc_html_e( 'Back to mapping', 'wp-leadflow-crm' ); ?></a>
	<?php endif; ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="leadflow_crm_import_cancel" />
		<input type="hidden" name="import" value="<?php echo esc_attr( $leadflow_token ); ?>" />
		<?php Nonce::field( 'import_' . $leadflow_token ); ?>
		<button type="submit" class="button-link lf-link-danger"><?php esc_html_e( 'Cancel import', 'wp-leadflow-crm' ); ?></button>
	</form>
</div>
