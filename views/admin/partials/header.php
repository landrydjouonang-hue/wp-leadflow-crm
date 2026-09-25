<?php
/**
 * CRM page header.
 *
 * @package LeadFlow\CRM
 *
 * @var array{title: string, subtitle?: string, before_title?: string, badge?: string, actions?: array<int, array{label: string, url: string, primary?: bool, class?: string, confirm?: string}>} $data
 *
 * `before_title` and `badge` must be pre-escaped HTML.
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="lf-header">
	<div class="lf-header__brand">
		<span class="lf-header__logo" aria-hidden="true">
			<span class="dashicons dashicons-groups"></span>
		</span>
		<div>
			<p class="lf-header__product">
				<?php esc_html_e( 'LeadFlow CRM', 'wp-leadflow-crm' ); ?>
				<span class="lf-badge lf-badge--muted">
					<?php
					/* translators: %s: Plugin version. */
					echo esc_html( sprintf( __( 'v%s', 'wp-leadflow-crm' ), LEADFLOW_CRM_VERSION ) );
					?>
				</span>
			</p>
			<h1 class="lf-header__title">
				<?php echo $data['before_title'] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped by caller. ?>
				<span><?php echo esc_html( $data['title'] ); ?></span>
				<?php echo $data['badge'] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped by caller. ?>
			</h1>
			<?php if ( ! empty( $data['subtitle'] ) ) : ?>
				<p class="lf-header__subtitle"><?php echo esc_html( $data['subtitle'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( ! empty( $data['actions'] ) ) : ?>
		<div class="lf-header__actions">
			<?php foreach ( $data['actions'] as $action ) : ?>
				<a
					class="<?php echo esc_attr( trim( 'button ' . ( ! empty( $action['primary'] ) ? 'button-primary ' : '' ) . ( $action['class'] ?? '' ) ) ); ?>"
					href="<?php echo esc_url( $action['url'] ); ?>"
					<?php if ( ! empty( $action['confirm'] ) ) : ?>
						data-lf-confirm="<?php echo esc_attr( $action['confirm'] ); ?>"
					<?php endif; ?>
				><?php echo esc_html( $action['label'] ); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</header>
<hr class="wp-header-end" />
