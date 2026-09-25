<?php
/**
 * Template preview screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     admin: \LeadFlow\CRM\Modules\Templates\TemplatesAdmin,
 *     template: \LeadFlow\CRM\Data\Models\EmailTemplate,
 *     preview: array{subject: string, body: string},
 *     records: array<string, array<string, string>>
 * } $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_template = $data['template'];
$leadflow_admin    = $data['admin'];
$leadflow_status   = (string) $leadflow_template->get( 'status', 'active' );
?>
<div class="wrap lf-wrap lf-record">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => $leadflow_template->name(),
			'subtitle' => __( 'Email template', 'wp-leadflow-crm' ),
			'badge'    => UI::badge( $leadflow_status, $leadflow_admin->status_labels()[ $leadflow_status ] ?? $leadflow_status ),
			'actions'  => $leadflow_admin->view_actions( $leadflow_template ),
		)
	);
	?>

	<?php if ( $leadflow_template->is_trashed() ) : ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'This template is in the trash.', 'wp-leadflow-crm' ); ?></p></div>
	<?php endif; ?>

	<p class="lf-breadcrumb"><a href="<?php echo esc_url( $leadflow_admin->url() ); ?>">&larr; <?php esc_html_e( 'All templates', 'wp-leadflow-crm' ); ?></a></p>

	<div class="lf-layout">
		<div class="lf-layout__main">
			<section class="lf-card lf-template-preview" aria-labelledby="lf-preview-title" data-lf-template-preview data-template-subject="<?php echo esc_attr( $leadflow_template->subject() ); ?>" data-template-body="<?php echo esc_attr( $leadflow_template->body() ); ?>">
				<header class="lf-card__header">
					<h2 class="lf-card__title" id="lf-preview-title"><?php esc_html_e( 'Preview', 'wp-leadflow-crm' ); ?></h2>
					<div class="hide-if-no-js">
						<label for="lf-preview-record"><?php esc_html_e( 'Preview with', 'wp-leadflow-crm' ); ?></label>
						<select id="lf-preview-record" data-lf-preview-record>
							<option value=""><?php esc_html_e( 'Sample data', 'wp-leadflow-crm' ); ?></option>
							<?php foreach ( $data['records'] as $leadflow_group => $leadflow_options ) : ?>
								<optgroup label="<?php echo esc_attr( $leadflow_group ); ?>">
									<?php foreach ( $leadflow_options as $leadflow_value => $leadflow_label ) : ?>
										<option value="<?php echo esc_attr( $leadflow_value ); ?>"><?php echo esc_html( $leadflow_label ); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
					</div>
				</header>
				<div class="lf-email-preview" aria-live="polite">
					<p class="lf-email-preview__subject"><strong><?php esc_html_e( 'Subject:', 'wp-leadflow-crm' ); ?></strong> <span data-lf-preview-subject><?php echo esc_html( $data['preview']['subject'] ); ?></span></p>
					<div class="lf-email-preview__body" data-lf-preview-body><?php echo wp_kses_post( $data['preview']['body'] ); ?></div>
				</div>
				<p class="description"><?php esc_html_e( 'Sending emails is not available yet. Templates are ready for the upcoming email features.', 'wp-leadflow-crm' ); ?></p>
			</section>

			<section class="lf-card" aria-labelledby="lf-source-title">
				<h2 class="lf-card__title" id="lf-source-title"><?php esc_html_e( 'Template source', 'wp-leadflow-crm' ); ?></h2>
				<p><strong><?php esc_html_e( 'Subject:', 'wp-leadflow-crm' ); ?></strong> <code><?php echo esc_html( $leadflow_template->subject() ); ?></code></p>
				<pre class="lf-template-source"><?php echo esc_html( $leadflow_template->body() ); ?></pre>
			</section>
		</div>

		<aside class="lf-layout__side" aria-label="<?php esc_attr_e( 'Template summary', 'wp-leadflow-crm' ); ?>">
			<div class="lf-card">
				<dl class="lf-meta">
					<dt><?php esc_html_e( 'Created', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo esc_html( Format::datetime( $leadflow_template->get( 'created_at' ) ) ); ?></dd>
					<dt><?php esc_html_e( 'Last updated', 'wp-leadflow-crm' ); ?></dt>
					<dd>
						<?php echo esc_html( Format::relative( $leadflow_template->get( 'updated_at' ) ) ); ?>
						<?php $leadflow_editor = Format::user( $leadflow_template->get( 'updated_by' ) ); ?>
						<?php if ( '' !== $leadflow_editor ) : ?>
							<br /><span class="lf-muted"><?php echo esc_html( sprintf( /* translators: %s: User name. */ __( 'by %s', 'wp-leadflow-crm' ), $leadflow_editor ) ); ?></span>
						<?php endif; ?>
					</dd>
				</dl>
			</div>
		</aside>
	</div>
</div>
