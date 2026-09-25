<?php
/**
 * Contact profile.
 *
 * @package LeadFlow\CRM
 *
 * @var array{
 *     admin: \LeadFlow\CRM\Modules\Contacts\ContactsAdmin,
 *     contact: \LeadFlow\CRM\Data\Models\Contact,
 *     company: \LeadFlow\CRM\Data\Models\Company|null,
 *     company_admin: \LeadFlow\CRM\Modules\Companies\CompaniesAdmin,
 *     summary: array<string, int>,
 *     note_actions: \LeadFlow\CRM\Admin\Notes\NoteActions
 * } $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Support\Countries;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_contact   = $data['contact'];
$leadflow_admin     = $data['admin'];
$leadflow_company   = $data['company'];
$leadflow_name      = $leadflow_admin->title_of( $leadflow_contact );
$leadflow_status    = (string) $leadflow_contact->get( 'status', 'active' );
$leadflow_statuses  = $leadflow_admin->status_labels();
$leadflow_job       = (string) $leadflow_contact->get( 'job_title', '' );
$leadflow_can_see_c = current_user_can( 'leadflow_view_companies' );

if ( '' !== $leadflow_job && null !== $leadflow_company ) {
	/* translators: 1: Job title, 2: Company name. */
	$leadflow_subtitle = sprintf( __( '%1$s at %2$s', 'wp-leadflow-crm' ), $leadflow_job, $leadflow_company->name() );
} else {
	$leadflow_subtitle = '' !== $leadflow_job ? $leadflow_job : ( null !== $leadflow_company ? $leadflow_company->name() : '' );
}
?>
<div class="wrap lf-wrap lf-record">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'        => $leadflow_name,
			'subtitle'     => $leadflow_subtitle,
			'before_title' => UI::avatar( $leadflow_name, 'lg' ),
			'badge'        => UI::badge( $leadflow_status, $leadflow_statuses[ $leadflow_status ] ?? $leadflow_status ),
			'actions'      => $leadflow_admin->view_actions( $leadflow_contact ),
		)
	);
	?>

	<?php if ( $leadflow_contact->is_trashed() ) : ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'This contact is in the trash. Restore it to make changes.', 'wp-leadflow-crm' ); ?></p></div>
	<?php endif; ?>

	<p class="lf-breadcrumb"><a href="<?php echo esc_url( $leadflow_admin->url() ); ?>">&larr; <?php esc_html_e( 'All contacts', 'wp-leadflow-crm' ); ?></a></p>

	<div class="lf-layout">
		<div class="lf-layout__main">
			<section class="lf-card" aria-labelledby="lf-details-title">
				<header class="lf-card__header">
					<h2 class="lf-card__title" id="lf-details-title"><?php esc_html_e( 'Details', 'wp-leadflow-crm' ); ?></h2>
				</header>
				<dl class="lf-details">
					<div><dt><?php esc_html_e( 'First name', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::text( (string) $leadflow_contact->get( 'first_name', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Last name', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::text( (string) $leadflow_contact->get( 'last_name', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Job title', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::text( $leadflow_job ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div>
						<dt><?php esc_html_e( 'Company', 'wp-leadflow-crm' ); ?></dt>
						<dd>
							<?php if ( null === $leadflow_company ) : ?>
								<?php echo UI::text( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php elseif ( $leadflow_can_see_c ) : ?>
								<a href="<?php echo esc_url( $data['company_admin']->view_url( $leadflow_company->id() ) ); ?>"><?php echo esc_html( $leadflow_company->name() ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $leadflow_company->name() ); ?>
							<?php endif; ?>
						</dd>
					</div>
					<div><dt><?php esc_html_e( 'Email', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::email( (string) $leadflow_contact->get( 'email', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Phone', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::phone( (string) $leadflow_contact->get( 'phone', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Mobile', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::phone( (string) $leadflow_contact->get( 'mobile', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Country', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::text( Countries::name( (string) $leadflow_contact->get( 'country', '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
					<div><dt><?php esc_html_e( 'Source', 'wp-leadflow-crm' ); ?></dt><dd><?php echo UI::text( (string) $leadflow_contact->get( 'source', '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd></div>
				</dl>
			</section>

			<?php \LeadFlow\CRM\Admin\RecordPanels::render_for( 'contact', $leadflow_contact ); ?>
		</div>

		<aside class="lf-layout__side" aria-label="<?php esc_attr_e( 'Contact summary', 'wp-leadflow-crm' ); ?>">
			<div class="lf-card">
				<dl class="lf-meta">
					<dt><?php esc_html_e( 'Status', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::badge( $leadflow_status, $leadflow_statuses[ $leadflow_status ] ?? $leadflow_status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php esc_html_e( 'Owner', 'wp-leadflow-crm' ); ?></dt>
					<dd><?php echo UI::text( Format::user( $leadflow_contact->get( 'owner_id' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					<dt><?php esc_html_e( 'Created', 'wp-leadflow-crm' ); ?></dt>
					<dd>
						<?php echo esc_html( Format::datetime( $leadflow_contact->get( 'created_at' ) ) ); ?>
						<?php $leadflow_creator = Format::user( $leadflow_contact->get( 'created_by' ) ); ?>
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
					<dd><?php echo esc_html( Format::relative( $leadflow_contact->get( 'updated_at' ) ) ); ?></dd>
				</dl>
			</div>

			<?php if ( null !== $leadflow_company ) : ?>
				<section class="lf-card" aria-labelledby="lf-company-title">
					<h2 class="lf-card__title" id="lf-company-title"><?php esc_html_e( 'Company', 'wp-leadflow-crm' ); ?></h2>
					<div class="lf-company-card">
						<?php echo UI::avatar( $leadflow_company->name(), 'md' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<div>
							<strong>
								<?php if ( $leadflow_can_see_c ) : ?>
									<a href="<?php echo esc_url( $data['company_admin']->view_url( $leadflow_company->id() ) ); ?>"><?php echo esc_html( $leadflow_company->name() ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $leadflow_company->name() ); ?>
								<?php endif; ?>
							</strong>
							<?php if ( '' !== (string) $leadflow_company->get( 'industry', '' ) ) : ?>
								<br /><span class="lf-muted"><?php echo esc_html( (string) $leadflow_company->get( 'industry' ) ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<dl class="lf-meta">
						<?php if ( '' !== (string) $leadflow_company->get( 'phone', '' ) ) : ?>
							<dt><?php esc_html_e( 'Phone', 'wp-leadflow-crm' ); ?></dt>
							<dd><?php echo UI::phone( (string) $leadflow_company->get( 'phone' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
						<?php endif; ?>
						<?php if ( '' !== (string) $leadflow_company->get( 'website', '' ) ) : ?>
							<dt><?php esc_html_e( 'Website', 'wp-leadflow-crm' ); ?></dt>
							<dd><?php echo UI::website( (string) $leadflow_company->get( 'website' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
						<?php endif; ?>
					</dl>
				</section>
			<?php endif; ?>
		</aside>
	</div>
</div>
