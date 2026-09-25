<?php
/**
 * Pipeline stage editor (Settings → Pipeline).
 *
 * @package LeadFlow\CRM
 *
 * @var array{stages: \LeadFlow\CRM\Data\Models\Stage[], counts: array<string, int>, types: array<string, string>} $data
 */

use LeadFlow\CRM\Security\Nonce;

defined( 'ABSPATH' ) || exit;

$leadflow_last = count( $data['stages'] ) - 1;
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lf-stages">
	<input type="hidden" name="action" value="leadflow_crm_save_stages" />
	<?php Nonce::field( 'save_stages' ); ?>

	<p class="lf-stages__intro">
		<?php esc_html_e( 'Stages are the columns of your sales pipeline. The stage type decides the lead status: leads in a “Won” or “Lost” stage are closed. You need at least one stage of each type.', 'wp-leadflow-crm' ); ?>
	</p>

	<table class="widefat lf-stages__table">
		<caption class="screen-reader-text"><?php esc_html_e( 'Pipeline stages', 'wp-leadflow-crm' ); ?></caption>
		<thead>
			<tr>
				<th scope="col" class="lf-stages__order"><?php esc_html_e( 'Order', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Name', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Color', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Probability (%)', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Type', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Leads', 'wp-leadflow-crm' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Delete', 'wp-leadflow-crm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $data['stages'] as $leadflow_index => $leadflow_stage ) : ?>
				<?php
				$leadflow_id    = $leadflow_stage->id();
				$leadflow_field = 'stages[' . $leadflow_id . ']';
				$leadflow_count = (int) ( $data['counts'][ $leadflow_stage->slug() ] ?? 0 );
				?>
				<tr>
					<td class="lf-stages__order">
						<input type="hidden" name="order[]" value="<?php echo esc_attr( (string) $leadflow_id ); ?>" />
						<button type="submit" name="move" value="<?php echo esc_attr( $leadflow_id . ':up' ); ?>" class="button button-small" <?php disabled( 0 === $leadflow_index ); ?>>
							<span aria-hidden="true">↑</span><span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: Stage name. */ __( 'Move %s up', 'wp-leadflow-crm' ), $leadflow_stage->name() ) ); ?></span>
						</button>
						<button type="submit" name="move" value="<?php echo esc_attr( $leadflow_id . ':down' ); ?>" class="button button-small" <?php disabled( $leadflow_last === $leadflow_index ); ?>>
							<span aria-hidden="true">↓</span><span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: Stage name. */ __( 'Move %s down', 'wp-leadflow-crm' ), $leadflow_stage->name() ) ); ?></span>
						</button>
					</td>
					<td>
						<label class="screen-reader-text" for="lf-stage-name-<?php echo esc_attr( (string) $leadflow_id ); ?>"><?php esc_html_e( 'Stage name', 'wp-leadflow-crm' ); ?></label>
						<input type="text" id="lf-stage-name-<?php echo esc_attr( (string) $leadflow_id ); ?>" name="<?php echo esc_attr( $leadflow_field ); ?>[name]" value="<?php echo esc_attr( $leadflow_stage->name() ); ?>" maxlength="100" required />
						<code class="lf-stages__slug"><?php echo esc_html( $leadflow_stage->slug() ); ?></code>
					</td>
					<td>
						<label class="screen-reader-text" for="lf-stage-color-<?php echo esc_attr( (string) $leadflow_id ); ?>"><?php echo esc_html( sprintf( /* translators: %s: Stage name. */ __( 'Color of %s', 'wp-leadflow-crm' ), $leadflow_stage->name() ) ); ?></label>
						<input type="color" id="lf-stage-color-<?php echo esc_attr( (string) $leadflow_id ); ?>" name="<?php echo esc_attr( $leadflow_field ); ?>[color]" value="<?php echo esc_attr( $leadflow_stage->color() ); ?>" />
					</td>
					<td>
						<label class="screen-reader-text" for="lf-stage-prob-<?php echo esc_attr( (string) $leadflow_id ); ?>"><?php echo esc_html( sprintf( /* translators: %s: Stage name. */ __( 'Probability of %s', 'wp-leadflow-crm' ), $leadflow_stage->name() ) ); ?></label>
						<input type="number" id="lf-stage-prob-<?php echo esc_attr( (string) $leadflow_id ); ?>" class="small-text" name="<?php echo esc_attr( $leadflow_field ); ?>[probability]" value="<?php echo esc_attr( null === $leadflow_stage->probability() ? '' : (string) $leadflow_stage->probability() ); ?>" min="0" max="100" step="1" />
					</td>
					<td>
						<label class="screen-reader-text" for="lf-stage-type-<?php echo esc_attr( (string) $leadflow_id ); ?>"><?php echo esc_html( sprintf( /* translators: %s: Stage name. */ __( 'Type of %s', 'wp-leadflow-crm' ), $leadflow_stage->name() ) ); ?></label>
						<select id="lf-stage-type-<?php echo esc_attr( (string) $leadflow_id ); ?>" name="<?php echo esc_attr( $leadflow_field ); ?>[type]">
							<?php foreach ( $data['types'] as $leadflow_type => $leadflow_label ) : ?>
								<option value="<?php echo esc_attr( $leadflow_type ); ?>" <?php selected( $leadflow_stage->type(), $leadflow_type ); ?>><?php echo esc_html( $leadflow_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td><?php echo esc_html( number_format_i18n( $leadflow_count ) ); ?></td>
					<td class="lf-stages__delete">
						<label>
							<input type="checkbox" name="delete[<?php echo esc_attr( (string) $leadflow_id ); ?>]" value="1" />
							<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: Stage name. */ __( 'Delete %s', 'wp-leadflow-crm' ), $leadflow_stage->name() ) ); ?></span>
						</label>
						<?php if ( $leadflow_count > 0 ) : ?>
							<label class="screen-reader-text" for="lf-stage-reassign-<?php echo esc_attr( (string) $leadflow_id ); ?>"><?php esc_html_e( 'Move its leads to', 'wp-leadflow-crm' ); ?></label>
							<select id="lf-stage-reassign-<?php echo esc_attr( (string) $leadflow_id ); ?>" name="reassign[<?php echo esc_attr( (string) $leadflow_id ); ?>]">
								<option value=""><?php esc_html_e( 'Move leads to…', 'wp-leadflow-crm' ); ?></option>
								<?php foreach ( $data['stages'] as $leadflow_other ) : ?>
									<?php if ( $leadflow_other->id() !== $leadflow_id ) : ?>
										<option value="<?php echo esc_attr( $leadflow_other->slug() ); ?>"><?php echo esc_html( $leadflow_other->name() ); ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr class="lf-stages__new">
				<td><span class="screen-reader-text"><?php esc_html_e( 'New stage', 'wp-leadflow-crm' ); ?></span><span aria-hidden="true">+</span></td>
				<td>
					<label class="screen-reader-text" for="lf-stage-new-name"><?php esc_html_e( 'New stage name', 'wp-leadflow-crm' ); ?></label>
					<input type="text" id="lf-stage-new-name" name="new[0][name]" maxlength="100" placeholder="<?php esc_attr_e( 'Add a stage…', 'wp-leadflow-crm' ); ?>" />
				</td>
				<td>
					<label class="screen-reader-text" for="lf-stage-new-color"><?php esc_html_e( 'New stage color', 'wp-leadflow-crm' ); ?></label>
					<input type="color" id="lf-stage-new-color" name="new[0][color]" value="#64748b" />
				</td>
				<td>
					<label class="screen-reader-text" for="lf-stage-new-prob"><?php esc_html_e( 'New stage probability', 'wp-leadflow-crm' ); ?></label>
					<input type="number" id="lf-stage-new-prob" class="small-text" name="new[0][probability]" min="0" max="100" step="1" />
				</td>
				<td>
					<label class="screen-reader-text" for="lf-stage-new-type"><?php esc_html_e( 'New stage type', 'wp-leadflow-crm' ); ?></label>
					<select id="lf-stage-new-type" name="new[0][type]">
						<?php foreach ( $data['types'] as $leadflow_type => $leadflow_label ) : ?>
							<option value="<?php echo esc_attr( $leadflow_type ); ?>"><?php echo esc_html( $leadflow_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td colspan="2"></td>
			</tr>
		</tfoot>
	</table>

	<p class="description"><?php esc_html_e( 'Renaming a stage keeps its leads. Deleting a stage that has leads requires choosing where to move them.', 'wp-leadflow-crm' ); ?></p>
	<?php submit_button( __( 'Save pipeline', 'wp-leadflow-crm' ) ); ?>
</form>
