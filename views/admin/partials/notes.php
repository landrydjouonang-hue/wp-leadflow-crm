<?php
/**
 * Notes panel.
 *
 * @package LeadFlow\CRM
 *
 * @var array{parent: string, parent_id: int, notes: \LeadFlow\CRM\Data\Models\Note[], origins: array<int, string>, can_add: bool, actions: \LeadFlow\CRM\Admin\Notes\NoteActions} $data
 */

use LeadFlow\CRM\Admin\UI;
use LeadFlow\CRM\Security\Nonce;
use LeadFlow\CRM\Support\Format;

defined( 'ABSPATH' ) || exit;

$leadflow_post_url = admin_url( 'admin-post.php' );
$leadflow_count    = count( $data['notes'] );
?>
<div class="lf-notes">
	<?php if ( $data['can_add'] ) : ?>
		<form class="lf-note-form" method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
			<input type="hidden" name="action" value="leadflow_crm_add_note" />
			<input type="hidden" name="parent" value="<?php echo esc_attr( $data['parent'] ); ?>" />
			<input type="hidden" name="parent_id" value="<?php echo esc_attr( (string) $data['parent_id'] ); ?>" />
			<?php Nonce::field( 'add_note_' . $data['parent'] . '_' . $data['parent_id'] ); ?>
			<label class="screen-reader-text" for="lf-note-content"><?php esc_html_e( 'New note', 'wp-leadflow-crm' ); ?></label>
			<textarea id="lf-note-content" name="content" rows="3" class="large-text" required placeholder="<?php esc_attr_e( 'Write a note…', 'wp-leadflow-crm' ); ?>"></textarea>
			<div class="lf-note-form__actions">
				<label><input type="checkbox" name="is_pinned" value="1" /> <?php esc_html_e( 'Pin to top', 'wp-leadflow-crm' ); ?></label>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Add note', 'wp-leadflow-crm' ); ?></button>
			</div>
		</form>
	<?php endif; ?>

	<?php if ( 0 === $leadflow_count ) : ?>
		<p class="lf-empty-text"><?php esc_html_e( 'No notes yet.', 'wp-leadflow-crm' ); ?></p>
	<?php else : ?>
		<ol class="lf-note-list">
			<?php foreach ( $data['notes'] as $leadflow_note ) : ?>
				<?php
				$leadflow_author = Format::user( $leadflow_note->get( 'created_by' ) );
				$leadflow_author = '' !== $leadflow_author ? $leadflow_author : __( 'System', 'wp-leadflow-crm' );
				$leadflow_origin = $data['origins'][ $leadflow_note->id() ] ?? '';
				?>
				<li class="lf-note<?php echo $leadflow_note->is_pinned() ? ' lf-note--pinned' : ''; ?>">
					<?php echo UI::avatar( $leadflow_author, 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in UI. ?>
					<div class="lf-note__body">
						<p class="lf-note__meta">
							<strong><?php echo esc_html( $leadflow_author ); ?></strong>
							<time datetime="<?php echo esc_attr( Format::iso( $leadflow_note->get( 'created_at' ) ) ); ?>" title="<?php echo esc_attr( Format::datetime( $leadflow_note->get( 'created_at' ) ) ); ?>">
								<?php echo esc_html( Format::relative( $leadflow_note->get( 'created_at' ) ) ); ?>
							</time>
							<?php if ( '' !== $leadflow_origin ) : ?>
								<span class="lf-muted">
									<?php
									/* translators: %s: Contact name. */
									echo esc_html( sprintf( __( 'on %s', 'wp-leadflow-crm' ), $leadflow_origin ) );
									?>
								</span>
							<?php endif; ?>
							<?php if ( $leadflow_note->is_pinned() ) : ?>
								<span class="lf-pill lf-pill--pinned"><?php esc_html_e( 'Pinned', 'wp-leadflow-crm' ); ?></span>
							<?php endif; ?>
						</p>
						<div class="lf-note__content"><?php echo wp_kses_post( wpautop( $leadflow_note->content() ) ); ?></div>

						<?php if ( $data['actions']->can_manage( $leadflow_note ) ) : ?>
							<div class="lf-note__actions">
								<form method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
									<input type="hidden" name="action" value="leadflow_crm_pin_note" />
									<input type="hidden" name="note_id" value="<?php echo esc_attr( (string) $leadflow_note->id() ); ?>" />
									<?php Nonce::field( 'pin_note_' . $leadflow_note->id() ); ?>
									<button type="submit" class="button-link"><?php echo esc_html( $leadflow_note->is_pinned() ? __( 'Unpin', 'wp-leadflow-crm' ) : __( 'Pin', 'wp-leadflow-crm' ) ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( $leadflow_post_url ); ?>">
									<input type="hidden" name="action" value="leadflow_crm_delete_note" />
									<input type="hidden" name="note_id" value="<?php echo esc_attr( (string) $leadflow_note->id() ); ?>" />
									<?php Nonce::field( 'delete_note_' . $leadflow_note->id() ); ?>
									<button type="submit" class="button-link lf-link-danger" data-lf-confirm="<?php esc_attr_e( 'Delete this note?', 'wp-leadflow-crm' ); ?>"><?php esc_html_e( 'Delete', 'wp-leadflow-crm' ); ?></button>
								</form>
							</div>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>
