<?php
/**
 * Import step 1: upload.
 *
 * @package LeadFlow\CRM
 *
 * @var array<string, mixed> $data See page.php.
 */

use LeadFlow\CRM\Modules\ImportExport\Importer;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="lf-import-upload">
	<input type="hidden" name="action" value="leadflow_crm_import_upload" />
	<?php Nonce::field( 'import_upload' ); ?>

	<fieldset class="lf-form-section">
		<legend class="lf-form-section__title"><?php esc_html_e( 'What do you want to import?', 'wp-leadflow-crm' ); ?></legend>
		<div class="lf-choice-grid">
			<?php $leadflow_first = true; ?>
			<?php foreach ( $data['importable'] as $leadflow_entity => $leadflow_label ) : ?>
				<label class="lf-choice">
					<input type="radio" name="entity" value="<?php echo esc_attr( $leadflow_entity ); ?>" <?php checked( $leadflow_first ); ?> required />
					<span><?php echo esc_html( $leadflow_label ); ?></span>
				</label>
				<?php $leadflow_first = false; ?>
			<?php endforeach; ?>
		</div>
	</fieldset>

	<fieldset class="lf-form-section">
		<legend class="lf-form-section__title"><?php esc_html_e( 'CSV file', 'wp-leadflow-crm' ); ?></legend>
		<p class="lf-form-section__description">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: Max rows, 2: Max megabytes. */
					__( 'The first row must contain column names. Up to %1$s rows and %2$d MB per file. Comma, semicolon and tab separators are detected automatically.', 'wp-leadflow-crm' ),
					number_format_i18n( Importer::MAX_ROWS ),
					(int) $data['max_mb']
				)
			);
			?>
		</p>
		<label class="lf-field__label" for="lf-csv-file"><?php esc_html_e( 'File', 'wp-leadflow-crm' ); ?> <span class="lf-required" aria-hidden="true">*</span></label>
		<input type="file" id="lf-csv-file" name="csv_file" accept=".csv,text/csv" required aria-required="true" />
	</fieldset>

	<fieldset class="lf-form-section">
		<legend class="lf-form-section__title"><?php esc_html_e( 'Options', 'wp-leadflow-crm' ); ?></legend>
		<div class="lf-form-grid">
			<div class="lf-field lf-field--half">
				<label class="lf-field__label" for="lf-import-mode"><?php esc_html_e( 'When a record already exists', 'wp-leadflow-crm' ); ?></label>
				<select id="lf-import-mode" name="mode" aria-describedby="lf-import-mode-help">
					<option value="skip"><?php esc_html_e( 'Skip it (recommended)', 'wp-leadflow-crm' ); ?></option>
					<option value="update"><?php esc_html_e( 'Update it with the values from the file', 'wp-leadflow-crm' ); ?></option>
					<option value="create"><?php esc_html_e( 'Import it anyway (create a duplicate)', 'wp-leadflow-crm' ); ?></option>
				</select>
				<p class="lf-field__description" id="lf-import-mode-help"><?php esc_html_e( 'Duplicates are found by email, name, website or lead name + company.', 'wp-leadflow-crm' ); ?></p>
			</div>
			<div class="lf-field lf-field--half">
				<label class="lf-field__label" for="lf-import-date"><?php esc_html_e( 'Date format in the file', 'wp-leadflow-crm' ); ?></label>
				<select id="lf-import-date" name="date_format">
					<option value="Y-m-d">2026-12-31</option>
					<option value="d/m/Y">31/12/2026</option>
					<option value="m/d/Y">12/31/2026</option>
				</select>
			</div>
			<div class="lf-field lf-field--full">
				<label><input type="checkbox" name="create_companies" value="1" checked /> <?php esc_html_e( 'Create companies that do not exist yet (for contacts and leads)', 'wp-leadflow-crm' ); ?></label>
			</div>
		</div>
	</fieldset>

	<p><button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Upload and continue', 'wp-leadflow-crm' ); ?></button></p>
</form>
