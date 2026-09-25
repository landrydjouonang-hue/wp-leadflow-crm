<?php
/**
 * CRM dashboard: analytics, then work widgets.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     user_name: string,
 *     sections: array<string, array<string, mixed>>,
 *     settings_url: string,
 *     analytics: bool,
 *     range: \LeadFlow\CRM\Modules\Dashboard\DateRange,
 *     owner: string,
 *     owners: array<string, string>,
 *     report: array<string, mixed>|null
 * } $data
 */

use LeadFlow\CRM\Admin\Menu;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Dashboard\DateRange;

defined( 'ABSPATH' ) || exit;

$leadflow_actions = array();

if ( '' !== $data['settings_url'] ) {
	$leadflow_actions[] = array(
		'label' => __( 'Settings', 'wp-leadflow-crm' ),
		'url'   => $data['settings_url'],
	);
}

$leadflow_range  = $data['range'];
$leadflow_report = $data['report'];
?>
<div class="wrap lf-wrap lf-dashboard">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => __( 'Dashboard', 'wp-leadflow-crm' ),
			/* translators: %s: User display name. */
			'subtitle' => sprintf( __( 'Welcome back, %s. Here is how your sales are doing.', 'wp-leadflow-crm' ), $data['user_name'] ),
			'actions'  => $leadflow_actions,
		)
	);
	?>

	<?php if ( $data['analytics'] && null !== $leadflow_report ) : ?>
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="lf-card lf-dashboard-filters" data-lf-dashboard-filters>
			<input type="hidden" name="page" value="<?php echo esc_attr( Menu::SLUG ); ?>" />
			<div class="lf-dashboard-filters__field">
				<label for="lf-range"><?php esc_html_e( 'Period', 'wp-leadflow-crm' ); ?></label>
				<select id="lf-range" name="range" data-lf-range>
					<?php foreach ( DateRange::presets() as $leadflow_key => $leadflow_label ) : ?>
						<option value="<?php echo esc_attr( $leadflow_key ); ?>" <?php selected( $leadflow_range->key(), $leadflow_key ); ?>><?php echo esc_html( $leadflow_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="lf-dashboard-filters__field lf-dashboard-filters__dates" data-lf-custom-dates>
				<label for="lf-range-from"><?php esc_html_e( 'From', 'wp-leadflow-crm' ); ?></label>
				<input type="date" id="lf-range-from" name="from" value="<?php echo esc_attr( $leadflow_range->from() ); ?>" min="2000-01-01" max="2100-12-31" />
				<label for="lf-range-to"><?php esc_html_e( 'To', 'wp-leadflow-crm' ); ?></label>
				<input type="date" id="lf-range-to" name="to" value="<?php echo esc_attr( $leadflow_range->to() ); ?>" min="2000-01-01" max="2100-12-31" />
			</div>
			<?php if ( count( $data['owners'] ) > 1 ) : ?>
				<div class="lf-dashboard-filters__field">
					<label for="lf-owner"><?php esc_html_e( 'Owner', 'wp-leadflow-crm' ); ?></label>
					<select id="lf-owner" name="owner">
						<?php foreach ( $data['owners'] as $leadflow_value => $leadflow_label ) : ?>
							<option value="<?php echo esc_attr( (string) $leadflow_value ); ?>" <?php selected( $data['owner'], (string) $leadflow_value ); ?>><?php echo esc_html( $leadflow_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'wp-leadflow-crm' ); ?></button>
			<p class="lf-dashboard-filters__summary" aria-live="polite">
				<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
				<?php echo esc_html( $leadflow_range->label() ); ?>
			</p>
		</form>

		<h2 class="screen-reader-text"><?php esc_html_e( 'Key figures', 'wp-leadflow-crm' ); ?></h2>
		<ul class="lf-kpis">
			<?php foreach ( $leadflow_report['kpis'] as $leadflow_id => $leadflow_kpi ) : ?>
				<?php View::render( 'admin/partials/kpi', array( 'id' => $leadflow_id, 'kpi' => $leadflow_kpi ) ); ?>
			<?php endforeach; ?>
		</ul>

		<h2 class="screen-reader-text"><?php esc_html_e( 'Charts', 'wp-leadflow-crm' ); ?></h2>
		<div class="lf-charts">
			<?php foreach ( $leadflow_report['charts'] as $leadflow_id => $leadflow_chart ) : ?>
				<?php View::render( 'admin/partials/chart', array( 'id' => $leadflow_id, 'chart' => $leadflow_chart ) ); ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $data['sections'] ) ) : ?>
		<h2 class="lf-dashboard__heading"><?php esc_html_e( 'Your work', 'wp-leadflow-crm' ); ?></h2>
		<div class="lf-grid">
			<?php foreach ( $data['sections'] as $leadflow_section ) : ?>
				<?php
				$leadflow_heading_id = 'lf-section-' . $leadflow_section['id'];
				$leadflow_classes    = 'lf-card lf-section' . ( $leadflow_section['wide'] ? ' lf-card--wide' : '' );
				$leadflow_is_planned = ! is_callable( $leadflow_section['render'] );
				?>
				<section class="<?php echo esc_attr( $leadflow_classes . ( $leadflow_is_planned ? ' lf-section--placeholder' : '' ) ); ?>" aria-labelledby="<?php echo esc_attr( $leadflow_heading_id ); ?>">
					<header class="lf-section__header">
						<span class="lf-section__icon" aria-hidden="true">
							<span class="dashicons dashicons-<?php echo esc_attr( $leadflow_section['icon'] ); ?>"></span>
						</span>
						<h3 class="lf-section__title" id="<?php echo esc_attr( $leadflow_heading_id ); ?>"><?php echo esc_html( $leadflow_section['title'] ); ?></h3>
						<?php if ( $leadflow_is_planned ) : ?>
							<span class="lf-badge lf-badge--soon"><?php esc_html_e( 'Coming soon', 'wp-leadflow-crm' ); ?></span>
						<?php endif; ?>
					</header>

					<?php if ( $leadflow_is_planned ) : ?>
						<div class="lf-section__body">
							<?php if ( '' !== $leadflow_section['description'] ) : ?>
								<p class="lf-section__description"><?php echo esc_html( $leadflow_section['description'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $leadflow_section['features'] ) ) : ?>
								<ul class="lf-feature-list">
									<?php foreach ( $leadflow_section['features'] as $leadflow_feature ) : ?>
										<li><?php echo esc_html( $leadflow_feature ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<div class="lf-skeleton" aria-hidden="true">
								<span></span><span></span><span></span>
							</div>
						</div>
					<?php else : ?>
						<div class="lf-section__body">
							<?php call_user_func( $leadflow_section['render'], $leadflow_section ); ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>
	<?php elseif ( ! $data['analytics'] ) : ?>
		<div class="lf-card lf-empty">
			<p><?php esc_html_e( 'There is nothing to show on your dashboard yet.', 'wp-leadflow-crm' ); ?></p>
		</div>
	<?php endif; ?>
</div>
