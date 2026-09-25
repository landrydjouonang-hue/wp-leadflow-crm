<?php
/**
 * Access control screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array<string, mixed> $data
 */

use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Access\AccessModule;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap lf-wrap lf-access">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => __( 'Access control', 'wp-leadflow-crm' ),
			'subtitle' => __( 'Decide who can see and change what in the CRM, in the admin and through the API.', 'wp-leadflow-crm' ),
		)
	);
	?>

	<nav class="nav-tab-wrapper lf-tabs" aria-label="<?php esc_attr_e( 'Access control sections', 'wp-leadflow-crm' ); ?>">
		<?php foreach ( $data['tabs'] as $leadflow_key => $leadflow_label ) : ?>
			<a href="<?php echo esc_url( AccessModule::url( $leadflow_key ) ); ?>" class="nav-tab<?php echo $leadflow_key === $data['tab'] ? ' nav-tab-active' : ''; ?>"<?php echo $leadflow_key === $data['tab'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $leadflow_label ); ?></a>
		<?php endforeach; ?>
	</nav>

	<?php View::render( 'admin/access/' . $data['tab'], $data ); ?>
</div>
