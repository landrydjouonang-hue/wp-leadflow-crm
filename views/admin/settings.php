<?php
/**
 * Settings screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array{tabs: array<string, array<string, mixed>>, current: string, registry: \LeadFlow\CRM\Settings\Registry, group: string} $data
 */

use LeadFlow\CRM\Admin\Menu;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Settings\SettingsModule;
use LeadFlow\CRM\Settings\Registry;

defined( 'ABSPATH' ) || exit;

$leadflow_tab = $data['tabs'][ $data['current'] ] ?? null;
?>
<div class="wrap lf-wrap">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => __( 'Settings', 'wp-leadflow-crm' ),
			'subtitle' => __( 'Configure how LeadFlow CRM works for your team.', 'wp-leadflow-crm' ),
		)
	);

	settings_errors();
	?>

	<nav class="nav-tab-wrapper lf-tabs" aria-label="<?php esc_attr_e( 'Settings sections', 'wp-leadflow-crm' ); ?>">
		<?php foreach ( $data['tabs'] as $leadflow_id => $leadflow_item ) : ?>
			<?php $leadflow_active = $leadflow_id === $data['current']; ?>
			<a
				href="<?php echo esc_url( Menu::url( SettingsModule::PAGE_SLUG, array( 'tab' => $leadflow_id ) ) ); ?>"
				class="nav-tab <?php echo $leadflow_active ? 'nav-tab-active' : ''; ?>"
				<?php echo $leadflow_active ? 'aria-current="page"' : ''; ?>
			><?php echo esc_html( $leadflow_item['label'] ); ?></a>
		<?php endforeach; ?>
	</nav>

	<div class="lf-card lf-settings">
		<?php if ( null === $leadflow_tab ) : ?>
			<p><?php esc_html_e( 'No settings are available.', 'wp-leadflow-crm' ); ?></p>
		<?php elseif ( is_callable( $leadflow_tab['render'] ) ) : ?>
			<?php call_user_func( $leadflow_tab['render'] ); ?>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" novalidate="novalidate">
				<?php
				settings_fields( $data['group'] );
				$data['registry']->tab_marker( $data['current'] );
				do_settings_sections( Registry::page_id( $data['current'] ) );
				submit_button();
				?>
			</form>
		<?php endif; ?>
	</div>
</div>
