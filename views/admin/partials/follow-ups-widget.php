<?php
/**
 * "My follow-ups" dashboard widget.
 *
 * @package LeadFlow\CRM
 *
 * @var array{items: \LeadFlow\CRM\Data\Models\FollowUp[], links: array<int, array<string, array{label: string, url: string}>>} $data
 */

use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;
?>
<div class="lf-widget">
	<?php if ( empty( $data['items'] ) ) : ?>
		<p class="lf-empty-text"><?php esc_html_e( 'No follow-ups due in the next 7 days.', 'wp-leadflow-crm' ); ?></p>
	<?php else : ?>
		<ul class="lf-due-list">
			<?php foreach ( $data['items'] as $leadflow_fu ) : ?>
				<?php
				$leadflow_links = $data['links'][ $leadflow_fu->id() ] ?? array();
				$leadflow_link  = $leadflow_links['lead'] ?? $leadflow_links['contact'] ?? $leadflow_links['company'] ?? null;
				?>
				<li class="<?php echo $leadflow_fu->is_overdue() ? 'is-overdue' : ''; ?>">
					<span class="lf-due-list__when">
						<?php echo esc_html( Format::due( $leadflow_fu->get( 'scheduled_at' ) ) ); ?>
						<?php if ( $leadflow_fu->is_overdue() ) : ?>
							<span class="lf-pill lf-pill--overdue"><?php esc_html_e( 'Overdue', 'wp-leadflow-crm' ); ?></span>
						<?php endif; ?>
					</span>
					<?php if ( null !== $leadflow_link && '' !== $leadflow_link['url'] ) : ?>
						<a href="<?php echo esc_url( $leadflow_link['url'] . '#lf-panel-follow-ups' ); ?>"><?php echo esc_html( (string) $leadflow_fu->get( 'subject' ) ); ?></a>
					<?php else : ?>
						<span><?php echo esc_html( (string) $leadflow_fu->get( 'subject' ) ); ?></span>
					<?php endif; ?>
					<?php if ( null !== $leadflow_link ) : ?>
						<span class="lf-muted"><?php echo esc_html( $leadflow_link['label'] ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
