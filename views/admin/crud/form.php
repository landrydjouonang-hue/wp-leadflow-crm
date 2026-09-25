<?php
/**
 * Generic add/edit form.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     admin: \LeadFlow\CRM\Admin\Crud\EntityAdmin,
 *     model: \LeadFlow\CRM\Data\Model|null,
 *     labels: array<string, string>,
 *     title: string,
 *     sections: array<int, array<string, mixed>>,
 *     sidebar: array<int, array<string, mixed>>,
 *     all_fields: array<int, array<string, mixed>>,
 *     values: array<string, mixed>,
 *     errors: array<string, string>,
 *     nonce: string,
 *     cancel_url: string
 * } $data
 */

use LeadFlow\CRM\Admin\Form\FormRenderer;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Security\Nonce;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_model  = $data['model'];
$leadflow_is_new = null === $leadflow_model;
?>
<div class="wrap lf-wrap lf-form-screen">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => $leadflow_is_new ? $data['labels']['add_new'] : sprintf(
				/* translators: %s: Record title. */
				__( 'Edit %s', 'wp-leadflow-crm' ),
				$data['title']
			),
			'subtitle' => $leadflow_is_new ? '' : $data['labels']['singular'],
			'actions'  => array(
				array(
					'label' => __( 'Back', 'wp-leadflow-crm' ),
					'url'   => $data['cancel_url'],
				),
			),
		)
	);

	FormRenderer::error_summary( $data['errors'], $data['all_fields'] );
	?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-entity-form" novalidate>
		<input type="hidden" name="action" value="<?php echo esc_attr( 'leadflow_crm_save_' . $data['admin']->entity() ); ?>" />
		<input type="hidden" name="id" value="<?php echo esc_attr( (string) ( $leadflow_is_new ? 0 : $leadflow_model->id() ) ); ?>" />
		<?php Nonce::field( $data['nonce'] ); ?>

		<div class="lf-layout">
			<div class="lf-layout__main">
				<div class="lf-card">
					<?php
					foreach ( $data['sections'] as $leadflow_section ) {
						FormRenderer::section(
							(string) $leadflow_section['title'],
							(array) $leadflow_section['fields'],
							$data['values'],
							$data['errors'],
							(string) ( $leadflow_section['description'] ?? '' )
						);
					}
					?>
				</div>
			</div>

			<aside class="lf-layout__side" aria-label="<?php esc_attr_e( 'Publish', 'wp-leadflow-crm' ); ?>">
				<div class="lf-card lf-sticky">
					<?php
					foreach ( $data['sidebar'] as $leadflow_field ) {
						FormRenderer::field( $leadflow_field, $data['values'][ $leadflow_field['name'] ] ?? '', $data['errors'][ $leadflow_field['name'] ] ?? null );
					}
					?>

					<?php if ( ! $leadflow_is_new ) : ?>
						<dl class="lf-meta">
							<dt><?php esc_html_e( 'Created', 'wp-leadflow-crm' ); ?></dt>
							<dd><?php echo esc_html( Format::datetime( $leadflow_model->get( 'created_at' ) ) ); ?></dd>
							<dt><?php esc_html_e( 'Last updated', 'wp-leadflow-crm' ); ?></dt>
							<dd><?php echo esc_html( Format::datetime( $leadflow_model->get( 'updated_at' ) ) ); ?></dd>
						</dl>
					<?php endif; ?>

					<div class="lf-form-actions">
						<button type="submit" class="button button-primary button-large">
							<?php echo esc_html( $leadflow_is_new ? $data['labels']['add_new'] : __( 'Save changes', 'wp-leadflow-crm' ) ); ?>
						</button>
						<a class="button button-large" href="<?php echo esc_url( $data['cancel_url'] ); ?>"><?php esc_html_e( 'Cancel', 'wp-leadflow-crm' ); ?></a>
					</div>
				</div>
				<?php $data['admin']->form_aside( $leadflow_model ); ?>
			</aside>
		</div>
	</form>
</div>
