<?php
/**
 * One dashboard chart with its data table.
 *
 * The chart is drawn by assets/js/charts.js from the `data-lf-chart`
 * configuration. The table is always in the page: it is the accessible
 * alternative and what users without JavaScript see (open by default,
 * collapsed by the script).
 *
 * @package LeadFlow\CRM
 *
 * @var array{id: string, chart: array<string, mixed>} $data
 */

defined( 'ABSPATH' ) || exit;

$leadflow_chart = $data['chart'];
$leadflow_id    = 'lf-chart-' . sanitize_html_class( $data['id'] );
$leadflow_total = 0.0;

foreach ( $leadflow_chart['datasets'] as $leadflow_set ) {
	$leadflow_total += array_sum( array_map( 'floatval', $leadflow_set['data'] ) );
}

$leadflow_wide = 'monthly' === $data['id'];
?>
<figure class="lf-card lf-chart lf-chart--<?php echo esc_attr( (string) $leadflow_chart['type'] ); ?><?php echo $leadflow_wide ? ' lf-chart--wide' : ''; ?>" aria-labelledby="<?php echo esc_attr( $leadflow_id ); ?>-title">
	<figcaption class="lf-chart__caption">
		<h3 class="lf-card__title" id="<?php echo esc_attr( $leadflow_id ); ?>-title"><?php echo esc_html( (string) $leadflow_chart['title'] ); ?></h3>
		<p class="lf-chart__description" id="<?php echo esc_attr( $leadflow_id ); ?>-desc"><?php echo esc_html( (string) $leadflow_chart['description'] ); ?></p>
	</figcaption>

	<?php if ( $leadflow_total <= 0 ) : ?>
		<p class="lf-chart__empty"><?php esc_html_e( 'No data for this period.', 'wp-leadflow-crm' ); ?></p>
	<?php else : ?>
		<div
			class="lf-chart__canvas"
			id="<?php echo esc_attr( $leadflow_id ); ?>"
			data-lf-chart="<?php echo esc_attr( (string) wp_json_encode( $leadflow_chart ) ); ?>"
			data-title-id="<?php echo esc_attr( $leadflow_id ); ?>-title"
			data-desc-id="<?php echo esc_attr( $leadflow_id ); ?>-desc"
		></div>

		<details class="lf-chart__data" open data-lf-chart-table>
			<summary><?php esc_html_e( 'Data table', 'wp-leadflow-crm' ); ?></summary>
			<div class="lf-table-scroll">
				<table class="widefat striped">
					<caption class="screen-reader-text"><?php echo esc_html( (string) $leadflow_chart['title'] ); ?></caption>
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Label', 'wp-leadflow-crm' ); ?></th>
							<?php foreach ( $leadflow_chart['datasets'] as $leadflow_set ) : ?>
								<th scope="col"><?php echo esc_html( (string) $leadflow_set['label'] ); ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $leadflow_chart['labels'] as $leadflow_i => $leadflow_label ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( (string) $leadflow_label ); ?></th>
								<?php foreach ( $leadflow_chart['datasets'] as $leadflow_set ) : ?>
									<td>
										<?php
										echo esc_html(
											isset( $leadflow_set['display'][ $leadflow_i ] )
												? (string) $leadflow_set['display'][ $leadflow_i ]
												: number_format_i18n( (float) ( $leadflow_set['data'][ $leadflow_i ] ?? 0 ) )
										);
										?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</details>
	<?php endif; ?>
</figure>
