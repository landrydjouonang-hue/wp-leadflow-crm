<?php
/**
 * Settings → Email tab.
 *
 * @package LeadFlow\CRM
 *
 * @var array{registry: \LeadFlow\CRM\Settings\Registry, group: string, from_name: string, from_email: string, test_to: string} $data
 */

use LeadFlow\CRM\Security\Nonce;
use LeadFlow\CRM\Settings\Registry;

defined( 'ABSPATH' ) || exit;
?>
<p class="lf-muted">
	<?php esc_html_e( 'CRM emails are sent through your site’s email system (wp_mail). For reliable delivery, connect your site to an SMTP or transactional email service with a dedicated plugin — LeadFlow CRM uses it automatically.', 'wp-leadflow-crm' ); ?>
</p>

<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" novalidate="novalidate">
	<?php
	settings_fields( $data['group'] );
	$data['registry']->tab_marker( 'email' );
	do_settings_sections( Registry::page_id( 'email' ) );
	submit_button();
	?>
</form>

<hr />

<h2><?php esc_html_e( 'Send a test email', 'wp-leadflow-crm' ); ?></h2>
<p>
	<?php
	echo esc_html(
		sprintf(
			/* translators: 1: Sender name, 2: Sender address. */
			__( 'Emails are currently sent as “%1$s” <%2$s>. Save your changes before sending a test.', 'wp-leadflow-crm' ),
			$data['from_name'],
			$data['from_email']
		)
	);
	?>
</p>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-inline-form">
	<input type="hidden" name="action" value="leadflow_crm_send_test_email" />
	<?php Nonce::field( 'send_test_email' ); ?>
	<label for="lf-test-email"><?php esc_html_e( 'Send to', 'wp-leadflow-crm' ); ?></label>
	<input type="email" id="lf-test-email" name="to" class="regular-text" required value="<?php echo esc_attr( $data['test_to'] ); ?>" />
	<button type="submit" class="button"><?php esc_html_e( 'Send test email', 'wp-leadflow-crm' ); ?></button>
</form>
