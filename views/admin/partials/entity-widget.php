<?php
/**
 * Dashboard summary widget for an entity (companies, contacts).
 *
 * @package LeadFlow\CRM
 *
 * @var array{admin: \LeadFlow\CRM\Admin\Crud\EntityAdmin, counts: array<string, int>, statuses: array<string, string>, recent: \LeadFlow\CRM\Data\Model[], can_add: bool} $data
 */

use LeadFlow\CRM\Admin\UI;

defined( 'ABSPATH' ) || exit;

$leadflow_admin  = $data['admin'];
$leadflow_labels = $leadflow_admin->labels();
$leadflow_total  = array_sum( $data['counts'] );
?>
<div class="lf-widget">
	<p class="lf-widget__total">
		<span class="lf-widget__number"><?php echo esc_html( number_format_i18n( $leadflow_total ) ); ?></span>
		<span class="lf-widget__label"><?php echo esc_html( $leadflow_labels['plural'] ); ?></span>
	</p>

	<?php if ( $leadflow_total > 0 ) : ?>
		<ul class="lf-widget__statuses">
			<?php foreach ( $data['statuses'] as $leadflow_status => $leadflow_label ) : ?>
				<?php if ( ! empty( $data['counts'][ $leadflow_status ] ) ) : ?>
					<li>
						<a href="<?php echo esc_url( $leadflow_admin->url( array( 'status' => $leadflow_status ) ) ); ?>">
							<?php echo UI::badge( $leadflow_status, $leadflow_label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in UI. ?>
							<span class="lf-widget__count"><?php echo esc_html( number_format_i18n( $data['counts'][ $leadflow_status ] ) ); ?></span>
						</a>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>

		<h3 class="lf-widget__subtitle"><?php esc_html_e( 'Recently added', 'wp-leadflow-crm' ); ?></h3>
		<ul class="lf-widget__recent">
			<?php foreach ( $data['recent'] as $leadflow_item ) : ?>
				<li>
					<?php echo UI::avatar( $leadflow_admin->title_of( $leadflow_item ), 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in UI. ?>
					<a href="<?php echo esc_url( $leadflow_admin->view_url( $leadflow_item->id() ) ); ?>"><?php echo esc_html( $leadflow_admin->title_of( $leadflow_item ) ); ?></a>
					<?php echo UI::time( $leadflow_item->get( 'created_at' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in UI. ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="lf-empty-text"><?php echo esc_html( $leadflow_labels['not_found'] ); ?></p>
	<?php endif; ?>

	<p class="lf-widget__actions">
		<a class="button" href="<?php echo esc_url( $leadflow_admin->url() ); ?>">
			<?php
			/* translators: %s: Plural entity name, e.g. "Companies". */
			echo esc_html( sprintf( __( 'View all %s', 'wp-leadflow-crm' ), mb_strtolower( $leadflow_labels['plural'] ) ) );
			?>
		</a>
		<?php if ( $data['can_add'] ) : ?>
			<a class="button button-primary" href="<?php echo esc_url( $leadflow_admin->new_url() ); ?>"><?php echo esc_html( $leadflow_labels['add_new'] ); ?></a>
		<?php endif; ?>
	</p>
</div>
