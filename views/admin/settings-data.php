<?php
/**
 * Settings → Data (retention and export tools).
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     registry: \LeadFlow\CRM\Settings\Registry,
 *     group: string,
 *     policies: array<string, array<string, string|int>>,
 *     active: bool,
 *     last_run: array{time: int, counts: array<string, int>}|null,
 *     next_run: int|false,
 *     export_url: string
 * } $data
 */

use LeadFlow\CRM\Security\Nonce;
use LeadFlow\CRM\Settings\Registry;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;
?>
<h2 class="lf-card__title"><?php esc_html_e( 'Data retention', 'wp-leadflow-crm' ); ?></h2>
<p class="lf-muted">
	<?php esc_html_e( 'Keep only the data you need. Clean-up runs once a day in the background, in batches, and deleting is permanent — export first if you want a copy.', 'wp-leadflow-crm' ); ?>
</p>

<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" novalidate="novalidate">
	<?php
	settings_fields( $data['group'] );
	$data['registry']->tab_marker( 'data' );
	do_settings_sections( Registry::page_id( 'data' ) );
	submit_button();
	?>
</form>

<hr />

<h2 class="lf-card__title"><?php esc_html_e( 'Clean-up', 'wp-leadflow-crm' ); ?></h2>
<dl class="lf-meta">
	<dt><?php esc_html_e( 'Status', 'wp-leadflow-crm' ); ?></dt>
	<dd><?php echo esc_html( $data['active'] ? __( 'Active', 'wp-leadflow-crm' ) : __( 'Nothing is deleted automatically', 'wp-leadflow-crm' ) ); ?></dd>
	<dt><?php esc_html_e( 'Next run', 'wp-leadflow-crm' ); ?></dt>
	<dd>
		<?php
		echo esc_html(
			false === $data['next_run']
				? __( 'Not scheduled', 'wp-leadflow-crm' )
				: Format::datetime( gmdate( 'Y-m-d H:i:s', (int) $data['next_run'] ) )
		);
		?>
	</dd>
	<dt><?php esc_html_e( 'Last run', 'wp-leadflow-crm' ); ?></dt>
	<dd>
		<?php if ( null === $data['last_run'] ) : ?>
			<?php esc_html_e( 'Never', 'wp-leadflow-crm' ); ?>
		<?php else : ?>
			<?php echo esc_html( Format::datetime( gmdate( 'Y-m-d H:i:s', (int) $data['last_run']['time'] ) ) ); ?>
			<span class="lf-muted">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: Activities, 2: Emails, 3: Records. */
						__( '(%1$s activities, %2$s emails, %3$s trashed records)', 'wp-leadflow-crm' ),
						number_format_i18n( (int) ( $data['last_run']['counts']['activities'] ?? 0 ) ),
						number_format_i18n( (int) ( $data['last_run']['counts']['emails'] ?? 0 ) ),
						number_format_i18n( (int) ( $data['last_run']['counts']['trashed'] ?? 0 ) )
					)
				);
				?>
			</span>
		<?php endif; ?>
	</dd>
</dl>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-inline-form">
	<input type="hidden" name="action" value="leadflow_crm_run_retention" />
	<?php Nonce::field( 'run_retention' ); ?>
	<button type="submit" class="button" data-lf-confirm="<?php esc_attr_e( 'Run the clean-up now? Data older than your settings is deleted permanently.', 'wp-leadflow-crm' ); ?>" data-lf-busy="<?php esc_attr_e( 'Cleaning up…', 'wp-leadflow-crm' ); ?>">
		<?php esc_html_e( 'Run clean-up now', 'wp-leadflow-crm' ); ?>
	</button>
</form>

<hr />

<h2 class="lf-card__title"><?php esc_html_e( 'Export tools', 'wp-leadflow-crm' ); ?></h2>
<p class="lf-muted"><?php esc_html_e( 'Take your data with you: spreadsheets per record type, or one complete backup file.', 'wp-leadflow-crm' ); ?></p>
<p>
	<a class="button" href="<?php echo esc_url( $data['export_url'] ); ?>">
		<span class="dashicons dashicons-download" aria-hidden="true"></span>
		<?php esc_html_e( 'Go to export', 'wp-leadflow-crm' ); ?>
	</a>
</p>
