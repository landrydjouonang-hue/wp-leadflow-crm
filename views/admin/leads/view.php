<?php
/**
 * Lead profile.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     admin: \LeadFlow\CRM\Modules\Leads\LeadsAdmin,
 *     lead: \LeadFlow\CRM\Data\Models\Lead,
 *     stages: \LeadFlow\CRM\Data\Models\Stage[],
 *     stage: \LeadFlow\CRM\Data\Models\Stage|null,
 *     company: \LeadFlow\CRM\Data\Models\Company|null,
 *     contact: \LeadFlow\CRM\Data\Models\Contact|null,
 *     company_admin: \LeadFlow\CRM\Modules\Companies\CompaniesAdmin,
 *     contact_admin: \LeadFlow\CRM\Modules\Contacts\ContactsAdmin,
 *     can_edit: bool,
 *     can_assign: bool,
 *     users: array<int, string>,
 *     note_actions: \LeadFlow\CRM\Admin\Notes\NoteActions
 * } $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Leads\PipelineBoard;
use LeadFlow\CRM\Security\Nonce;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_lead     = $data['lead'];
$leadflow_admin    = $data['admin'];
$leadflow_stage    = $data['stage'];
$leadflow_status   = (string) $leadflow_lead->get( 'status', 'open' );
$leadflow_statuses = $leadflow_admin->status_labels();
$leadflow_currency = (string) $leadflow_lead->get( 'currency', '' );
$leadflow_close    = $leadflow_lead->get( 'expected_close_date' );
$leadflow_date     = static fn( string $date ): string => (string) wp_date( (string) get_option( 'date_format' ), (int) strtotime( $date . ' 12:00:00 UTC' ) );
$leadflow_post_url = admin_url( 'admin-post.php' );
$leadflow_subtitle = array_filter(
	array(
		Format::money( $leadflow_lead->amount(), $leadflow_currency ),
		null !== $data['company'] ? $data['company']->name() : '',
	)
);

// Position of the current stage among open stages (for the progress bar).
$leadflow_open_stages = array_values( array_filter( $data['stages'], static fn( $s ) => 'open' === $s->type() ) );
$leadflow_closed      = array_values( array_filter( $data['stages'], static fn( $s ) => 'open' !== $s->type() ) );
$leadflow_current_idx = -1;
foreach ( $leadflow_open_stages as $leadflow_i => $leadflow_s ) {
	if ( null !== $leadflow_stage && $leadflow_s->slug() === $leadflow_stage->slug() ) {
		$leadflow_current_idx = $leadflow_i;
	}
}
?>
<div class="wrap lf-wrap lf-record">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => $leadflow_lead->title(),
			'subtitle' => implode( ' · ', $leadflow_subtitle ),
			'badge'    => UI::badge( $leadflow_status, $leadflow_statuses[ $leadflow_status ] ?? $leadflow_status ),
			'actions'  => $leadflow_admin->view_actions( $leadflow_lead ),
		)
	);
	?>

	<?php if ( $leadflow_lead->is_trashed() ) : ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'This lead is in the trash. Restore it to make changes.', 'wp-leadflow-crm' ); ?></p></div>
	<?php endif; ?>

	<p class="lf-breadcrumb">
		<a href="<?php echo esc_url( $leadflow_admin->url() ); ?>">&larr; <?php esc_html_e( 'All leads', 'wp-leadflow-crm' ); ?></a>
		<span aria-hidden="true">·</span>
		<a href="<?php echo esc_url( PipelineBoard::url() ); ?>"><?php esc_html_e( 'Pipeline', 'wp-leadflow-crm' ); ?></a>
	</p>

	<nav class="lf-card lf-stagebar" aria-label="<?php esc_attr_e( 'Sales stage', 'wp-leadflow-crm' ); ?>">
		<ol class="lf-stagebar__steps">
			<?php foreach ( $leadflow_open_stages as $leadflow_i => $leadflow_s ) : ?>
				<?php
				$leadflow_is_current = null !== $leadflow_stage && $leadflow_s->slug() === $leadflow_stage->slug();
				$leadflow_state      = $leadflow_is_current ? 'current' : ( ( $leadflow_i < $leadflow_current_idx || 'won' === $leadflow_status ) ? 'done' : 'todo' );
				?>
				<li class="lf-stagebar__step is-<?php echo esc_attr( $leadflow_state ); ?>" style="--lf-stage-color: <?php echo esc_attr( $leadflow_s->color() ); ?>">
					<?php if ( $data['can_edit'] && ! $leadflow_is_current ) : ?>
						<form method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
							<input type="hidden" name="action" value="leadflow_crm_move_lead" />
							<input type="hidden" name="lead_id" value="<?php echo esc_attr( (string) $leadflow_lead->id() ); ?>" />
							<input type="hidden" name="stage" value="<?php echo esc_attr( $leadflow_s->slug() ); ?>" />
							<?php Nonce::field( 'move_lead_' . $leadflow_lead->id() ); ?>
							<button type="submit" class="lf-stagebar__button">
								<?php echo esc_html( $leadflow_s->name() ); ?>
								<span class="screen-reader-text"><?php esc_html_e( '(move to this stage)', 'wp-leadflow-crm' ); ?></span>
							</button>
						</form>
					<?php else : ?>
						<span class="lf-stagebar__label" <?php echo $leadflow_is_current ? 'aria-current="step"' : ''; ?>><?php echo esc_html( $leadflow_s->name() ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>

		<?php if ( ! empty( $leadflow_closed ) ) : ?>
			<div class="lf-stagebar__outcomes">
				<?php foreach ( $leadflow_closed as $leadflow_s ) : ?>
					<?php $leadflow_is_current = null !== $leadflow_stage && $leadflow_s->slug() === $leadflow_stage->slug(); ?>
					<?php if ( $leadflow_is_current ) : ?>
						<span class="lf-stagebar__outcome is-current is-<?php echo esc_attr( $leadflow_s->type() ); ?>" aria-current="step"><?php echo esc_html( $leadflow_s->name() ); ?></span>
					<?php elseif ( $data['can_edit'] ) : ?>
						<form method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
							<input type="hidden" name="action" value="leadflow_crm_move_lead" />
							<input type="hidden" name="lead_id" value="<?php echo esc_attr( (string) $leadflow_lead->id() ); ?>" />
							<input type="hidden" name="stage" value="<?php echo esc_attr( $leadflow_s->slug() ); ?>" />
							<?php Nonce::field( 'move_lead_' . $leadflow_lead->id() ); ?>
							<button type="submit" class="button lf-stagebar__outcome is-<?php echo esc_attr( $leadflow_s->type() ); ?>">
								<?php echo esc_html( sprintf( /* translators: %s: Stage name, e.g. "Won". */ __( 'Mark as %s', 'wp-leadflow-crm' ), $leadflow_s->name() ) ); ?>
							</button>
						</form>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</nav>

	<div class="lf-layout">
		<div class="lf-layout__main">
			<section class="lf-card" aria-labelledby="lf-details-title">
				<header class="lf-card__header">
					<h2 class="lf-card__title" id="lf-details-title"><?php esc_html_e( 'Details', 'wp-leadflow-crm' ); ?></h2>
				</header>
				<dl class="lf-details">
					<div>
						<dt><?php esc_html_e( 'Company', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php if ( null === $data['company'] ) : ?>
								<?php echo UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php elseif ( current_user_can( 'leadflow_view_companies' ) ) : ?>
								<a href="<?php echo esc_url( $data['company_admin']->view_url( $data['company']->id() ) ); ?>"><?php echo esc_html( $data['company']->name() ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $data['company']->name() ); ?>
							<?php endif; ?>
						</dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Contact', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php if ( null === $data['contact'] ) : ?>
								<?php echo UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php elseif ( current_user_can( 'leadflow_view_contacts' ) ) : ?>
								<a href="<?php echo esc_url( $data['contact_admin']->view_url( $data['contact']->id() ) ); ?>"><?php echo esc_html( $data['contact']->full_name() ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $data['contact']->full_name() ); ?>
							<?php endif; ?>
						</dd>
					</div>
					<div><dt><?php esc_html_e( 'Email', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::email( (string) $leadflow_lead->get( 'email', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Phone', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::phone( (string) $leadflow_lead->get( 'phone', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Value', 'wp-leadflow-crm' ); ?></dt><dd><?php echo esc_html( Format::money( $leadflow_lead->amount(), $leadflow_currency ) ); ?></dd></div>
					<div>
						<dt><?php esc_html_e( 'Win probability', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php if ( null === $leadflow_lead->get( 'probability' ) ) : ?>
								<?php echo UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<?php echo esc_html( (int) $leadflow_lead->get( 'probability' ) . '%' ); ?>
								<span class="lf-muted">
									<?php echo esc_html( sprintf( /* translators: %s: Weighted amount. */ __( '(weighted %s)', 'wp-leadflow-crm' ), Format::money( $leadflow_lead->weighted_amount(), $leadflow_currency ) ) ); ?>
								</span>
							<?php endif; ?>
						</dd>
					</div>
					<div><dt><?php esc_html_e( 'Source', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::text( (string) $leadflow_lead->get( 'source', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div>
						<dt><?php esc_html_e( 'Expected close date', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php if ( null === $leadflow_close ) : ?>
								<?php echo UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<?php echo esc_html( $leadflow_date( (string) $leadflow_close ) ); ?>
								<?php if ( $leadflow_lead->is_open() && $leadflow_close < current_time( 'Y-m-d' ) ) : ?>
									<span class="lf-pill lf-pill--lost"><?php esc_html_e( 'Overdue', 'wp-leadflow-crm' ); ?></span>
								<?php endif; ?>
							<?php endif; ?>
						</dd>
					</div>
					<?php if ( null !== $leadflow_lead->get( 'closed_at' ) ) : ?>
						<div><dt><?php esc_html_e( 'Closed', 'wp-leadflow-crm' ); ?></dt><dd><?php echo esc_html( Format::datetime( $leadflow_lead->get( 'closed_at' ) ) ); ?></dd></div>
					<?php endif; ?>
					<?php if ( 'lost' === $leadflow_status && '' !== (string) $leadflow_lead->get( 'lost_reason', '' ) ) : ?>
						<div><dt><?php esc_html_e( 'Lost reason', 'wp-leadflow-crm' ); ?></dt><dd><?php echo esc_html( (string) $leadflow_lead->get( 'lost_reason' ) ); ?></dd></div>
					<?php endif; ?>
					<?php if ( '' !== (string) $leadflow_lead->get( 'description', '' ) ) : ?>
						<div class="lf-details__wide">
							<dt><?php esc_html_e( 'Description', 'wp-leadflow-crm' ); ?></dt>
							<dd><?php echo wp_kses_post( wpautop( esc_html( (string) $leadflow_lead->get( 'description' ) ) ) ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
			</section>

			<?php \LeadFlow\CRM\Admin\RecordPanels::render_for( 'lead', $leadflow_lead ); ?>
		</div>

		<aside class="lf-layout__side" aria-label="<?php esc_attr_e( 'Lead summary', 'wp-leadflow-crm' ); ?>">
			<div class="lf-card">
				<dl class="lf-meta">
					<dt><?php esc_html_e( 'Status', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::badge( $leadflow_status, $leadflow_statuses[ $leadflow_status ] ?? $leadflow_status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php esc_html_e( 'Sales stage', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo null !== $leadflow_stage ? UI::stage( $leadflow_stage->name(), $leadflow_stage->color() ) : UI::text( (string) $leadflow_lead->get( 'stage' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt id="lf-assignee-label"><?php esc_html_e( 'Assigned to', 'wp-leadflow-crm' ); ?></dt>
					<dd>
						<?php if ( $data['can_assign'] ) : ?>
							<form class="lf-assign" method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
								<input type="hidden" name="action" value="leadflow_crm_assign_lead" />
								<input type="hidden" name="lead_id" value="<?php echo esc_attr( (string) $leadflow_lead->id() ); ?>" />
								<?php Nonce::field( 'assign_lead_' . $leadflow_lead->id() ); ?>
								<select name="owner_id" aria-labelledby="lf-assignee-label">
									<option value="0"><?php esc_html_e( 'Unassigned', 'wp-leadflow-crm' ); ?></option>
									<?php foreach ( $data['users'] as $leadflow_user_id => $leadflow_user_name ) : ?>
										<option value="<?php echo esc_attr( (string) $leadflow_user_id ); ?>" <?php selected( (int) $leadflow_lead->get( 'owner_id', 0 ), $leadflow_user_id ); ?>><?php echo esc_html( $leadflow_user_name ); ?></option>
									<?php endforeach; ?>
								</select>
								<button type="submit" class="button"><?php esc_html_e( 'Assign', 'wp-leadflow-crm' ); ?></button>
							</form>
						<?php else : ?>
							<?php echo UI::text( Format::user( $leadflow_lead->get( 'owner_id' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</dd>
					<dt><?php esc_html_e( 'Next follow-up', 'wp-leadflow-crm' ); ?></dt>
					<dd>
						<?php if ( null === $data['next_follow_up'] ) : ?>
							<a href="#lf-panel-follow-ups" data-lf-open-tab="follow-ups"><?php esc_html_e( 'Schedule one', 'wp-leadflow-crm' ); ?></a>
						<?php else : ?>
							<a href="#lf-panel-follow-ups" data-lf-open-tab="follow-ups"><?php echo esc_html( Format::due( $data['next_follow_up'] ) ); ?></a>
							<?php if ( $data['next_follow_up'] < current_time( 'mysql', true ) ) : ?>
								<span class="lf-pill lf-pill--overdue"><?php esc_html_e( 'Overdue', 'wp-leadflow-crm' ); ?></span>
							<?php endif; ?>
						<?php endif; ?>
					</dd>
					<dt><?php esc_html_e( 'Open tasks', 'wp-leadflow-crm' ); ?></dt>
					<dd>
						<a href="#lf-panel-tasks" data-lf-open-tab="tasks"><?php echo esc_html( number_format_i18n( (int) $data['open_tasks'] ) ); ?></a>
						<?php if ( $data['overdue_tasks'] > 0 ) : ?>
							<span class="lf-pill lf-pill--overdue">
								<?php
								/* translators: %d: Number of overdue tasks. */
								echo esc_html( sprintf( _n( '%d overdue', '%d overdue', (int) $data['overdue_tasks'], 'wp-leadflow-crm' ), (int) $data['overdue_tasks'] ) );
								?>
							</span>
						<?php endif; ?>
					</dd>
					<dt><?php esc_html_e( 'Created', 'wp-leadflow-crm' ); ?></dt>
					<dd>
						<?php echo esc_html( Format::datetime( $leadflow_lead->get( 'created_at' ) ) ); ?>
						<?php $leadflow_creator = Format::user( $leadflow_lead->get( 'created_by' ) ); ?>
						<?php if ( '' !== $leadflow_creator ) : ?>
							<br /><span class="lf-muted"><?php echo esc_html( sprintf( /* translators: %s: User name. */ __( 'by %s', 'wp-leadflow-crm' ), $leadflow_creator ) ); ?></span>
						<?php endif; ?>
					</dd>
					<dt><?php esc_html_e( 'Last updated', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo esc_html( Format::relative( $leadflow_lead->get( 'updated_at' ) ) ); ?></dd>
				</dl>
			</div>
		</aside>
	</div>
</div>
