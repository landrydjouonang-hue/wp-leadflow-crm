<?php
/**
 * Import / Export screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     tab: string,
 *     can_import: bool,
 *     can_export: bool,
 *     session: \LeadFlow\CRM\Modules\ImportExport\ImportSession|null,
 *     importable: array<string, string>,
 *     exportable: array<string, string>,
 *     fields: array<string, array<string, mixed>>,
 *     max_mb: int
 * } $data
 */

use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\ImportExport\ImportExportPage;

defined( 'ABSPATH' ) || exit;

$leadflow_session = $data['session'];
$leadflow_step    = null === $leadflow_session ? 'upload' : (string) $leadflow_session->data['step'];

// "Back to mapping" from the preview.
if ( 'preview' === $leadflow_step && 'map' === \LeadFlow\CRM\Security\Input::key( 'step' ) ) {
	$leadflow_step = 'map';
}
$leadflow_steps   = array(
	'upload'  => __( 'Upload', 'wp-leadflow-crm' ),
	'map'     => __( 'Map columns', 'wp-leadflow-crm' ),
	'preview' => __( 'Preview', 'wp-leadflow-crm' ),
	'running' => __( 'Import', 'wp-leadflow-crm' ),
	'done'    => __( 'Summary', 'wp-leadflow-crm' ),
);
$leadflow_step_keys = array_keys( $leadflow_steps );
$leadflow_current   = (int) array_search( $leadflow_step, $leadflow_step_keys, true );
?>
<div class="wrap lf-wrap lf-import-export">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => __( 'Import & export', 'wp-leadflow-crm' ),
			'subtitle' => __( 'Bring your leads, contacts and companies in from a spreadsheet, or download them as CSV.', 'wp-leadflow-crm' ),
		)
	);
	?>

	<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Import or export', 'wp-leadflow-crm' ); ?>">
		<?php if ( $data['can_import'] ) : ?>
			<a href="<?php echo esc_url( ImportExportPage::url( array( 'tab' => 'import' ) ) ); ?>" class="nav-tab <?php echo 'import' === $data['tab'] ? 'nav-tab-active' : ''; ?>" <?php echo 'import' === $data['tab'] ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'Import', 'wp-leadflow-crm' ); ?></a>
		<?php endif; ?>
		<?php if ( $data['can_export'] ) : ?>
			<a href="<?php echo esc_url( ImportExportPage::url( array( 'tab' => 'export' ) ) ); ?>" class="nav-tab <?php echo 'export' === $data['tab'] ? 'nav-tab-active' : ''; ?>" <?php echo 'export' === $data['tab'] ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'Export', 'wp-leadflow-crm' ); ?></a>
		<?php endif; ?>
	</nav>

	<div class="lf-card lf-settings">
		<?php if ( 'export' === $data['tab'] ) : ?>
			<?php View::render( 'admin/import-export/export', $data ); ?>
		<?php else : ?>
			<ol class="lf-steps" aria-label="<?php esc_attr_e( 'Import steps', 'wp-leadflow-crm' ); ?>">
				<?php foreach ( $leadflow_step_keys as $leadflow_index => $leadflow_key ) : ?>
					<li class="lf-steps__item <?php echo $leadflow_index < $leadflow_current ? 'is-done' : ( $leadflow_index === $leadflow_current ? 'is-current' : '' ); ?>" <?php echo $leadflow_index === $leadflow_current ? 'aria-current="step"' : ''; ?>>
						<span class="lf-steps__number" aria-hidden="true"><?php echo esc_html( (string) ( $leadflow_index + 1 ) ); ?></span>
						<?php echo esc_html( $leadflow_steps[ $leadflow_key ] ); ?>
					</li>
				<?php endforeach; ?>
			</ol>

			<?php
			$leadflow_views = array(
				'upload'  => 'admin/import-export/upload',
				'map'     => 'admin/import-export/map',
				'preview' => 'admin/import-export/preview',
				'running' => 'admin/import-export/preview',
				'done'    => 'admin/import-export/summary',
			);
			View::render( $leadflow_views[ $leadflow_step ] ?? 'admin/import-export/upload', $data );
			?>
		<?php endif; ?>
	</div>
</div>
