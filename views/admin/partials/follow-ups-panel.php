<?php
/**
 * Follow-ups tab on a record screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     parent: string,
 *     parent_id: int,
 *     scheduled: \LeadFlow\CRM\Data\Models\FollowUp[],
 *     history: \LeadFlow\CRM\Data\Models\FollowUp[],
 *     can_add: bool,
 *     can_assign: bool,
 *     users: array<int, string>
 * } $data
 */

use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\FollowUps\FollowUpsModule;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_uid = $data['parent'] . '-' . $data['parent_id'];
?>
<div class="lf-follow-ups">
	<?php if ( $data['can_add'] ) : ?>
		<details class="lf-schedule"<?php echo empty( $data['scheduled'] ) ? ' open' : ''; ?>>
			<summary class="button"><?php esc_html_e( 'Schedule a follow-up', 'wp-leadflow-crm' ); ?></summary>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-schedule__form">
				<input type="hidden" name="action" value="leadflow_crm_schedule_follow_up" />
				<input type="hidden" name="parent" value="<?php echo esc_attr( $data['parent'] ); ?>" />
				<input type="hidden" name="parent_id" value="<?php echo esc_attr( (string) $data['parent_id'] ); ?>" />
				<?php Nonce::field( 'schedule_follow_up_' . $data['parent'] . '_' . $data['parent_id'] ); ?>
				<div class="lf-form-grid">
					<div class="lf-field lf-field--half">
						<label class="lf-field__label" for="lf-fu-type-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Type', 'wp-leadflow-crm' ); ?></label>
						<select id="lf-fu-type-<?php echo esc_attr( $leadflow_uid ); ?>" name="type">
							<?php foreach ( FollowUpsModule::type_labels() as $leadflow_value => $leadflow_label ) : ?>
								<option value="<?php echo esc_attr( $leadflow_value ); ?>"><?php echo esc_html( $leadflow_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="lf-field lf-field--half">
						<label class="lf-field__label" for="lf-fu-when-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Follow-up date', 'wp-leadflow-crm' ); ?> <span class="lf-required" aria-hidden="true">*</span></label>
						<input type="datetime-local" id="lf-fu-when-<?php echo esc_attr( $leadflow_uid ); ?>" name="scheduled_at" required aria-required="true" value="<?php echo esc_attr( wp_date( 'Y-m-d\T09:00', strtotime( '+1 day' ) ) ); ?>" />
					</div>
					<div class="lf-field lf-field--full">
						<label class="lf-field__label" for="lf-fu-subject-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Subject', 'wp-leadflow-crm' ); ?> <span class="lf-required" aria-hidden="true">*</span></label>
						<input type="text" id="lf-fu-subject-<?php echo esc_attr( $leadflow_uid ); ?>" name="subject" maxlength="255" required aria-required="true" placeholder="<?php esc_attr_e( 'e.g. Check whether they reviewed the proposal', 'wp-leadflow-crm' ); ?>" />
					</div>
					<div class="lf-field lf-field--full">
						<label class="lf-field__label" for="lf-fu-notes-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Notes', 'wp-leadflow-crm' ); ?></label>
						<textarea id="lf-fu-notes-<?php echo esc_attr( $leadflow_uid ); ?>" name="notes" rows="2" class="large-text"></textarea>
					</div>
					<?php if ( $data['can_assign'] ) : ?>
						<div class="lf-field lf-field--half">
							<label class="lf-field__label" for="lf-fu-assignee-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Assign to', 'wp-leadflow-crm' ); ?></label>
							<select id="lf-fu-assignee-<?php echo esc_attr( $leadflow_uid ); ?>" name="assigned_to">
								<?php foreach ( $data['users'] as $leadflow_user_id => $leadflow_user_name ) : ?>
									<option value="<?php echo esc_attr( (string) $leadflow_user_id ); ?>" <?php selected( get_current_user_id(), $leadflow_user_id ); ?>><?php echo esc_html( $leadflow_user_name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>
				</div>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Schedule follow-up', 'wp-leadflow-crm' ); ?></button></p>
			</form>
		</details>
	<?php endif; ?>

	<h3 class="lf-subheading"><?php esc_html_e( 'Upcoming', 'wp-leadflow-crm' ); ?></h3>
	<?php if ( empty( $data['scheduled'] ) ) : ?>
		<p class="lf-empty-text"><?php esc_html_e( 'Nothing scheduled.', 'wp-leadflow-crm' ); ?></p>
	<?php else : ?>
		<ul class="lf-follow-up-list">
			<?php foreach ( $data['scheduled'] as $leadflow_fu ) : ?>
				<?php View::render( 'admin/partials/follow-up-item', array( 'follow_up' => $leadflow_fu ) ); ?>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( ! empty( $data['history'] ) ) : ?>
		<h3 class="lf-subheading"><?php esc_html_e( 'Recent', 'wp-leadflow-crm' ); ?></h3>
		<ul class="lf-follow-up-list">
			<?php foreach ( $data['history'] as $leadflow_fu ) : ?>
				<?php View::render( 'admin/partials/follow-up-item', array( 'follow_up' => $leadflow_fu ) ); ?>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
