<?php
/**
 * One email.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     email: \LeadFlow\CRM\Data\Models\Email,
 *     type: string|null,
 *     record: \LeadFlow\CRM\Data\Model|null,
 *     record_url: string,
 *     resend_url: string
 * } $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Email\EmailSender;
use LeadFlow\CRM\Email\Mailer;
use LeadFlow\CRM\Modules\Emails\EmailsModule;
use LeadFlow\CRM\Modules\Emails\EmailsPage;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_email   = $data['email'];
$leadflow_meta    = (array) $leadflow_email->get( 'meta', array() );
$leadflow_actions = array();

if ( '' !== $data['resend_url'] ) {
	$leadflow_actions[] = array(
		'label'   => $leadflow_email->is_sent() ? __( 'Send again', 'wp-leadflow-crm' ) : __( 'Try again', 'wp-leadflow-crm' ),
		'url'     => $data['resend_url'],
		'primary' => ! $leadflow_email->is_sent(),
	);
}

if ( '' !== $data['record_url'] ) {
	$leadflow_actions[] = array(
		'label' => __( 'Back to record', 'wp-leadflow-crm' ),
		'url'   => $data['record_url'] . '#lf-panel-emails',
	);
}

// Bcc recipients are only shown to the sender and to users who see all emails.
$leadflow_show_bcc = (int) $leadflow_email->get( 'sent_by' ) === get_current_user_id() || current_user_can( EmailsModule::VIEW_OTHERS_CAP );
$leadflow_from     = '' !== (string) $leadflow_email->get( 'from_email', '' )
	? Mailer::address( (string) $leadflow_email->get( 'from_email' ), (string) $leadflow_email->get( 'from_name', '' ) )
	: (string) $leadflow_email->get( 'from_name', '' );
?>
<div class="wrap lf-wrap lf-record">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => '' !== $leadflow_email->subject() ? $leadflow_email->subject() : __( '(no subject)', 'wp-leadflow-crm' ),
			'subtitle' => __( 'Email', 'wp-leadflow-crm' ),
			'badge'    => $leadflow_email->is_sent() ? UI::badge( 'sent', __( 'Sent', 'wp-leadflow-crm' ) ) : UI::badge( 'failed', __( 'Failed', 'wp-leadflow-crm' ) ),
			'actions'  => $leadflow_actions,
		)
	);
	?>

	<p class="lf-breadcrumb">
		<a href="<?php echo esc_url( \LeadFlow\CRM\Admin\Menu::url( EmailsPage::PAGE_SLUG ) ); ?>">&larr; <?php esc_html_e( 'All emails', 'wp-leadflow-crm' ); ?></a>
	</p>

	<?php if ( ! $leadflow_email->is_sent() ) : ?>
		<div class="notice notice-error inline">
			<p>
				<strong><?php esc_html_e( 'This email was not sent.', 'wp-leadflow-crm' ); ?></strong>
				<?php echo esc_html( (string) $leadflow_email->get( 'error', '' ) ); ?>
			</p>
		</div>
	<?php endif; ?>

	<div class="lf-layout">
		<div class="lf-layout__main">
			<section class="lf-card" aria-labelledby="lf-email-body-title">
				<h2 class="screen-reader-text" id="lf-email-body-title"><?php esc_html_e( 'Message', 'wp-leadflow-crm' ); ?></h2>
				<div class="lf-email-preview">
					<div class="lf-email-preview__body"><?php echo wp_kses_post( $leadflow_email->body() ); ?></div>
				</div>
			</section>
		</div>

		<aside class="lf-layout__side" aria-label="<?php esc_attr_e( 'Email details', 'wp-leadflow-crm' ); ?>">
			<div class="lf-card">
				<dl class="lf-meta">
					<dt><?php esc_html_e( 'To', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo esc_html( $leadflow_email->recipient() ); ?></dd>
					<?php if ( ! empty( $leadflow_email->cc() ) ) : ?>
						<dt><?php esc_html_e( 'Cc', 'wp-leadflow-crm' ); ?></dt>
						<dd><?php echo esc_html( implode( ', ', $leadflow_email->cc() ) ); ?></dd>
					<?php endif; ?>
					<?php if ( $leadflow_show_bcc && ! empty( $leadflow_email->bcc() ) ) : ?>
						<dt><?php esc_html_e( 'Bcc', 'wp-leadflow-crm' ); ?></dt>
						<dd><?php echo esc_html( implode( ', ', $leadflow_email->bcc() ) ); ?></dd>
					<?php endif; ?>
					<dt><?php esc_html_e( 'From', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::text( $leadflow_from ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<?php if ( '' !== (string) $leadflow_email->get( 'reply_to', '' ) ) : ?>
						<dt><?php esc_html_e( 'Reply-To', 'wp-leadflow-crm' ); ?></dt>
						<dd><?php echo esc_html( (string) $leadflow_email->get( 'reply_to' ) ); ?></dd>
					<?php endif; ?>
					<dt><?php esc_html_e( 'Sent by', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::text( Format::user( $leadflow_email->get( 'sent_by' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php echo esc_html( $leadflow_email->is_sent() ? __( 'Sent', 'wp-leadflow-crm' ) : __( 'Attempted', 'wp-leadflow-crm' ) ); ?></dt>
					<dd><?php echo esc_html( Format::datetime( $leadflow_email->get( 'sent_at' ) ?? $leadflow_email->get( 'created_at' ) ) ); ?></dd>
					<?php if ( ! empty( $leadflow_meta['template'] ) ) : ?>
						<dt><?php esc_html_e( 'Template', 'wp-leadflow-crm' ); ?></dt>
						<dd><?php echo esc_html( (string) $leadflow_meta['template'] ); ?></dd>
					<?php endif; ?>
					<?php if ( null !== $data['record'] ) : ?>
						<dt><?php esc_html_e( 'Related to', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php if ( '' !== $data['record_url'] ) : ?>
								<a href="<?php echo esc_url( $data['record_url'] ); ?>"><?php echo esc_html( EmailSender::label( $data['record'] ) ); ?></a>
							<?php else : ?>
								<?php echo esc_html( EmailSender::label( $data['record'] ) ); ?>
							<?php endif; ?>
						</dd>
					<?php endif; ?>
				</dl>
			</div>
		</aside>
	</div>
</div>
