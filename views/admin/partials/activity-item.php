<?php
/**
 * One timeline entry.
 *
 * @package LeadFlow\CRM
 *
 * @var array{activity: \LeadFlow\CRM\Data\Models\Activity, origin: array{label: string, url: string}|null} $data
 */

use LeadFlow\CRM\Modules\Activities\ActivitiesModule;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_activity = $data['activity'];
$leadflow_type     = $leadflow_activity->type();
$leadflow_user     = Format::user( $leadflow_activity->get( 'user_id' ) );
$leadflow_when     = $leadflow_activity->get( 'occurred_at' );
$leadflow_desc     = (string) $leadflow_activity->get( 'description', '' );
?>
<li class="lf-activity lf-activity--<?php echo esc_attr( sanitize_html_class( $leadflow_type ) ); ?>" data-activity-id="<?php echo esc_attr( (string) $leadflow_activity->id() ); ?>">
	<span class="lf-activity__icon" aria-hidden="true"><span class="dashicons dashicons-<?php echo esc_attr( ActivitiesModule::icon( $leadflow_type ) ); ?>"></span></span>
	<div class="lf-activity__body">
		<p class="lf-activity__subject">
			<strong><?php echo esc_html( (string) $leadflow_activity->get( 'subject', '' ) ); ?></strong>
			<?php if ( null !== $data['origin'] ) : ?>
				<span class="lf-muted">
					<?php esc_html_e( 'on', 'wp-leadflow-crm' ); ?>
					<?php if ( '' !== $data['origin']['url'] ) : ?>
						<a href="<?php echo esc_url( $data['origin']['url'] ); ?>"><?php echo esc_html( $data['origin']['label'] ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $data['origin']['label'] ); ?>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</p>
		<?php if ( '' !== $leadflow_desc ) : ?>
			<div class="lf-activity__description"><?php echo wp_kses_post( wpautop( esc_html( $leadflow_desc ) ) ); ?></div>
		<?php endif; ?>
		<p class="lf-activity__meta">
			<?php if ( '' !== $leadflow_user ) : ?>
				<span><?php echo esc_html( $leadflow_user ); ?></span>
				<span aria-hidden="true">·</span>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( Format::iso( $leadflow_when ) ); ?>" title="<?php echo esc_attr( Format::datetime( $leadflow_when ) ); ?>"><?php echo esc_html( Format::relative( $leadflow_when ) ); ?></time>
		</p>
	</div>
</li>
