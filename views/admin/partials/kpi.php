<?php
/**
 * One KPI card.
 *
 * @package LeadFlow\CRM
 *
 * @var array{id: string, kpi: array{label: string, value: string, hint?: string, icon?: string, tone?: string, url?: string, trend?: array{text: string, direction: string, good: bool, label: string}|null}} $data
 */

defined( 'ABSPATH' ) || exit;

$leadflow_kpi   = $data['kpi'];
$leadflow_trend = $leadflow_kpi['trend'] ?? null;
$leadflow_tone  = (string) ( $leadflow_kpi['tone'] ?? '' );
$leadflow_id    = 'lf-kpi-' . sanitize_html_class( $data['id'] );
?>
<li class="lf-kpi<?php echo '' !== $leadflow_tone ? ' lf-kpi--' . esc_attr( $leadflow_tone ) : ''; ?>" data-kpi="<?php echo esc_attr( $data['id'] ); ?>">
	<p class="lf-kpi__label" id="<?php echo esc_attr( $leadflow_id ); ?>">
		<span class="dashicons dashicons-<?php echo esc_attr( (string) ( $leadflow_kpi['icon'] ?? 'chart-bar' ) ); ?>" aria-hidden="true"></span>
		<?php echo esc_html( $leadflow_kpi['label'] ); ?>
	</p>
	<p class="lf-kpi__value" aria-describedby="<?php echo esc_attr( $leadflow_id ); ?>">
		<?php if ( ! empty( $leadflow_kpi['url'] ) ) : ?>
			<a href="<?php echo esc_url( $leadflow_kpi['url'] ); ?>"><?php echo esc_html( $leadflow_kpi['value'] ); ?><span class="screen-reader-text"> <?php echo esc_html( $leadflow_kpi['label'] ); ?></span></a>
		<?php else : ?>
			<?php echo esc_html( $leadflow_kpi['value'] ); ?>
		<?php endif; ?>
	</p>
	<p class="lf-kpi__meta">
		<?php if ( is_array( $leadflow_trend ) ) : ?>
			<span class="lf-trend lf-trend--<?php echo esc_attr( $leadflow_trend['direction'] ); ?> <?php echo $leadflow_trend['good'] ? 'is-good' : 'is-bad'; ?>" title="<?php echo esc_attr( $leadflow_trend['label'] ); ?>">
				<span class="dashicons dashicons-arrow-<?php echo esc_attr( 'up' === $leadflow_trend['direction'] ? 'up' : ( 'down' === $leadflow_trend['direction'] ? 'down' : 'right' ) ); ?>-alt" aria-hidden="true"></span>
				<?php echo esc_html( $leadflow_trend['text'] ); ?>
				<span class="screen-reader-text"><?php echo esc_html( $leadflow_trend['label'] ); ?></span>
			</span>
		<?php endif; ?>
		<?php if ( ! empty( $leadflow_kpi['hint'] ) ) : ?>
			<span class="lf-kpi__hint"><?php echo esc_html( $leadflow_kpi['hint'] ); ?></span>
		<?php endif; ?>
	</p>
</li>
