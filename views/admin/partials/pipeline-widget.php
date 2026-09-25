<?php
/**
 * Dashboard pipeline summary.
 *
 * @package LeadFlow\CRM
 *
 * @var array{stages: \LeadFlow\CRM\Data\Models\Stage[], totals: array<string, array{count: int, amounts: array<string, float>}>, open_value: array<string, float>, board_url: string} $data
 */

use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_max = 1;
foreach ( $data['totals'] as $leadflow_total ) {
	$leadflow_max = max( $leadflow_max, (int) $leadflow_total['count'] );
}
?>
<div class="lf-widget lf-pipeline-widget">
	<p class="lf-widget__total">
		<span class="lf-widget__number"><?php echo esc_html( Format::money_list( $data['open_value'] ) ); ?></span>
		<span class="lf-widget__label"><?php esc_html_e( 'open pipeline value', 'wp-leadflow-crm' ); ?></span>
	</p>

	<table class="lf-pipeline-bars">
		<caption class="screen-reader-text"><?php esc_html_e( 'Leads per stage', 'wp-leadflow-crm' ); ?></caption>
		<thead class="screen-reader-text">
			<tr>
				<th scope="col"><?php esc_html_e( 'Stage', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Leads', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Value', 'wp-leadflow-crm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $data['stages'] as $leadflow_stage ) : ?>
				<?php
				$leadflow_total = $data['totals'][ $leadflow_stage->slug() ] ?? array(
					'count'   => 0,
					'amounts' => array(),
				);
				$leadflow_width = (int) round( 100 * $leadflow_total['count'] / $leadflow_max );
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $leadflow_stage->name() ); ?></th>
					<td class="lf-pipeline-bars__bar">
						<span class="lf-pipeline-bars__fill" style="width: <?php echo esc_attr( (string) $leadflow_width ); ?>%; background: <?php echo esc_attr( $leadflow_stage->color() ); ?>" aria-hidden="true"></span>
						<span class="lf-pipeline-bars__count"><?php echo esc_html( number_format_i18n( $leadflow_total['count'] ) ); ?></span>
					</td>
					<td class="lf-pipeline-bars__value"><?php echo esc_html( Format::money_list( $leadflow_total['amounts'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="lf-widget__actions">
		<a class="button button-primary" href="<?php echo esc_url( $data['board_url'] ); ?>"><?php esc_html_e( 'Open pipeline', 'wp-leadflow-crm' ); ?></a>
	</p>
</div>
