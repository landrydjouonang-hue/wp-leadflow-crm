<?php
/**
 * Generic entity list screen.
 *
 * @package LeadFlow\CRM
 *
 * @var array{admin: \LeadFlow\CRM\Admin\Crud\EntityAdmin, table: \LeadFlow\CRM\Admin\Crud\EntityListTable, labels: array<string, string>, can_create: bool} $data
 */

use LeadFlow\CRM\Admin\View;
use LeadFlow\CRM\Security\Input;

defined( 'ABSPATH' ) || exit;

$leadflow_actions = array();

if ( $data['can_create'] ) {
	$leadflow_actions[] = array(
		'label'   => $data['labels']['add_new'],
		'url'     => $data['admin']->new_url(),
		'primary' => true,
	);
}

$leadflow_search = Input::text( 's' );
?>
<div class="wrap lf-wrap lf-list">
	<?php
	View::render(
		'admin/partials/header',
		array(
			'title'    => $data['labels']['plural'],
			'subtitle' => '' !== $leadflow_search
				/* translators: %s: Search terms. */
				? sprintf( __( 'Search results for “%s”', 'wp-leadflow-crm' ), $leadflow_search )
				: '',
			'actions'  => $leadflow_actions,
		)
	);

	$data['table']->views();
	?>

	<form method="get" class="lf-list-form">
		<input type="hidden" name="page" value="<?php echo esc_attr( Input::key( 'page' ) ); ?>" />
		<?php if ( 'all' !== $data['table']->current_status() ) : ?>
			<input type="hidden" name="status" value="<?php echo esc_attr( $data['table']->current_status() ); ?>" />
		<?php endif; ?>
		<?php
		$data['table']->search_box( $data['labels']['search'], 'lf-search' );
		$data['table']->display();
		?>
	</form>
</div>
