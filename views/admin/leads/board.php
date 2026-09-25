<?php
/**
 * Pipeline board.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     admin: \LeadFlow\CRM\Modules\Leads\LeadsAdmin,
 *     columns: array<int, array{stage: \LeadFlow\CRM\Data\Models\Stage, cards: \LeadFlow\CRM\Data\Models\Lead[]}>,
 *     totals: array<string, array{count: int, amounts: array<string, float>}>,
 *     companies: array<int, \LeadFlow\CRM\Data\Models\Company>,
 *     users: array<int, string>,
 *     filters: array{s: string, owner: int},
 *     nonce: string
 * } $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Leads\PipelineBoard;
use LeadFlow\CRM\Security\Nonce;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_admin   = $data['admin'];
$leadflow_actions = array(
	array(
		'label' => __( 'List view', 'wp-leadflow-crm' ),
		'url'   => $leadflow_admin->url(),
	),
);

if ( current_user_can( $leadflow_admin->cap( 'edit' ) ) ) {
	$leadflow_actions[] = array(
		'label'   => __( 'Add lead', 'wp-leadflow-crm' ),
		'url'     => $leadflow_admin->new_url(),
		'primary' => true,
	);
}

$leadflow_stage_options = array();
foreach ( $data['columns'] as $leadflow_column ) {
	$leadflow_stage_options[ $leadflow_column['stage']->slug() ] = $leadflow_column['stage']->name();
}
$leadflow_filtered = '' !== $data['filters']['s'] || $data['filters']['owner'] > 0;
?>
<div class="wrap lf-wrap lf-pipeline">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => __( 'Sales pipeline', 'wp-leadflow-crm' ),
			'subtitle' => __( 'Drag cards between stages, or use “Move to” on a card.', 'wp-leadflow-crm' ),
			'actions'  => $leadflow_actions,
		)
	);
	?>

	<form method="get" class="lf-board-filters" role="search">
		<input type="hidden" name="page" value="<?php echo esc_attr( PipelineBoard::PAGE_SLUG ); ?>" />
		<label class="screen-reader-text" for="lf-board-search"><?php esc_html_e( 'Search leads', 'wp-leadflow-crm' ); ?></label>
		<input type="search" id="lf-board-search" name="s" value="<?php echo esc_attr( $data['filters']['s'] ); ?>" placeholder="<?php esc_attr_e( 'Search leads…', 'wp-leadflow-crm' ); ?>" />
		<label class="screen-reader-text" for="lf-board-owner"><?php esc_html_e( 'Filter by assignee', 'wp-leadflow-crm' ); ?></label>
		<select id="lf-board-owner" name="owner">
			<option value=""><?php esc_html_e( 'All assignees', 'wp-leadflow-crm' ); ?></option>
			<?php foreach ( $data['users'] as $leadflow_user_id => $leadflow_user_name ) : ?>
				<option value="<?php echo esc_attr( (string) $leadflow_user_id ); ?>" <?php selected( $data['filters']['owner'], $leadflow_user_id ); ?>><?php echo esc_html( $leadflow_user_name ); ?></option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="button"><?php esc_html_e( 'Filter', 'wp-leadflow-crm' ); ?></button>
		<a class="button-link" href="<?php echo esc_url( PipelineBoard::url( array( 'owner' => get_current_user_id() ) ) ); ?>"><?php esc_html_e( 'My leads', 'wp-leadflow-crm' ); ?></a>
		<?php if ( $leadflow_filtered ) : ?>
			<a class="button-link" href="<?php echo esc_url( PipelineBoard::url() ); ?>"><?php esc_html_e( 'Clear filters', 'wp-leadflow-crm' ); ?></a>
		<?php endif; ?>
	</form>

	<div class="notice notice-error inline lf-board-message" data-lf-board-message hidden><p></p></div>

	<div
		class="lf-board"
		data-lf-board
		data-nonce="<?php echo esc_attr( $data['nonce'] ); ?>"
		data-filter-s="<?php echo esc_attr( $data['filters']['s'] ); ?>"
		data-filter-owner="<?php echo esc_attr( (string) $data['filters']['owner'] ); ?>"
	>
		<?php foreach ( $data['columns'] as $leadflow_column ) : ?>
			<?php
			$leadflow_stage = $leadflow_column['stage'];
			$leadflow_slug  = $leadflow_stage->slug();
			$leadflow_total = $data['totals'][ $leadflow_slug ] ?? array(
				'count'   => 0,
				'amounts' => array(),
			);
			$leadflow_hid   = 'lf-stage-' . $leadflow_slug;
			?>
			<section class="lf-column lf-column--<?php echo esc_attr( $leadflow_stage->type() ); ?>" data-stage="<?php echo esc_attr( $leadflow_slug ); ?>" aria-labelledby="<?php echo esc_attr( $leadflow_hid ); ?>" style="--lf-stage-color: <?php echo esc_attr( $leadflow_stage->color() ); ?>">
				<header class="lf-column__header">
					<h2 class="lf-column__title" id="<?php echo esc_attr( $leadflow_hid ); ?>">
						<?php echo esc_html( $leadflow_stage->name() ); ?>
						<span class="lf-count" data-lf-count><?php echo esc_html( number_format_i18n( $leadflow_total['count'] ) ); ?></span>
					</h2>
					<p class="lf-column__value" data-lf-value><?php echo esc_html( Format::money_list( $leadflow_total['amounts'] ) ); ?></p>
				</header>

				<ol class="lf-column__cards" data-lf-dropzone aria-label="<?php echo esc_attr( sprintf( /* translators: %s: Stage name. */ __( 'Leads in %s', 'wp-leadflow-crm' ), $leadflow_stage->name() ) ); ?>">
					<?php foreach ( $leadflow_column['cards'] as $leadflow_lead ) : ?>
						<?php
						$leadflow_can_move = current_user_can( $leadflow_admin->meta_cap( 'edit' ), $leadflow_lead->id() );
						$leadflow_company  = $data['companies'][ (int) $leadflow_lead->get( 'company_id', 0 ) ] ?? null;
						$leadflow_owner    = Format::user( $leadflow_lead->get( 'owner_id' ) );
						$leadflow_close    = $leadflow_lead->get( 'expected_close_date' );
						?>
						<li class="lf-deal<?php echo $leadflow_can_move ? ' is-movable' : ''; ?>" data-lead-id="<?php echo esc_attr( (string) $leadflow_lead->id() ); ?>" data-title="<?php echo esc_attr( $leadflow_lead->title() ); ?>" <?php echo $leadflow_can_move ? 'draggable="true"' : ''; ?>>
							<a class="lf-deal__title" href="<?php echo esc_url( $leadflow_admin->view_url( $leadflow_lead->id() ) ); ?>"><?php echo esc_html( $leadflow_lead->title() ); ?></a>
							<?php if ( null !== $leadflow_company ) : ?>
								<span class="lf-deal__company"><?php echo esc_html( $leadflow_company->name() ); ?></span>
							<?php endif; ?>
							<div class="lf-deal__meta">
								<strong class="lf-deal__value"><?php echo esc_html( Format::money( $leadflow_lead->amount(), (string) $leadflow_lead->get( 'currency', '' ) ) ); ?></strong>
								<?php if ( null !== $leadflow_close ) : ?>
									<span class="lf-deal__date<?php echo ( $leadflow_lead->is_open() && $leadflow_close < current_time( 'Y-m-d' ) ) ? ' is-overdue' : ''; ?>">
										<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
										<span class="screen-reader-text"><?php esc_html_e( 'Expected close:', 'wp-leadflow-crm' ); ?></span>
										<?php echo esc_html( (string) wp_date( 'M j', (int) strtotime( $leadflow_close . ' 12:00:00 UTC' ) ) ); ?>
									</span>
								<?php endif; ?>
								<?php if ( '' !== $leadflow_owner ) : ?>
									<span class="lf-deal__owner" title="<?php echo esc_attr( $leadflow_owner ); ?>">
										<?php echo UI::avatar( $leadflow_owner, 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in UI. ?>
										<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: User name. */ __( 'Assigned to %s', 'wp-leadflow-crm' ), $leadflow_owner ) ); ?></span>
									</span>
								<?php endif; ?>
							</div>

							<?php if ( $leadflow_can_move ) : ?>
								<form class="lf-deal__move" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<input type="hidden" name="action" value="leadflow_crm_move_lead" />
									<input type="hidden" name="lead_id" value="<?php echo esc_attr( (string) $leadflow_lead->id() ); ?>" />
									<?php Nonce::field( 'move_lead_' . $leadflow_lead->id() ); ?>
									<label class="screen-reader-text" for="lf-move-<?php echo esc_attr( (string) $leadflow_lead->id() ); ?>">
										<?php echo esc_html( sprintf( /* translators: %s: Lead name. */ __( 'Move “%s” to stage', 'wp-leadflow-crm' ), $leadflow_lead->title() ) ); ?>
									</label>
									<select id="lf-move-<?php echo esc_attr( (string) $leadflow_lead->id() ); ?>" name="stage" data-lf-move>
										<?php foreach ( $leadflow_stage_options as $leadflow_option_slug => $leadflow_option_name ) : ?>
											<option value="<?php echo esc_attr( $leadflow_option_slug ); ?>" <?php selected( $leadflow_slug, $leadflow_option_slug ); ?>><?php echo esc_html( $leadflow_option_name ); ?></option>
										<?php endforeach; ?>
									</select>
									<button type="submit" class="button button-small hide-if-js"><?php esc_html_e( 'Move', 'wp-leadflow-crm' ); ?></button>
								</form>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
					<li class="lf-column__empty"<?php echo empty( $leadflow_column['cards'] ) ? '' : ' hidden'; ?>><?php esc_html_e( 'No leads', 'wp-leadflow-crm' ); ?></li>
				</ol>

				<?php if ( $leadflow_total['count'] > count( $leadflow_column['cards'] ) ) : ?>
					<p class="lf-column__more">
						<a href="<?php echo esc_url( $leadflow_admin->url( array( 'stage' => $leadflow_slug ) ) ); ?>">
							<?php echo esc_html( sprintf( /* translators: %d: Number of leads. */ __( 'View all %d in the list', 'wp-leadflow-crm' ), $leadflow_total['count'] ) ); ?>
						</a>
					</p>
				<?php endif; ?>

				<?php if ( 'open' === $leadflow_stage->type() && current_user_can( $leadflow_admin->cap( 'edit' ) ) ) : ?>
					<a class="lf-column__add" href="<?php echo esc_url( $leadflow_admin->new_url( array( 'stage' => $leadflow_slug ) ) ); ?>">
						+ <?php echo esc_html( sprintf( /* translators: %s: Stage name. */ __( 'Add lead to %s', 'wp-leadflow-crm' ), $leadflow_stage->name() ) ); ?>
					</a>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
</div>
