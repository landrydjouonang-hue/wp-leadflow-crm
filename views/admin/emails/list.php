<?php
/**
 * Email log.
 *
 * @package LeadFlow\CRM
 *
 * @var array{table: \LeadFlow\CRM\Modules\Emails\EmailsListTable, others: bool} $data
 */

use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Modules\Emails\EmailsPage;
use LeadFlow\CRM\Security\Input;

defined( 'ABSPATH' ) || exit;

$leadflow_search = Input::text( 's' );
?>
<div class="wrap lf-wrap lf-list">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => __( 'Emails', 'wp-leadflow-crm' ),
			'subtitle' => '' !== $leadflow_search
				/* translators: %s: Search terms. */
				? sprintf( __( 'Search results for “%s”', 'wp-leadflow-crm' ), $leadflow_search )
				: ( $data['others'] ? __( 'Every email sent from the CRM.', 'wp-leadflow-crm' ) : __( 'Emails you sent from the CRM.', 'wp-leadflow-crm' ) ),
		)
	);

	$data['table']->views();
	?>

	<form method="get" class="lf-list-form">
		<input type="hidden" name="page" value="<?php echo esc_attr( EmailsPage::PAGE_SLUG ); ?>" />
		<?php if ( 'all' !== $data['table']->current_status() ) : ?>
			<input type="hidden" name="status" value="<?php echo esc_attr( $data['table']->current_status() ); ?>" />
		<?php endif; ?>
		<?php
		$data['table']->search_box( __( 'Search emails', 'wp-leadflow-crm' ), 'lf-search' );
		$data['table']->display();
		?>
	</form>
</div>
