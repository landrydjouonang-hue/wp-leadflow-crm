<?php
/**
 * Placeholder picker and live preview (template add/edit and compose screens).
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     placeholders: array<string, array{label: string, group: string, sample: string}>,
 *     records?: array<string, array<string, string>>,
 *     record?: string,
 *     record_label?: string,
 *     action?: string,
 *     nonce?: string
 * } $data
 *
 * With `record` ("lead:5") the preview is fixed to that record and uses
 * the AJAX `action` / `nonce` key given (compose screen).
 */

defined( 'ABSPATH' ) || exit;

$leadflow_groups = array();
foreach ( $data['placeholders'] as $leadflow_key => $leadflow_placeholder ) {
	$leadflow_groups[ $leadflow_placeholder['group'] ][ $leadflow_key ] = $leadflow_placeholder;
}

$leadflow_fixed = (string) ( $data['record'] ?? '' );
?>
<section class="lf-card lf-placeholders" aria-labelledby="lf-placeholders-title">
	<h2 class="lf-card__title" id="lf-placeholders-title"><?php esc_html_e( 'Placeholders', 'wp-leadflow-crm' ); ?></h2>
	<p class="lf-muted hide-if-no-js"><?php esc_html_e( 'Click to insert at the cursor in the subject or message.', 'wp-leadflow-crm' ); ?></p>
	<?php foreach ( $leadflow_groups as $leadflow_group => $leadflow_items ) : ?>
		<h3 class="lf-subheading"><?php echo esc_html( $leadflow_group ); ?></h3>
		<ul class="lf-placeholder-list">
			<?php foreach ( $leadflow_items as $leadflow_key => $leadflow_placeholder ) : ?>
				<li>
					<button type="button" class="lf-placeholder" data-lf-placeholder="<?php echo esc_attr( '{{' . $leadflow_key . '}}' ); ?>" title="<?php echo esc_attr( $leadflow_placeholder['label'] ); ?>">
						<code><?php echo esc_html( '{{' . $leadflow_key . '}}' ); ?></code>
						<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: Placeholder label. */ __( 'Insert %s', 'wp-leadflow-crm' ), $leadflow_placeholder['label'] ) ); ?></span>
					</button>
					<span class="lf-placeholder-list__label"><?php echo esc_html( $leadflow_placeholder['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endforeach; ?>
</section>

<section
	class="lf-card lf-template-preview hide-if-no-js"
	aria-labelledby="lf-preview-title"
	data-lf-template-preview
	<?php if ( ! empty( $data['action'] ) ) : ?>
		data-lf-preview-action="<?php echo esc_attr( (string) $data['action'] ); ?>"
		data-lf-preview-nonce="<?php echo esc_attr( (string) ( $data['nonce'] ?? 'template' ) ); ?>"
	<?php endif; ?>
>
	<h2 class="lf-card__title" id="lf-preview-title"><?php esc_html_e( 'Live preview', 'wp-leadflow-crm' ); ?></h2>
	<?php if ( '' !== $leadflow_fixed ) : ?>
		<input type="hidden" value="<?php echo esc_attr( $leadflow_fixed ); ?>" data-lf-preview-record />
		<p class="lf-muted">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: Record name. */
					__( 'As %s will receive it, including your signature.', 'wp-leadflow-crm' ),
					(string) ( $data['record_label'] ?? '' )
				)
			);
			?>
		</p>
	<?php else : ?>
		<label for="lf-preview-record"><?php esc_html_e( 'Preview with', 'wp-leadflow-crm' ); ?></label>
		<select id="lf-preview-record" data-lf-preview-record>
			<option value=""><?php esc_html_e( 'Sample data', 'wp-leadflow-crm' ); ?></option>
			<?php foreach ( (array) ( $data['records'] ?? array() ) as $leadflow_group => $leadflow_options ) : ?>
				<optgroup label="<?php echo esc_attr( $leadflow_group ); ?>">
					<?php foreach ( $leadflow_options as $leadflow_value => $leadflow_label ) : ?>
						<option value="<?php echo esc_attr( $leadflow_value ); ?>"><?php echo esc_html( $leadflow_label ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
		</select>
	<?php endif; ?>
	<div class="lf-email-preview" aria-live="polite">
		<p class="lf-email-preview__subject"><strong><?php esc_html_e( 'Subject:', 'wp-leadflow-crm' ); ?></strong> <span data-lf-preview-subject></span></p>
		<div class="lf-email-preview__body" data-lf-preview-body></div>
		<p class="lf-field__error" data-lf-preview-unknown hidden></p>
	</div>
</section>
