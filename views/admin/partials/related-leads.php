<?php
/**
 * "Leads" card on company and contact profiles.
 *
 * @package LeadFlow\CRM
 *
 * @var array{leads: \LeadFlow\CRM\Data\Models\Lead[], total: int, list_url: string, add_url: string} $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Data\Repositories\StageRepository;
use LeadFlow\CRM\Admin\Menu;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_stages = leadflow_crm()->get( StageRepository::class );
?>
<div class="lf-related-leads">
	<?php if ( '' !== $data['add_url'] ) : ?>
		<p class="lf-panel-toolbar">
			<a class="button button-small" href="<?php echo esc_url( $data['add_url'] ); ?>"><?php esc_html_e( 'Add lead', 'wp-leadflow-crm' ); ?></a>
		</p>
	<?php endif; ?>

	<?php if ( empty( $data['leads'] ) ) : ?>
		<p class="lf-empty-text"><?php esc_html_e( 'No leads yet.', 'wp-leadflow-crm' ); ?></p>
	<?php else : ?>
		<table class="widefat striped lf-mini-table">
			<caption class="screen-reader-text"><?php esc_html_e( 'Leads', 'wp-leadflow-crm' ); ?></caption>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Lead', 'wp-leadflow-crm' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Stage', 'wp-leadflow-crm' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Value', 'wp-leadflow-crm' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Assigned to', 'wp-leadflow-crm' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['leads'] as $leadflow_lead ) : ?>
					<?php $leadflow_stage = $leadflow_stages->find_by_slug( (string) $leadflow_lead->get( 'stage' ) ); ?>
					<tr>
						<th scope="row"><a href="<?php echo esc_url( Menu::url( 'leadflow-crm-leads', array( 'action' => 'view', 'id' => $leadflow_lead->id() ) ) ); ?>"><?php echo esc_html( $leadflow_lead->title() ); ?></a></th>
						<td><?php echo null !== $leadflow_stage ? UI::stage( $leadflow_stage->name(), $leadflow_stage->color() ) : UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<td><?php echo esc_html( Format::money( $leadflow_lead->amount(), (string) $leadflow_lead->get( 'currency', '' ) ) ); ?></td>
						<td><?php echo UI::text( Format::user( $leadflow_lead->get( 'owner_id' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php if ( $data['total'] > count( $data['leads'] ) ) : ?>
			<p><a href="<?php echo esc_url( $data['list_url'] ); ?>"><?php esc_html_e( 'View all leads', 'wp-leadflow-crm' ); ?></a></p>
		<?php endif; ?>
	<?php endif; ?>
</div>
