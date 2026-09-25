<?php
/**
 * Activity timeline with the "Log activity" form.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     parent: string,
 *     parent_id: int,
 *     items: \LeadFlow\CRM\Data\Models\Activity[],
 *     origins: array<int, array{label: string, url: string}>,
 *     has_more: bool,
 *     next_page: int,
 *     can_log: bool,
 *     types: array<string, string>,
 *     nonce: string
 * } $data
 */

use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_uid = $data['parent'] . '-' . $data['parent_id'];
?>
<div class="lf-timeline-wrap">
	<?php if ( $data['can_log'] ) : ?>
		<form class="lf-log-activity" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-lf-log-activity>
			<input type="hidden" name="action" value="leadflow_crm_log_activity" />
			<input type="hidden" name="parent" value="<?php echo esc_attr( $data['parent'] ); ?>" />
			<input type="hidden" name="parent_id" value="<?php echo esc_attr( (string) $data['parent_id'] ); ?>" />
			<?php Nonce::field( 'activity' ); ?>
			<div class="lf-log-activity__row">
				<div class="lf-log-activity__type">
					<label for="lf-activity-type-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Log', 'wp-leadflow-crm' ); ?></label>
					<select id="lf-activity-type-<?php echo esc_attr( $leadflow_uid ); ?>" name="type">
						<?php foreach ( $data['types'] as $leadflow_type => $leadflow_label ) : ?>
							<option value="<?php echo esc_attr( $leadflow_type ); ?>"><?php echo esc_html( $leadflow_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="lf-log-activity__when">
					<label for="lf-activity-when-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'When', 'wp-leadflow-crm' ); ?></label>
					<input type="datetime-local" id="lf-activity-when-<?php echo esc_attr( $leadflow_uid ); ?>" name="occurred_at" value="<?php echo esc_attr( current_time( 'Y-m-d\TH:i' ) ); ?>" max="<?php echo esc_attr( current_time( 'Y-m-d\TH:i' ) ); ?>" />
				</div>
			</div>
			<label class="screen-reader-text" for="lf-activity-desc-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'What happened?', 'wp-leadflow-crm' ); ?></label>
			<textarea id="lf-activity-desc-<?php echo esc_attr( $leadflow_uid ); ?>" name="description" rows="2" class="large-text" required placeholder="<?php esc_attr_e( 'What happened? e.g. Discussed pricing, sending a proposal on Friday.', 'wp-leadflow-crm' ); ?>"></textarea>
			<p class="lf-log-activity__actions">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Log activity', 'wp-leadflow-crm' ); ?></button>
			</p>
		</form>
	<?php endif; ?>

	<ol
		class="lf-timeline"
		data-lf-timeline
		data-parent="<?php echo esc_attr( $data['parent'] ); ?>"
		data-parent-id="<?php echo esc_attr( (string) $data['parent_id'] ); ?>"
		data-page="<?php echo esc_attr( (string) ( $data['next_page'] - 1 ) ); ?>"
		data-nonce="<?php echo esc_attr( $data['nonce'] ); ?>"
		aria-live="polite"
	>
		<?php foreach ( $data['items'] as $leadflow_item ) : ?>
			<?php
			View::render(
				'admin/partials/activity-item',
				array(
					'activity' => $leadflow_item,
					'origin'   => $data['origins'][ $leadflow_item->id() ] ?? null,
				)
			);
			?>
		<?php endforeach; ?>
	</ol>

	<?php if ( empty( $data['items'] ) ) : ?>
		<p class="lf-empty-text" data-lf-timeline-empty><?php esc_html_e( 'No activity yet.', 'wp-leadflow-crm' ); ?></p>
	<?php endif; ?>

	<?php if ( $data['has_more'] ) : ?>
		<p class="lf-timeline__more">
			<a class="button" href="<?php echo esc_url( add_query_arg( 'timeline_page', $data['next_page'] ) . '#lf-panel-activity' ); ?>" data-lf-timeline-more><?php esc_html_e( 'Load older activity', 'wp-leadflow-crm' ); ?></a>
		</p>
	<?php endif; ?>
</div>
