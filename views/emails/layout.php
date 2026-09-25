<?php
/**
 * HTML layout of CRM emails.
 *
 * Inline styles only (email clients ignore <style> blocks inconsistently).
 * Override the whole document with the `leadflow_crm_email_html` filter.
 *
 * @package LeadFlow\CRM
 *
 * @var array{subject: string, content: string, business: string, site_url: string} $data
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( str_replace( '_', '-', get_locale() ) ); ?>" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo esc_html( $data['subject'] ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f5f7;">
	<tr>
		<td align="center" style="padding:24px 12px;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:640px;background:#ffffff;border-radius:6px;">
				<tr>
					<td style="padding:28px 32px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#1f2937;">
						<?php echo wp_kses_post( $data['content'] ); ?>
					</td>
				</tr>
			</table>
			<?php if ( '' !== $data['business'] ) : ?>
				<p style="margin:16px 0 0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;color:#6b7280;">
					<a href="<?php echo esc_url( $data['site_url'] ); ?>" style="color:#6b7280;text-decoration:none;"><?php echo esc_html( $data['business'] ); ?></a>
				</p>
			<?php endif; ?>
		</td>
	</tr>
</table>
</body>
</html>
