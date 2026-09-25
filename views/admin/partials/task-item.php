<?php
/**
 * One task row (record panels and dashboard).
 *
 * @package LeadFlow\CRM
 *
 * @var array{task: \LeadFlow\CRM\Data\Models\Task, admin: \LeadFlow\CRM\Modules\Tasks\TasksAdmin, show_assignee?: bool} $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Modules\Tasks\TasksAdmin;
use LeadFlow\CRM\Modules\Tasks\TasksListTable;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_task     = $data['task'];
$leadflow_status   = (string) $leadflow_task->get( 'status' );
$leadflow_priority = (string) $leadflow_task->get( 'priority', 'normal' );
$leadflow_classes  = array( 'lf-task' );

if ( 'completed' === $leadflow_status ) {
	$leadflow_classes[] = 'is-completed';
} elseif ( TasksAdmin::is_overdue( $leadflow_task ) ) {
	$leadflow_classes[] = 'is-overdue';
}
?>
<li class="<?php echo esc_attr( implode( ' ', $leadflow_classes ) ); ?>" data-task-row="<?php echo esc_attr( (string) $leadflow_task->id() ); ?>">
	<?php echo TasksListTable::toggle( $leadflow_task ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in toggle(). ?>
	<div class="lf-task__body">
		<a class="lf-task__title" href="<?php echo esc_url( $data['admin']->view_url( $leadflow_task->id() ) ); ?>"><?php echo esc_html( (string) $leadflow_task->get( 'title' ) ); ?></a>
		<span class="lf-task__meta">
			<?php echo TasksListTable::due_label( $leadflow_task ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in due_label(). ?>
			<?php if ( 'high' === $leadflow_priority ) : ?>
				<?php echo UI::badge( 'priority-high', TasksAdmin::priority_labels()['high'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
			<?php if ( ! empty( $data['show_assignee'] ) && null !== $leadflow_task->get( 'assigned_to' ) ) : ?>
				<span class="lf-muted"><?php echo esc_html( Format::user( $leadflow_task->get( 'assigned_to' ) ) ); ?></span>
			<?php endif; ?>
		</span>
	</div>
</li>
