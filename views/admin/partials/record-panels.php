<?php
/**
 * Tabbed record panels (progressively enhanced into ARIA tabs by admin.js).
 *
 * @package LeadFlow\CRM
 *
 * @var array{panels: array<string, array{id: string, title: string, count: int|null, render: callable}>, type: string} $data
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="lf-card lf-record-tabs" data-lf-tabs>
	<div class="lf-record-tabs__list hide-if-no-js" role="tablist" aria-label="<?php esc_attr_e( 'Record sections', 'wp-leadflow-crm' ); ?>">
		<?php $leadflow_first = true; ?>
		<?php foreach ( $data['panels'] as $leadflow_panel ) : ?>
			<button
				type="button"
				role="tab"
				class="lf-record-tabs__tab"
				id="lf-tab-<?php echo esc_attr( $leadflow_panel['id'] ); ?>"
				aria-controls="lf-panel-<?php echo esc_attr( $leadflow_panel['id'] ); ?>"
				aria-selected="<?php echo $leadflow_first ? 'true' : 'false'; ?>"
				tabindex="<?php echo $leadflow_first ? '0' : '-1'; ?>"
				data-lf-tab="<?php echo esc_attr( $leadflow_panel['id'] ); ?>"
			>
				<?php echo esc_html( $leadflow_panel['title'] ); ?>
				<?php if ( null !== $leadflow_panel['count'] ) : ?>
					<span class="lf-count"><?php echo esc_html( number_format_i18n( $leadflow_panel['count'] ) ); ?></span>
				<?php endif; ?>
			</button>
			<?php $leadflow_first = false; ?>
		<?php endforeach; ?>
	</div>

	<?php foreach ( $data['panels'] as $leadflow_panel ) : ?>
		<section
			class="lf-record-tabs__panel"
			id="lf-panel-<?php echo esc_attr( $leadflow_panel['id'] ); ?>"
			role="tabpanel"
			aria-labelledby="lf-tab-<?php echo esc_attr( $leadflow_panel['id'] ); ?>"
			tabindex="0"
		>
			<h2 class="lf-record-tabs__heading"><?php echo esc_html( $leadflow_panel['title'] ); ?></h2>
			<?php call_user_func( $leadflow_panel['render'] ); ?>
		</section>
	<?php endforeach; ?>
</div>
