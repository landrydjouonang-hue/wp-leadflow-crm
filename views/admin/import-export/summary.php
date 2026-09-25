<?php
/**
 * Import step 5: summary.
 *
 * @package LeadFlow\CRM
 *
 * @var array<string, mixed> $data See page.php.
 */

use LeadFlow\CRM\Admin\Menu;
use LeadFlow\CRM\Modules\ImportExport\ImportExportPage;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_session = $data['session'];
$leadflow_results = (array) ( $leadflow_session->data['results'] ?? array() );
$leadflow_entity  = (string) $leadflow_session->data['entity'];
$leadflow_failed  = (int) ( $leadflow_results['failed'] ?? 0 );
$leadflow_report  = Nonce::url(
	add_query_arg(
		array(
			'action' => 'leadflow_crm_import_errors',
			'import' => $leadflow_session->token(),
		),
		admin_url( 'admin-post.php' )
	),
	'import_' . $leadflow_session->token()
);
?>
<div class="notice <?php echo $leadflow_failed > 0 ? 'notice-warning' : 'notice-success'; ?> inline" role="status">
	<p>
		<strong><?php esc_html_e( 'Import complete.', 'wp-leadflow-crm' ); ?></strong>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: File name. */
				__( 'File: %s (deleted from the server).', 'wp-leadflow-crm' ),
				(string) $leadflow_session->data['filename']
			)
		);
		?>
	</p>
</div>

<ul class="lf-import-stats">
	<li><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( (int) ( $leadflow_results['created'] ?? 0 ) ) ); ?></span> <?php esc_html_e( 'created', 'wp-leadflow-crm' ); ?></li>
	<li><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( (int) ( $leadflow_results['updated'] ?? 0 ) ) ); ?></span> <?php esc_html_e( 'updated', 'wp-leadflow-crm' ); ?></li>
	<li><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( (int) ( $leadflow_results['skipped'] ?? 0 ) ) ); ?></span> <?php esc_html_e( 'skipped (duplicates)', 'wp-leadflow-crm' ); ?></li>
	<li class="<?php echo $leadflow_failed > 0 ? 'has-errors' : ''; ?>"><span class="lf-import-stats__number"><?php echo esc_html( number_format_i18n( $leadflow_failed ) ); ?></span> <?php esc_html_e( 'failed', 'wp-leadflow-crm' ); ?></li>
</ul>

<?php if ( $leadflow_failed > 0 ) : ?>
	<h3 class="lf-subheading"><?php esc_html_e( 'Rows that were not imported', 'wp-leadflow-crm' ); ?></h3>
	<ul class="lf-messages">
		<?php foreach ( array_slice( (array) $leadflow_results['errors'], 0, 50 ) as $leadflow_error ) : ?>
			<li>
				<strong><?php echo esc_html( sprintf( /* translators: %d: Row number. */ __( 'Row %d:', 'wp-leadflow-crm' ), (int) $leadflow_error['row'] ) ); ?></strong>
				<?php echo esc_html( implode( ' ', (array) $leadflow_error['messages'] ) ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<p><a class="button" href="<?php echo esc_url( $leadflow_report ); ?>"><?php esc_html_e( 'Download error report (CSV)', 'wp-leadflow-crm' ); ?></a></p>
<?php endif; ?>

<p class="lf-form-actions">
	<a class="button button-primary" href="<?php echo esc_url( Menu::url( 'leadflow-crm-' . $leadflow_entity ) ); ?>"><?php esc_html_e( 'View imported records', 'wp-leadflow-crm' ); ?></a>
	<a class="button" href="<?php echo esc_url( ImportExportPage::url( array( 'tab' => 'import' ) ) ); ?>"><?php esc_html_e( 'Import another file', 'wp-leadflow-crm' ); ?></a>
</p>
