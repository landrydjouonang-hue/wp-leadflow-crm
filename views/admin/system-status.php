<?php
/**
 * System status report and debug log.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     rows: array<int, array{label: string, value: string, status: string}>,
 *     log_enabled: bool,
 *     log_size: int,
 *     log_lines: string[]
 * } $data
 */

use LeadFlow\CRM\Modules\Settings\SettingsModule;
use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_status_labels = array(
	'ok'      => __( 'OK', 'wp-leadflow-crm' ),
	'warning' => __( 'Warning', 'wp-leadflow-crm' ),
	'error'   => __( 'Error', 'wp-leadflow-crm' ),
	'info'    => __( 'Info', 'wp-leadflow-crm' ),
);
$leadflow_post_url      = admin_url( 'admin-post.php' );
?>
<div class="lf-status">
	<div class="lf-status__intro">
		<p><?php esc_html_e( 'Share this report with support when you need help. It contains no CRM records or personal data.', 'wp-leadflow-crm' ); ?></p>
		<div class="lf-status__actions">
			<button type="button" class="button" data-lf-copy-status>
				<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
				<span data-lf-label><?php esc_html_e( 'Copy report', 'wp-leadflow-crm' ); ?></span>
			</button>
			<form method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
				<input type="hidden" name="action" value="leadflow_crm_download_status" />
				<?php Nonce::field( 'download_status' ); ?>
				<button type="submit" class="button">
					<span class="dashicons dashicons-download" aria-hidden="true"></span>
					<?php esc_html_e( 'Download report', 'wp-leadflow-crm' ); ?>
				</button>
			</form>
		</div>
	</div>

	<table class="widefat striped lf-status__table">
		<caption class="screen-reader-text"><?php esc_html_e( 'System status', 'wp-leadflow-crm' ); ?></caption>
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Item', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Value', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'wp-leadflow-crm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $data['rows'] as $leadflow_row ) : ?>
				<?php $leadflow_state = isset( $leadflow_status_labels[ $leadflow_row['status'] ] ) ? $leadflow_row['status'] : 'info'; ?>
				<tr>
					<th scope="row"><?php echo esc_html( $leadflow_row['label'] ); ?></th>
					<td><code><?php echo esc_html( $leadflow_row['value'] ); ?></code></td>
					<td>
						<span class="lf-status-pill lf-status-pill--<?php echo esc_attr( $leadflow_state ); ?>">
							<?php echo esc_html( $leadflow_status_labels[ $leadflow_state ] ); ?>
						</span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h3 class="lf-subheading"><?php esc_html_e( 'Debug log', 'wp-leadflow-crm' ); ?></h3>

	<?php if ( ! $data['log_enabled'] && empty( $data['log_lines'] ) ) : ?>
		<p class="lf-empty-text">
			<?php esc_html_e( 'Debug logging is off, so there is nothing to show.', 'wp-leadflow-crm' ); ?>
			<a href="<?php echo esc_url( \LeadFlow\CRM\Admin\Menu::url( SettingsModule::PAGE_SLUG, array( 'tab' => 'advanced' ) ) ); ?>"><?php esc_html_e( 'Turn it on in Advanced', 'wp-leadflow-crm' ); ?></a>
		</p>
	<?php else : ?>
		<p class="lf-muted">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: Number of lines, 2: File size. */
					__( 'Last %1$s entries (newest first) of %2$s. The file is private and never served over the web.', 'wp-leadflow-crm' ),
					number_format_i18n( count( $data['log_lines'] ) ),
					size_format( max( 0, $data['log_size'] ) )
				)
			);
			?>
		</p>

		<?php if ( empty( $data['log_lines'] ) ) : ?>
			<p class="lf-empty-text"><?php esc_html_e( 'Nothing has been logged yet.', 'wp-leadflow-crm' ); ?></p>
		<?php else : ?>
			<pre class="lf-code lf-log" tabindex="0" aria-label="<?php esc_attr_e( 'Debug log', 'wp-leadflow-crm' ); ?>"><?php echo esc_html( implode( "\n", $data['log_lines'] ) ); ?></pre>
		<?php endif; ?>

		<div class="lf-status__actions">
			<form method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
				<input type="hidden" name="action" value="leadflow_crm_download_log" />
				<?php Nonce::field( 'download_log' ); ?>
				<button type="submit" class="button"><?php esc_html_e( 'Download log', 'wp-leadflow-crm' ); ?></button>
			</form>
			<form method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
				<input type="hidden" name="action" value="leadflow_crm_clear_log" />
				<?php Nonce::field( 'clear_log' ); ?>
				<button type="submit" class="button" data-lf-confirm="<?php esc_attr_e( 'Delete the debug log?', 'wp-leadflow-crm' ); ?>"><?php esc_html_e( 'Clear log', 'wp-leadflow-crm' ); ?></button>
			</form>
		</div>
	<?php endif; ?>
</div>
