<?php
/**
 * "My tasks" dashboard widget.
 *
 * @package LeadFlow\CRM
 *
 * @var array{admin: \LeadFlow\CRM\Modules\Tasks\TasksAdmin, tasks: \LeadFlow\CRM\Data\Models\Task[], overdue: int, open: int, nonce: string} $data
 */

use LeadFlow\CRM\Admin\View;

defined( 'ABSPATH' ) || exit;
?>
<div class="lf-widget" data-lf-task-list data-nonce="<?php echo esc_attr( $data['nonce'] ); ?>">
	<p class="lf-widget__total">
		<span class="lf-widget__number"><?php echo esc_html( number_format_i18n( $data['open'] ) ); ?></span>
		<span class="lf-widget__label"><?php esc_html_e( 'open tasks', 'wp-leadflow-crm' ); ?></span>
		<?php if ( $data['overdue'] > 0 ) : ?>
			<a class="lf-pill lf-pill--overdue" href="<?php echo esc_url( $data['admin']->url( array( 'status' => 'overdue' ) ) ); ?>">
				<?php
				/* translators: %d: Number of overdue tasks. */
				echo esc_html( sprintf( _n( '%d overdue', '%d overdue', $data['overdue'], 'wp-leadflow-crm' ), $data['overdue'] ) );
				?>
			</a>
		<?php endif; ?>
	</p>

	<?php if ( empty( $data['tasks'] ) ) : ?>
		<p class="lf-empty-text"><?php esc_html_e( 'You are all caught up.', 'wp-leadflow-crm' ); ?></p>
	<?php else : ?>
		<ul class="lf-task-list">
			<?php foreach ( $data['tasks'] as $leadflow_task ) : ?>
				<?php
				View::render(
					'admin/partials/task-item',
					array(
						'task'  => $leadflow_task,
						'admin' => $data['admin'],
					)
				);
				?>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<p class="lf-widget__actions">
		<a class="button" href="<?php echo esc_url( $data['admin']->url( array( 'status' => 'mine' ) ) ); ?>"><?php esc_html_e( 'View my tasks', 'wp-leadflow-crm' ); ?></a>
		<?php if ( current_user_can( $data['admin']->cap( 'edit' ) ) ) : ?>
			<a class="button button-primary" href="<?php echo esc_url( $data['admin']->new_url() ); ?>"><?php esc_html_e( 'Add task', 'wp-leadflow-crm' ); ?></a>
		<?php endif; ?>
	</p>
</div>
