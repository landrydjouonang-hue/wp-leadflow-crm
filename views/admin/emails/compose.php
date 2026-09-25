<?php
/**
 * Compose an email to a lead, contact or company.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     type: string,
 *     record: \LeadFlow\CRM\Data\Model,
 *     label: string,
 *     record_url: string,
 *     recipients: array<string, string>,
 *     templates: array<int, \LeadFlow\CRM\Data\Models\EmailTemplate>,
 *     values: array<string, mixed>,
 *     errors: array<string, string>,
 *     remaining: int|null,
 *     limit: int,
 *     has_signature: bool,
 *     can_settings: bool,
 *     placeholders: array<string, array<string, string>>
 * } $data
 */

use LeadFlow\CRM\Admin\Form\FormRenderer;
use LeadFlow\CRM\Admin\Menu;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Emails\EmailsPage;
use LeadFlow\CRM\Modules\Settings\SettingsModule;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_values    = $data['values'];
$leadflow_errors    = $data['errors'];
$leadflow_record_id = $data['record']->id();
$leadflow_blocked   = null !== $data['remaining'] && $data['remaining'] <= 0;
$leadflow_copies    = '' !== (string) $leadflow_values['cc'] || '' !== (string) $leadflow_values['bcc'] || isset( $leadflow_errors['cc'] ) || isset( $leadflow_errors['bcc'] );
$leadflow_to_error  = $leadflow_errors['to'] ?? null;

$leadflow_fields = array(
	'to'      => array(
		'name'  => 'to',
		'label' => __( 'To', 'wp-leadflow-crm' ),
	),
	'cc'      => array(
		'name'        => 'cc',
		'label'       => __( 'Cc', 'wp-leadflow-crm' ),
		'description' => __( 'Separate several addresses with commas.', 'wp-leadflow-crm' ),
		'autocomplete' => 'off',
	),
	'bcc'     => array(
		'name'         => 'bcc',
		'label'        => __( 'Bcc', 'wp-leadflow-crm' ),
		'description'  => __( 'Separate several addresses with commas. Other recipients do not see these addresses.', 'wp-leadflow-crm' ),
		'autocomplete' => 'off',
	),
	'subject' => array(
		'name'      => 'subject',
		'label'     => __( 'Subject', 'wp-leadflow-crm' ),
		'required'  => true,
		'maxlength' => 255,
	),
	'body'    => array(
		'name'        => 'body',
		'label'       => __( 'Message', 'wp-leadflow-crm' ),
		'type'        => 'textarea',
		'rows'        => 14,
		'required'    => true,
		'description' => __( 'Write plain text or basic HTML. Placeholders such as {{first_name}} are replaced for this recipient.', 'wp-leadflow-crm' ),
	),
);

// Template data for the JavaScript picker (subject and body are already sanitized).
$leadflow_template_data = array();
foreach ( $data['templates'] as $leadflow_template ) {
	$leadflow_template_data[ $leadflow_template->id() ] = array(
		'subject' => $leadflow_template->subject(),
		'body'    => $leadflow_template->body(),
	);
}
?>
<div class="wrap lf-wrap lf-form-screen lf-compose">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => __( 'Send email', 'wp-leadflow-crm' ),
			/* translators: %s: Record name. */
			'subtitle' => sprintf( __( 'To %s', 'wp-leadflow-crm' ), $data['label'] ),
			'actions'  => array(
				array(
					'label' => __( 'Back', 'wp-leadflow-crm' ),
					'url'   => $data['record_url'],
				),
			),
		)
	);

	FormRenderer::error_summary( $leadflow_errors, array_values( $leadflow_fields ) );
	?>

	<?php if ( $leadflow_blocked ) : ?>
		<div class="notice notice-error inline">
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: Emails per hour. */
						__( 'You have reached the limit of %d emails per hour. You can send more emails later.', 'wp-leadflow-crm' ),
						$data['limit']
					)
				);
				?>
			</p>
		</div>
	<?php elseif ( null !== $data['remaining'] && $data['remaining'] <= 5 ) : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: Number of emails. */
						_n( 'You can send %d more email this hour.', 'You can send %d more emails this hour.', $data['remaining'], 'wp-leadflow-crm' ),
						$data['remaining']
					)
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $data['templates'] ) ) : ?>
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="lf-card lf-compose__template">
			<input type="hidden" name="page" value="<?php echo esc_attr( EmailsPage::PAGE_SLUG ); ?>" />
			<input type="hidden" name="action" value="compose" />
			<input type="hidden" name="record" value="<?php echo esc_attr( $data['type'] ); ?>" />
			<input type="hidden" name="record_id" value="<?php echo esc_attr( (string) $leadflow_record_id ); ?>" />
			<label for="lf-compose-template"><?php esc_html_e( 'Start from a template', 'wp-leadflow-crm' ); ?></label>
			<select
				id="lf-compose-template"
				name="template"
				data-lf-compose-template
				data-templates="<?php echo esc_attr( (string) wp_json_encode( $leadflow_template_data ) ); ?>"
			>
				<option value=""><?php esc_html_e( '— No template —', 'wp-leadflow-crm' ); ?></option>
				<?php foreach ( $data['templates'] as $leadflow_id => $leadflow_template ) : ?>
					<option value="<?php echo esc_attr( (string) $leadflow_id ); ?>" <?php selected( (string) $leadflow_values['template_id'], (string) $leadflow_id ); ?>><?php echo esc_html( $leadflow_template->name() ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button hide-if-js"><?php esc_html_e( 'Use template', 'wp-leadflow-crm' ); ?></button>
			<span class="lf-muted hide-if-no-js"><?php esc_html_e( 'Choosing a template replaces the subject and message.', 'wp-leadflow-crm' ); ?></span>
		</form>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-entity-form" novalidate data-lf-compose>
		<input type="hidden" name="action" value="leadflow_crm_send_email" />
		<input type="hidden" name="record" value="<?php echo esc_attr( $data['type'] ); ?>" />
		<input type="hidden" name="record_id" value="<?php echo esc_attr( (string) $leadflow_record_id ); ?>" />
		<input type="hidden" name="template_id" value="<?php echo esc_attr( (string) $leadflow_values['template_id'] ); ?>" data-lf-compose-template-id />
		<?php Nonce::field( 'send_email_' . $data['type'] . '_' . $leadflow_record_id ); ?>

		<div class="lf-layout">
			<div class="lf-layout__main">
				<div class="lf-card">
					<div class="lf-form-grid">
						<div class="lf-field lf-field--full lf-field--email<?php echo null !== $leadflow_to_error ? ' lf-field--error' : ''; ?>">
							<label class="lf-field__label" for="lf-field-to">
								<?php esc_html_e( 'To', 'wp-leadflow-crm' ); ?>
								<span class="lf-required" aria-hidden="true">*</span><span class="screen-reader-text"> <?php esc_html_e( '(required)', 'wp-leadflow-crm' ); ?></span>
							</label>
							<input
								type="email"
								id="lf-field-to"
								name="to"
								class="regular-text"
								required
								aria-required="true"
								autocomplete="off"
								list="lf-recipients"
								value="<?php echo esc_attr( (string) $leadflow_values['to'] ); ?>"
								aria-describedby="lf-field-to-description<?php echo null !== $leadflow_to_error ? ' lf-field-to-error' : ''; ?>"
								<?php echo null !== $leadflow_to_error ? 'aria-invalid="true"' : ''; ?>
							/>
							<datalist id="lf-recipients">
								<?php foreach ( $data['recipients'] as $leadflow_address => $leadflow_name ) : ?>
									<option value="<?php echo esc_attr( $leadflow_address ); ?>"><?php echo esc_html( $leadflow_name ); ?></option>
								<?php endforeach; ?>
							</datalist>
							<p class="lf-field__description" id="lf-field-to-description">
								<?php
								if ( empty( $data['recipients'] ) ) {
									esc_html_e( 'This record has no email address yet. Type the address to send to.', 'wp-leadflow-crm' );
								} else {
									echo esc_html(
										sprintf(
											/* translators: %s: Comma-separated email addresses. */
											__( 'Known addresses: %s', 'wp-leadflow-crm' ),
											implode( ', ', array_keys( $data['recipients'] ) )
										)
									);
								}
								?>
							</p>
							<?php if ( null !== $leadflow_to_error ) : ?>
								<p class="lf-field__error" id="lf-field-to-error"><?php echo esc_html( $leadflow_to_error ); ?></p>
							<?php endif; ?>
						</div>

						<details class="lf-field lf-field--full lf-compose__copies"<?php echo $leadflow_copies ? ' open' : ''; ?>>
							<summary><?php esc_html_e( 'Cc / Bcc', 'wp-leadflow-crm' ); ?></summary>
							<div class="lf-form-grid">
								<?php
								FormRenderer::field( $leadflow_fields['cc'], $leadflow_values['cc'], $leadflow_errors['cc'] ?? null );
								FormRenderer::field( $leadflow_fields['bcc'], $leadflow_values['bcc'], $leadflow_errors['bcc'] ?? null );
								?>
							</div>
						</details>

						<?php
						FormRenderer::field( $leadflow_fields['subject'], $leadflow_values['subject'], $leadflow_errors['subject'] ?? null );
						FormRenderer::field( $leadflow_fields['body'], $leadflow_values['body'], $leadflow_errors['body'] ?? null );
						?>

						<div class="lf-field lf-field--full">
							<?php if ( $data['has_signature'] ) : ?>
								<p class="lf-muted"><?php esc_html_e( 'Your email signature is added automatically.', 'wp-leadflow-crm' ); ?></p>
							<?php elseif ( $data['can_settings'] ) : ?>
								<p class="lf-muted">
									<a href="<?php echo esc_url( Menu::url( SettingsModule::PAGE_SLUG, array( 'tab' => 'email' ) ) ); ?>"><?php esc_html_e( 'Add an email signature', 'wp-leadflow-crm' ); ?></a>
								</p>
							<?php endif; ?>
							<label for="lf-field-copy-me">
								<input type="checkbox" id="lf-field-copy-me" name="copy_me" value="1" <?php checked( (bool) $leadflow_values['copy_me'] ); ?> />
								<?php esc_html_e( 'Send me a copy', 'wp-leadflow-crm' ); ?>
							</label>
						</div>
					</div>
				</div>
			</div>

			<aside class="lf-layout__side" aria-label="<?php esc_attr_e( 'Send', 'wp-leadflow-crm' ); ?>">
				<div class="lf-card lf-sticky">
					<div class="lf-form-actions">
						<button type="submit" class="button button-primary button-large" <?php disabled( $leadflow_blocked ); ?>>
							<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
							<?php esc_html_e( 'Send email', 'wp-leadflow-crm' ); ?>
						</button>
						<a class="button button-large" href="<?php echo esc_url( $data['record_url'] ); ?>"><?php esc_html_e( 'Cancel', 'wp-leadflow-crm' ); ?></a>
					</div>
					<p class="lf-muted"><?php esc_html_e( 'The email is saved in the record’s Emails tab and on its activity timeline.', 'wp-leadflow-crm' ); ?></p>
				</div>
				<?php
				View::render(
					'admin/templates/aside',
					array(
						'placeholders' => $data['placeholders'],
						'record'       => $data['type'] . ':' . $leadflow_record_id,
						'record_label' => $data['label'],
						'action'       => 'leadflow_crm_preview_email',
						'nonce'        => 'email',
					)
				);
				?>
			</aside>
		</div>
	</form>
</div>
