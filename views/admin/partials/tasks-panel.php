<?php
/**
 * Tasks tab on a record screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     admin: \LeadFlow\CRM\Modules\Tasks\TasksAdmin,
 *     parent: string,
 *     parent_id: int,
 *     tasks: \LeadFlow\CRM\Data\Models\Task[],
 *     can_add: bool,
 *     can_assign: bool,
 *     users: array<int, string>,
 *     nonce: string,
 *     list_url: string
 * } $data
 */

use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Tasks\TasksAdmin;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_uid = $data['parent'] . '-' . $data['parent_id'];
?>
<div class="lf-tasks-panel" data-lf-task-list data-nonce="<?php echo esc_attr( $data['nonce'] ); ?>">
	<?php if ( $data['can_add'] ) : ?>
		<form class="lf-quick-task" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="leadflow_crm_quick_task" />
			<input type="hidden" name="parent" value="<?php echo esc_attr( $data['parent'] ); ?>" />
			<input type="hidden" name="parent_id" value="<?php echo esc_attr( (string) $data['parent_id'] ); ?>" />
			<?php Nonce::field( 'quick_task_' . $data['parent'] . '_' . $data['parent_id'] ); ?>
			<div class="lf-quick-task__title">
				<label for="lf-quick-task-title-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'New task', 'wp-leadflow-crm' ); ?></label>
				<input type="text" id="lf-quick-task-title-<?php echo esc_attr( $leadflow_uid ); ?>" name="title" maxlength="255" required placeholder="<?php esc_attr_e( 'e.g. Call back about pricing', 'wp-leadflow-crm' ); ?>" />
			</div>
			<div>
				<label for="lf-quick-task-due-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Due', 'wp-leadflow-crm' ); ?></label>
				<input type="datetime-local" id="lf-quick-task-due-<?php echo esc_attr( $leadflow_uid ); ?>" name="due_at" />
			</div>
			<div>
				<label for="lf-quick-task-priority-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Priority', 'wp-leadflow-crm' ); ?></label>
				<select id="lf-quick-task-priority-<?php echo esc_attr( $leadflow_uid ); ?>" name="priority">
					<?php foreach ( TasksAdmin::priority_labels() as $leadflow_value => $leadflow_label ) : ?>
						<option value="<?php echo esc_attr( $leadflow_value ); ?>" <?php selected( 'normal', $leadflow_value ); ?>><?php echo esc_html( $leadflow_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php if ( $data['can_assign'] ) : ?>
				<div>
					<label for="lf-quick-task-assignee-<?php echo esc_attr( $leadflow_uid ); ?>"><?php esc_html_e( 'Assign to', 'wp-leadflow-crm' ); ?></label>
					<select id="lf-quick-task-assignee-<?php echo esc_attr( $leadflow_uid ); ?>" name="assigned_to">
						<?php foreach ( $data['users'] as $leadflow_user_id => $leadflow_user_name ) : ?>
							<option value="<?php echo esc_attr( (string) $leadflow_user_id ); ?>" <?php selected( get_current_user_id(), $leadflow_user_id ); ?>><?php echo esc_html( $leadflow_user_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<div class="lf-quick-task__submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Add task', 'wp-leadflow-crm' ); ?></button>
			</div>
		</form>
	<?php endif; ?>

	<?php if ( empty( $data['tasks'] ) ) : ?>
		<p class="lf-empty-text"><?php esc_html_e( 'No tasks yet.', 'wp-leadflow-crm' ); ?></p>
	<?php else : ?>
		<ul class="lf-task-list">
			<?php foreach ( $data['tasks'] as $leadflow_task ) : ?>
				<?php
				View::render(
					'admin/partials/task-item',
					array(
						'task'          => $leadflow_task,
						'admin'         => $data['admin'],
						'show_assignee' => true,
					)
				);
				?>
			<?php endforeach; ?>
		</ul>
		<p><a href="<?php echo esc_url( $data['list_url'] ); ?>"><?php esc_html_e( 'View all tasks for this record', 'wp-leadflow-crm' ); ?></a></p>
	<?php endif; ?>
</div>
