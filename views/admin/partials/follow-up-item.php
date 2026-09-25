<?php
/**
 * One follow-up.
 *
 * @package LeadFlow\CRM
 *
 * @var array{follow_up: \LeadFlow\CRM\Data\Models\FollowUp} $data
 */

use LeadFlow\CRM\Modules\Activities\ActivitiesModule;
use LeadFlow\CRM\Modules\FollowUps\FollowUpsModule;
use LeadFlow\CRM\Security\Nonce;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_fu      = $data['follow_up'];
$leadflow_status  = (string) $leadflow_fu->get( 'status' );
$leadflow_type    = (string) $leadflow_fu->get( 'type', 'call' );
$leadflow_types   = FollowUpsModule::type_labels();
$leadflow_overdue = $leadflow_fu->is_overdue();
$leadflow_manage  = 'scheduled' === $leadflow_status && FollowUpsModule::can_manage( $leadflow_fu );
$leadflow_post    = admin_url( 'admin-post.php' );
$leadflow_id      = (string) $leadflow_fu->id();
$leadflow_labels  = array(
	'completed' => __( 'Completed', 'wp-leadflow-crm' ),
	'cancelled' => __( 'Cancelled', 'wp-leadflow-crm' ),
	'missed'    => __( 'Missed', 'wp-leadflow-crm' ),
);
?>
<li class="lf-follow-up is-<?php echo esc_attr( $leadflow_status ); ?><?php echo $leadflow_overdue ? ' is-overdue' : ''; ?>" data-follow-up-id="<?php echo esc_attr( $leadflow_id ); ?>">
	<span class="lf-follow-up__icon" aria-hidden="true"><span class="dashicons dashicons-<?php echo esc_attr( ActivitiesModule::icon( 'other' === $leadflow_type ? 'follow_up' : $leadflow_type ) ); ?>"></span></span>
	<div class="lf-follow-up__body">
		<p class="lf-follow-up__subject">
			<strong><?php echo esc_html( (string) $leadflow_fu->get( 'subject' ) ); ?></strong>
			<span class="lf-muted"><?php echo esc_html( $leadflow_types[ $leadflow_type ] ?? $leadflow_type ); ?></span>
			<?php if ( $leadflow_overdue ) : ?>
				<span class="lf-pill lf-pill--overdue"><?php esc_html_e( 'Overdue', 'wp-leadflow-crm' ); ?></span>
			<?php elseif ( isset( $leadflow_labels[ $leadflow_status ] ) ) : ?>
				<span class="lf-pill lf-pill--<?php echo esc_attr( $leadflow_status ); ?>"><?php echo esc_html( $leadflow_labels[ $leadflow_status ] ); ?></span>
			<?php endif; ?>
		</p>
		<p class="lf-follow-up__meta">
			<time datetime="<?php echo esc_attr( Format::iso( $leadflow_fu->get( 'scheduled_at' ) ) ); ?>"><?php echo esc_html( Format::due( $leadflow_fu->get( 'scheduled_at' ) ) ); ?></time>
			<?php $leadflow_assignee = Format::user( $leadflow_fu->get( 'assigned_to' ) ); ?>
			<?php if ( '' !== $leadflow_assignee ) : ?>
				<span aria-hidden="true">·</span> <span><?php echo esc_html( $leadflow_assignee ); ?></span>
			<?php endif; ?>
		</p>
		<?php if ( '' !== (string) $leadflow_fu->get( 'notes', '' ) ) : ?>
			<div class="lf-follow-up__notes"><?php echo wp_kses_post( wpautop( esc_html( (string) $leadflow_fu->get( 'notes' ) ) ) ); ?></div>
		<?php endif; ?>
		<?php if ( '' !== (string) $leadflow_fu->get( 'outcome', '' ) ) : ?>
			<p class="lf-follow-up__outcome"><strong><?php esc_html_e( 'Outcome:', 'wp-leadflow-crm' ); ?></strong> <?php echo esc_html( (string) $leadflow_fu->get( 'outcome' ) ); ?></p>
		<?php endif; ?>

		<?php if ( $leadflow_manage ) : ?>
			<div class="lf-follow-up__actions">
				<form method="post" action="<?php echo esc_url( $leadflow_post ); ?>" class="lf-follow-up__complete" data-lf-follow-up-complete>
					<input type="hidden" name="action" value="leadflow_crm_complete_follow_up" />
					<input type="hidden" name="follow_up_id" value="<?php echo esc_attr( $leadflow_id ); ?>" />
					<?php Nonce::field( 'follow_up' ); ?>
					<label class="screen-reader-text" for="lf-fu-outcome-<?php echo esc_attr( $leadflow_id ); ?>"><?php esc_html_e( 'Outcome', 'wp-leadflow-crm' ); ?></label>
					<input type="text" id="lf-fu-outcome-<?php echo esc_attr( $leadflow_id ); ?>" name="outcome" maxlength="255" placeholder="<?php esc_attr_e( 'Outcome (optional)', 'wp-leadflow-crm' ); ?>" />
					<button type="submit" class="button button-small"><?php esc_html_e( 'Mark as done', 'wp-leadflow-crm' ); ?></button>
				</form>
				<form method="post" action="<?php echo esc_url( $leadflow_post ); ?>">
					<input type="hidden" name="action" value="leadflow_crm_cancel_follow_up" />
					<input type="hidden" name="follow_up_id" value="<?php echo esc_attr( $leadflow_id ); ?>" />
					<?php Nonce::field( 'follow_up' ); ?>
					<button type="submit" class="button-link lf-link-danger" data-lf-confirm="<?php esc_attr_e( 'Cancel this follow-up?', 'wp-leadflow-crm' ); ?>"><?php esc_html_e( 'Cancel', 'wp-leadflow-crm' ); ?></button>
				</form>
			</div>
		<?php endif; ?>
	</div>
</li>
