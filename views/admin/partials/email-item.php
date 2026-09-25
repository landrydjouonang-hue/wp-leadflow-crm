<?php
/**
 * One email in a record's Emails tab.
 *
 * @package LeadFlow\CRM
 *
 * @var array{email: \LeadFlow\CRM\Data\Models\Email, resend_url: string} $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Modules\Emails\EmailsModule;
use LeadFlow\CRM\Modules\Emails\EmailsPage;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_email = $data['email'];
$leadflow_sent  = $leadflow_email->is_sent();
$leadflow_when  = $leadflow_email->get( 'sent_at' ) ?? $leadflow_email->get( 'created_at' );
$leadflow_user  = Format::user( $leadflow_email->get( 'sent_by' ) );
?>
<li class="lf-email-item<?php echo $leadflow_sent ? '' : ' is-failed'; ?>">
	<details>
		<summary>
			<span class="dashicons dashicons-<?php echo $leadflow_sent ? 'email-alt' : 'warning'; ?>" aria-hidden="true"></span>
			<span class="lf-email-item__main">
				<strong class="lf-email-item__subject"><?php echo esc_html( '' !== $leadflow_email->subject() ? $leadflow_email->subject() : __( '(no subject)', 'wp-leadflow-crm' ) ); ?></strong>
				<span class="lf-email-item__meta">
					<?php
					echo esc_html(
						'' !== $leadflow_user
							/* translators: 1: Recipient, 2: Sender name. */
							? sprintf( __( 'To %1$s · by %2$s', 'wp-leadflow-crm' ), $leadflow_email->to_email(), $leadflow_user )
							/* translators: %s: Recipient. */
							: sprintf( __( 'To %s', 'wp-leadflow-crm' ), $leadflow_email->to_email() )
					);
					?>
				</span>
			</span>
			<?php echo $leadflow_sent ? '' : UI::badge( 'failed', __( 'Failed', 'wp-leadflow-crm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<time class="lf-email-item__time" datetime="<?php echo esc_attr( Format::iso( $leadflow_when ) ); ?>" title="<?php echo esc_attr( Format::datetime( $leadflow_when ) ); ?>"><?php echo esc_html( Format::due( $leadflow_when ) ); ?></time>
		</summary>
		<div class="lf-email-item__body">
			<?php if ( ! $leadflow_sent ) : ?>
				<p class="lf-field__error"><?php echo esc_html( (string) $leadflow_email->get( 'error', '' ) ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $leadflow_email->cc() ) ) : ?>
				<p class="lf-muted"><?php echo esc_html( sprintf( /* translators: %s: Addresses. */ __( 'Cc: %s', 'wp-leadflow-crm' ), implode( ', ', $leadflow_email->cc() ) ) ); ?></p>
			<?php endif; ?>
			<div class="lf-email-preview__body"><?php echo wp_kses_post( $leadflow_email->body() ); ?></div>
			<p class="lf-email-item__actions">
				<?php if ( current_user_can( EmailsModule::VIEW_ONE_CAP, $leadflow_email->id() ) ) : ?>
					<a href="<?php echo esc_url( EmailsPage::view_url( $leadflow_email->id() ) ); ?>"><?php esc_html_e( 'Open', 'wp-leadflow-crm' ); ?></a>
				<?php endif; ?>
				<?php if ( '' !== $data['resend_url'] ) : ?>
					<a href="<?php echo esc_url( $data['resend_url'] ); ?>"><?php echo esc_html( $leadflow_sent ? __( 'Send again', 'wp-leadflow-crm' ) : __( 'Try again', 'wp-leadflow-crm' ) ); ?></a>
				<?php endif; ?>
			</p>
		</div>
	</details>
</li>
