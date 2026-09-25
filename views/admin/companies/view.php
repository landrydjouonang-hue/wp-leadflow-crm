<?php
/**
 * Company profile.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     admin: \LeadFlow\CRM\Modules\Companies\CompaniesAdmin,
 *     company: \LeadFlow\CRM\Data\Models\Company,
 *     contacts: \LeadFlow\CRM\Data\Models\Contact[],
 *     summary: array<string, int>,
 *     contacts_url: string,
 *     contact_admin: \LeadFlow\CRM\Modules\Contacts\ContactsAdmin,
 *     can_add: bool,
 *     note_actions: \LeadFlow\CRM\Admin\Notes\NoteActions
 * } $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Support\Countries;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_company  = $data['company'];
$leadflow_admin    = $data['admin'];
$leadflow_status   = $leadflow_company->status();
$leadflow_statuses = $leadflow_admin->status_labels();
$leadflow_subtitle = array_filter(
	array(
		(string) $leadflow_company->get( 'industry', '' ),
		Countries::name( (string) $leadflow_company->get( 'country', '' ) ),
	)
);
$leadflow_address  = array_filter(
	array(
		(string) $leadflow_company->get( 'address_line_1', '' ),
		(string) $leadflow_company->get( 'address_line_2', '' ),
		trim( $leadflow_company->get( 'postal_code', '' ) . ' ' . $leadflow_company->get( 'city', '' ) ),
		(string) $leadflow_company->get( 'state', '' ),
		Countries::name( (string) $leadflow_company->get( 'country', '' ) ),
	)
);
$leadflow_actions  = $leadflow_admin->view_actions( $leadflow_company );

if ( $data['can_add'] ) {
	array_splice(
		$leadflow_actions,
		1,
		0,
		array(
			array(
				'label' => __( 'Add contact', 'wp-leadflow-crm' ),
				'url'   => $data['contact_admin']->new_url( array( 'company_id' => $leadflow_company->id() ) ),
			),
		)
	);
}
?>
<div class="wrap lf-wrap lf-record">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'        => $leadflow_company->name(),
			'subtitle'     => implode( ' · ', $leadflow_subtitle ),
			'before_title' => UI::avatar( $leadflow_company->name(), 'lg' ),
			'badge'        => UI::badge( $leadflow_status, $leadflow_statuses[ $leadflow_status ] ?? $leadflow_status ),
			'actions'      => $leadflow_actions,
		)
	);
	?>

	<?php if ( $leadflow_company->is_trashed() ) : ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'This company is in the trash. Restore it to make changes.', 'wp-leadflow-crm' ); ?></p></div>
	<?php endif; ?>

	<p class="lf-breadcrumb"><a href="<?php echo esc_url( $leadflow_admin->url() ); ?>">&larr; <?php esc_html_e( 'All companies', 'wp-leadflow-crm' ); ?></a></p>

	<div class="lf-layout">
		<div class="lf-layout__main">
			<section class="lf-card" aria-labelledby="lf-details-title">
				<header class="lf-card__header">
					<h2 class="lf-card__title" id="lf-details-title"><?php esc_html_e( 'Details', 'wp-leadflow-crm' ); ?></h2>
				</header>
				<dl class="lf-details">
					<div><dt><?php esc_html_e( 'Industry', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::text( (string) $leadflow_company->get( 'industry', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Website', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::website( (string) $leadflow_company->get( 'website', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Email', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::email( (string) $leadflow_company->get( 'email', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Phone', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::phone( (string) $leadflow_company->get( 'phone', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div class="lf-details__wide">
						<dt><?php esc_html_e( 'Address', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php if ( empty( $leadflow_address ) ) : ?>
								<?php echo UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<address><?php echo implode( '<br />', array_map( 'esc_html', $leadflow_address ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each part escaped. ?></address>
							<?php endif; ?>
						</dd>
					</div>
					<?php if ( '' !== (string) $leadflow_company->get( 'description', '' ) ) : ?>
						<div class="lf-details__wide">
							<dt><?php esc_html_e( 'Description', 'wp-leadflow-crm' ); ?></dt>
							<dd><?php echo wp_kses_post( wpautop( esc_html( (string) $leadflow_company->get( 'description' ) ) ) ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
			</section>

			<section class="lf-card" aria-labelledby="lf-contacts-title">
				<header class="lf-card__header">
					<h2 class="lf-card__title" id="lf-contacts-title">
						<?php esc_html_e( 'Contacts', 'wp-leadflow-crm' ); ?>
						<span class="lf-count"><?php echo esc_html( number_format_i18n( (int) $data['summary']['contacts'] ) ); ?></span>
					</h2>
					<?php if ( $data['can_add'] ) : ?>
						<a class="button button-small" href="<?php echo esc_url( $data['contact_admin']->new_url( array( 'company_id' => $leadflow_company->id() ) ) ); ?>"><?php esc_html_e( 'Add contact', 'wp-leadflow-crm' ); ?></a>
					<?php endif; ?>
				</header>

				<?php if ( empty( $data['contacts'] ) ) : ?>
					<p class="lf-empty-text"><?php esc_html_e( 'No contacts at this company yet.', 'wp-leadflow-crm' ); ?></p>
				<?php else : ?>
					<table class="widefat striped lf-mini-table">
						<caption class="screen-reader-text"><?php esc_html_e( 'Contacts at this company', 'wp-leadflow-crm' ); ?></caption>
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Name', 'wp-leadflow-crm' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Job title', 'wp-leadflow-crm' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Email', 'wp-leadflow-crm' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Phone', 'wp-leadflow-crm' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $data['contacts'] as $leadflow_contact ) : ?>
								<tr>
									<th scope="row">
										<a href="<?php echo esc_url( $data['contact_admin']->view_url( $leadflow_contact->id() ) ); ?>"><?php echo esc_html( $data['contact_admin']->title_of( $leadflow_contact ) ); ?></a>
									</th>
									<td><?php echo UI::text( (string) $leadflow_contact->get( 'job_title', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td><?php echo UI::email( (string) $leadflow_contact->get( 'email', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td><?php echo UI::phone( (string) $leadflow_contact->get( 'phone', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php if ( (int) $data['summary']['contacts'] > count( $data['contacts'] ) ) : ?>
						<p><a href="<?php echo esc_url( $data['contacts_url'] ); ?>"><?php esc_html_e( 'View all contacts', 'wp-leadflow-crm' ); ?></a></p>
					<?php endif; ?>
				<?php endif; ?>
			</section>

			<?php \LeadFlow\CRM\Admin\RecordPanels::render_for( 'company', $leadflow_company ); ?>
		</div>

		<aside class="lf-layout__side" aria-label="<?php esc_attr_e( 'Company summary', 'wp-leadflow-crm' ); ?>">
			<div class="lf-card">
				<dl class="lf-meta">
					<dt><?php esc_html_e( 'Status', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::badge( $leadflow_status, $leadflow_statuses[ $leadflow_status ] ?? $leadflow_status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php esc_html_e( 'Owner', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::text( Format::user( $leadflow_company->get( 'owner_id' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php esc_html_e( 'Created', 'wp-leadflow-crm' ); ?></dt>
					<dd>
						<?php echo esc_html( Format::datetime( $leadflow_company->get( 'created_at' ) ) ); ?>
						<?php $leadflow_creator = Format::user( $leadflow_company->get( 'created_by' ) ); ?>
						<?php if ( '' !== $leadflow_creator ) : ?>
							<br /><span class="lf-muted">
								<?php
								/* translators: %s: User name. */
								echo esc_html( sprintf( __( 'by %s', 'wp-leadflow-crm' ), $leadflow_creator ) );
								?>
							</span>
						<?php endif; ?>
					</dd>
					<dt><?php esc_html_e( 'Last updated', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo esc_html( Format::relative( $leadflow_company->get( 'updated_at' ) ) ); ?></dd>
				</dl>
			</div>

			<div class="lf-card">
				<ul class="lf-stats">
					<li><span class="lf-stats__number"><?php echo esc_html( number_format_i18n( (int) $data['summary']['contacts'] ) ); ?></span> <?php esc_html_e( 'Contacts', 'wp-leadflow-crm' ); ?></li>
					<li><span class="lf-stats__number"><?php echo esc_html( number_format_i18n( (int) $data['summary']['notes'] ) ); ?></span> <?php esc_html_e( 'Notes', 'wp-leadflow-crm' ); ?></li>
				</ul>
			</div>
		</aside>
	</div>
</div>
