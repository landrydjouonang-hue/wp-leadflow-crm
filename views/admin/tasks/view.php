<?php
/**
 * Task screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     admin: \LeadFlow\CRM\Modules\Tasks\TasksAdmin,
 *     task: \LeadFlow\CRM\Data\Models\Task,
 *     related: array<string, array{label: string, url: string}>,
 *     can_edit: bool,
 *     nonce: string
 * } $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Tasks\TasksAdmin;
use LeadFlow\CRM\Modules\Tasks\TasksListTable;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_task     = $data['task'];
$leadflow_admin    = $data['admin'];
$leadflow_status   = (string) $leadflow_task->get( 'status' );
$leadflow_statuses = $leadflow_admin->status_labels();
$leadflow_priority = (string) $leadflow_task->get( 'priority', 'normal' );
$leadflow_labels   = array(
	'lead'    => __( 'Lead', 'wp-leadflow-crm' ),
	'company' => __( 'Company', 'wp-leadflow-crm' ),
	'contact' => __( 'Contact', 'wp-leadflow-crm' ),
);
?>
<div class="wrap lf-wrap lf-record">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'   => (string) $leadflow_task->get( 'title' ),
			'badge'   => UI::badge( $leadflow_status, $leadflow_statuses[ $leadflow_status ] ?? $leadflow_status ),
			'actions' => $leadflow_admin->view_actions( $leadflow_task ),
		)
	);
	?>

	<?php if ( $leadflow_task->is_trashed() ) : ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'This task is in the trash. Restore it to make changes.', 'wp-leadflow-crm' ); ?></p></div>
	<?php elseif ( TasksAdmin::is_overdue( $leadflow_task ) ) : ?>
		<div class="notice notice-error inline"><p><?php esc_html_e( 'This task is overdue.', 'wp-leadflow-crm' ); ?></p></div>
	<?php endif; ?>

	<p class="lf-breadcrumb"><a href="<?php echo esc_url( $leadflow_admin->url() ); ?>">&larr; <?php esc_html_e( 'All tasks', 'wp-leadflow-crm' ); ?></a></p>

	<div class="lf-layout">
		<div class="lf-layout__main">
			<section class="lf-card" aria-labelledby="lf-task-details">
				<header class="lf-card__header">
					<h2 class="lf-card__title" id="lf-task-details"><?php esc_html_e( 'Details', 'wp-leadflow-crm' ); ?></h2>
					<?php if ( $data['can_edit'] ) : ?>
						<div class="lf-task-complete" data-lf-task-list data-nonce="<?php echo esc_attr( $data['nonce'] ); ?>">
							<label>
								<?php echo TasksListTable::toggle( $leadflow_task ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in toggle(). ?>
								<span class="hide-if-no-js"><?php esc_html_e( 'Completed', 'wp-leadflow-crm' ); ?></span>
							</label>
						</div>
					<?php endif; ?>
				</header>
				<dl class="lf-details">
					<div class="lf-details__wide">
						<dt><?php esc_html_e( 'Description', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php if ( '' === (string) $leadflow_task->get( 'description', '' ) ) : ?>
								<?php echo UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<?php echo wp_kses_post( wpautop( esc_html( (string) $leadflow_task->get( 'description' ) ) ) ); ?>
							<?php endif; ?>
						</dd>
					</div>
					<?php foreach ( $leadflow_labels as $leadflow_type => $leadflow_label ) : ?>
						<div>
							<dt><?php echo esc_html( $leadflow_label ); ?></dt>
							<dd>
								<?php $leadflow_link = $data['related'][ $leadflow_type ] ?? null; ?>
								<?php if ( null === $leadflow_link ) : ?>
									<?php echo UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php elseif ( '' !== $leadflow_link['url'] ) : ?>
									<a href="<?php echo esc_url( $leadflow_link['url'] ); ?>"><?php echo esc_html( $leadflow_link['label'] ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $leadflow_link['label'] ); ?>
								<?php endif; ?>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</section>
		</div>

		<aside class="lf-layout__side" aria-label="<?php esc_attr_e( 'Task summary', 'wp-leadflow-crm' ); ?>">
			<div class="lf-card">
				<dl class="lf-meta">
					<dt><?php esc_html_e( 'Status', 'wp-leadflow-crm' ); ?></dt>
					<dd data-lf-task-status><?php echo UI::badge( $leadflow_status, $leadflow_statuses[ $leadflow_status ] ?? $leadflow_status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php esc_html_e( 'Priority', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::badge( 'priority-' . $leadflow_priority, TasksAdmin::priority_labels()[ $leadflow_priority ] ?? $leadflow_priority ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php esc_html_e( 'Due', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo TasksListTable::due_label( $leadflow_task ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php esc_html_e( 'Assigned to', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::text( Format::user( $leadflow_task->get( 'assigned_to' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<?php if ( null !== $leadflow_task->get( 'completed_at' ) ) : ?>
						<dt><?php esc_html_e( 'Completed', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php echo esc_html( Format::datetime( $leadflow_task->get( 'completed_at' ) ) ); ?>
							<?php $leadflow_by = Format::user( $leadflow_task->get( 'completed_by' ) ); ?>
							<?php if ( '' !== $leadflow_by ) : ?>
								<br /><span class="lf-muted"><?php echo esc_html( sprintf( /* translators: %s: User name. */ __( 'by %s', 'wp-leadflow-crm' ), $leadflow_by ) ); ?></span>
							<?php endif; ?>
						</dd>
					<?php endif; ?>
					<dt><?php esc_html_e( 'Created', 'wp-leadflow-crm' ); ?></dt>
					<dd>
						<?php echo esc_html( Format::datetime( $leadflow_task->get( 'created_at' ) ) ); ?>
						<?php $leadflow_creator = Format::user( $leadflow_task->get( 'created_by' ) ); ?>
						<?php if ( '' !== $leadflow_creator ) : ?>
							<br /><span class="lf-muted"><?php echo esc_html( sprintf( /* translators: %s: User name. */ __( 'by %s', 'wp-leadflow-crm' ), $leadflow_creator ) ); ?></span>
						<?php endif; ?>
					</dd>
				</dl>
			</div>
		</aside>
	</div>
</div>
