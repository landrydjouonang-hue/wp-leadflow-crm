<?php
/**
 * Import step 2: column mapping.
 *
 * @package LeadFlow\CRM
 *
 * @var array<string, mixed> $data See page.php.
 */

use LeadFlow\CRM\Modules\ImportExport\CsvReader;
use LeadFlow\CRM\Modules\ImportExport\EntityFields;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_session = $data['session'];
$leadflow_entity  = (string) $leadflow_session->data['entity'];
$leadflow_samples = array();

try {
	$leadflow_reader = new CsvReader( (string) $leadflow_session->data['path'] );
	foreach ( $leadflow_reader->rows( 0, 3 ) as $leadflow_row ) {
		$leadflow_samples[] = $leadflow_row;
	}
} catch ( \RuntimeException $e ) {
	$leadflow_samples = array();
}
?>
<p>
	<?php
	echo esc_html(
		sprintf(
			/* translators: 1: File name, 2: Rows, 3: Entity label. */
			__( '%1$s — %2$s rows to import as %3$s. Choose which CRM field each column goes into. Columns set to “Don’t import” are ignored.', 'wp-leadflow-crm' ),
			(string) $leadflow_session->data['filename'],
			number_format_i18n( (int) $leadflow_session->data['total'] ),
			mb_strtolower( EntityFields::get( $leadflow_entity )['label'] )
		)
	);
	?>
</p>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="leadflow_crm_import_map" />
	<input type="hidden" name="import" value="<?php echo esc_attr( $leadflow_session->token() ); ?>" />
	<?php Nonce::field( 'import_' . $leadflow_session->token() ); ?>

	<table class="widefat striped lf-mapping">
		<caption class="screen-reader-text"><?php esc_html_e( 'Column mapping', 'wp-leadflow-crm' ); ?></caption>
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Column in your file', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Sample values', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Import into', 'wp-leadflow-crm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( (array) $leadflow_session->data['headers'] as $leadflow_index => $leadflow_header ) : ?>
				<?php $leadflow_selected = (string) ( $leadflow_session->data['mapping'][ $leadflow_index ] ?? '' ); ?>
				<tr>
					<th scope="row"><?php echo esc_html( '' !== $leadflow_header ? $leadflow_header : sprintf( /* translators: %d: Column number. */ __( 'Column %d', 'wp-leadflow-crm' ), $leadflow_index + 1 ) ); ?></th>
					<td class="lf-mapping__samples">
						<?php foreach ( $leadflow_samples as $leadflow_sample ) : ?>
							<?php if ( '' !== (string) ( $leadflow_sample[ $leadflow_index ] ?? '' ) ) : ?>
								<code><?php echo esc_html( mb_strimwidth( (string) $leadflow_sample[ $leadflow_index ], 0, 40, '…' ) ); ?></code>
							<?php endif; ?>
						<?php endforeach; ?>
					</td>
					<td>
						<label class="screen-reader-text" for="lf-map-<?php echo esc_attr( (string) $leadflow_index ); ?>">
							<?php echo esc_html( sprintf( /* translators: %s: Column name. */ __( 'Field for column “%s”', 'wp-leadflow-crm' ), $leadflow_header ) ); ?>
						</label>
						<select id="lf-map-<?php echo esc_attr( (string) $leadflow_index ); ?>" name="mapping[<?php echo esc_attr( (string) $leadflow_index ); ?>]" class="<?php echo '' === $leadflow_selected ? 'is-skipped' : ''; ?>">
							<option value=""><?php esc_html_e( '— Don’t import —', 'wp-leadflow-crm' ); ?></option>
							<?php foreach ( $data['fields'] as $leadflow_key => $leadflow_field ) : ?>
								<option value="<?php echo esc_attr( $leadflow_key ); ?>" <?php selected( $leadflow_selected, $leadflow_key ); ?>>
									<?php echo esc_html( $leadflow_field['label'] . ( $leadflow_field['required'] ? ' *' : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="description"><?php esc_html_e( '* Required field.', 'wp-leadflow-crm' ); ?></p>

	<p class="lf-form-actions">
		<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Check the data', 'wp-leadflow-crm' ); ?></button>
		<button type="submit" class="button button-large" form="lf-import-cancel"><?php esc_html_e( 'Cancel import', 'wp-leadflow-crm' ); ?></button>
	</p>
</form>

<form id="lf-import-cancel" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="leadflow_crm_import_cancel" />
	<input type="hidden" name="import" value="<?php echo esc_attr( $leadflow_session->token() ); ?>" />
	<?php Nonce::field( 'import_' . $leadflow_session->token() ); ?>
</form>
