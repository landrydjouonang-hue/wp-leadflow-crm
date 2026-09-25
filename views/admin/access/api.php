<?php
/**
 * API access information.
 *
 * @package LeadFlow\CRM
 *
 * @var array{base: string, available: bool, https: bool, profile: string} $data
 */

defined( 'ABSPATH' ) || exit;

$leadflow_endpoints = array( 'leads', 'companies', 'contacts', 'tasks', 'activities' );
?>
<div class="lf-card">
	<h2 class="lf-card__title"><?php esc_html_e( 'REST API', 'wp-leadflow-crm' ); ?></h2>
	<p><?php esc_html_e( 'External applications can read and change CRM data through the REST API. Every request runs as a WordPress user and is limited to that user’s CRM permissions.', 'wp-leadflow-crm' ); ?></p>

	<dl class="lf-meta">
		<dt><?php esc_html_e( 'Base URL', 'wp-leadflow-crm' ); ?></dt>
		<dd><code><?php echo esc_html( $data['base'] ); ?></code></dd>
		<dt><?php esc_html_e( 'Authentication', 'wp-leadflow-crm' ); ?></dt>
		<dd>
			<?php esc_html_e( 'Application Passwords (HTTP Basic authentication: username and application password).', 'wp-leadflow-crm' ); ?>
			<?php if ( $data['available'] ) : ?>
				<a href="<?php echo esc_url( $data['profile'] ); ?>"><?php esc_html_e( 'Create an application password in your profile', 'wp-leadflow-crm' ); ?></a>
			<?php else : ?>
				<br /><span class="lf-field__error"><?php esc_html_e( 'Application Passwords are not available on this site (they need HTTPS or a local environment, and must not be disabled by another plugin).', 'wp-leadflow-crm' ); ?></span>
			<?php endif; ?>
		</dd>
	</dl>

	<?php if ( ! $data['https'] ) : ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'This site does not use HTTPS. Credentials sent to the API could be intercepted; use HTTPS in production.', 'wp-leadflow-crm' ); ?></p></div>
	<?php endif; ?>

	<h3 class="lf-subheading"><?php esc_html_e( 'Endpoints', 'wp-leadflow-crm' ); ?></h3>
	<div class="lf-table-scroll">
		<table class="widefat striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Resource', 'wp-leadflow-crm' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Routes', 'wp-leadflow-crm' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $leadflow_endpoints as $leadflow_endpoint ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( ucfirst( $leadflow_endpoint ) ); ?></th>
						<td>
							<code>GET|POST /<?php echo esc_html( $leadflow_endpoint ); ?></code>
							<code>GET|PUT|PATCH|DELETE /<?php echo esc_html( $leadflow_endpoint ); ?>/{id}</code>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<h3 class="lf-subheading"><?php esc_html_e( 'Example', 'wp-leadflow-crm' ); ?></h3>
	<pre class="lf-code"><code>curl -u "USERNAME:APPLICATION PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"title":"Website redesign","amount":12000}' \
  <?php echo esc_html( $data['base'] . '/leads' ); ?></code></pre>

	<p class="lf-muted"><?php esc_html_e( 'Every endpoint describes its fields: send an OPTIONS request to see the JSON schema. The full reference is in docs/API.md in the plugin folder.', 'wp-leadflow-crm' ); ?></p>
</div>
