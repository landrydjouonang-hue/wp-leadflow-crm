<?php
/**
 * Emails tab on a record screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     type: string,
 *     record: \LeadFlow\CRM\Data\Model,
 *     emails: \LeadFlow\CRM\Data\Models\Email[],
 *     total: int,
 *     can_send: bool,
 *     compose_url: string
 * } $data
 */

use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Emails\EmailsPage;

defined( 'ABSPATH' ) || exit;
?>
<div class="lf-emails">
	<?php if ( $data['can_send'] ) : ?>
		<p><a class="button" href="<?php echo esc_url( $data['compose_url'] ); ?>"><span class="dashicons dashicons-email-alt" aria-hidden="true"></span> <?php esc_html_e( 'Send email', 'wp-leadflow-crm' ); ?></a></p>
	<?php endif; ?>

	<?php if ( empty( $data['emails'] ) ) : ?>
		<p class="lf-empty-text"><?php esc_html_e( 'No emails have been sent from the CRM yet.', 'wp-leadflow-crm' ); ?></p>
	<?php else : ?>
		<ul class="lf-email-list">
			<?php foreach ( $data['emails'] as $leadflow_email ) : ?>
				<?php
				View::render(
					'admin/partials/email-item',
					array(
						'email'      => $leadflow_email,
						'resend_url' => $data['can_send'] ? EmailsPage::compose_url( $data['type'], $data['record']->id(), array( 'resend' => $leadflow_email->id() ) ) : '',
					)
				);
				?>
			<?php endforeach; ?>
		</ul>
		<?php if ( $data['total'] > count( $data['emails'] ) ) : ?>
			<p class="lf-muted">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: Shown emails, 2: Total emails. */
						__( 'Showing the latest %1$d of %2$d emails.', 'wp-leadflow-crm' ),
						count( $data['emails'] ),
						$data['total']
					)
				);
				?>
			</p>
		<?php endif; ?>
	<?php endif; ?>
</div>
