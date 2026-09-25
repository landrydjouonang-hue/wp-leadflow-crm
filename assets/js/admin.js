/**
 * WP LeadFlow CRM — admin scripts.
 *
 * Dependencies: wp-i18n, wp-api-fetch (adds the REST nonce), wp-a11y, wp-url.
 */
( () => {
	'use strict';

	const { __ } = wp.i18n;
	const config = window.leadflowCRM || {};

	/**
	 * Builds a plain-text report from the REST endpoint.
	 *
	 * @return {Promise<string>} Report text.
	 */
	const fetchStatusReport = async () => {
		const response = await wp.apiFetch( {
			path: `/${ config.restNamespace }/system-status`,
		} );

		const lines = [
			'### WP LeadFlow CRM — System status ###',
			`Generated: ${ response.generated_at }`,
			'',
			...response.rows.map(
				( row ) => `${ row.label }: ${ row.value } [${ row.status }]`
			),
		];

		return lines.join( '\n' );
	};

	/**
	 * Copies text, falling back to a hidden textarea for non-secure contexts.
	 *
	 * @param {string} text Text.
	 * @return {Promise<void>}
	 */
	const copyText = async ( text ) => {
		if ( navigator.clipboard && window.isSecureContext ) {
			await navigator.clipboard.writeText( text );
			return;
		}

		const area = document.createElement( 'textarea' );
		area.value = text;
		area.setAttribute( 'readonly', '' );
		area.style.position = 'fixed';
		area.style.opacity = '0';
		document.body.appendChild( area );
		area.select();

		const copied = document.execCommand( 'copy' );
		area.remove();

		if ( ! copied ) {
			throw new Error( 'copy failed' );
		}
	};

	const initStatusCopy = () => {
		const button = document.querySelector( '[data-lf-copy-status]' );

		if ( ! button ) {
			return;
		}

		const label = button.querySelector( '[data-lf-label]' );
		const originalLabel = label ? label.textContent : '';

		button.addEventListener( 'click', async () => {
			button.disabled = true;
			button.setAttribute( 'aria-busy', 'true' );

			try {
				await copyText( await fetchStatusReport() );

				if ( label ) {
					label.textContent = __( 'Copied!', 'wp-leadflow-crm' );
					window.setTimeout( () => {
						label.textContent = originalLabel;
					}, 2000 );
				}

				wp.a11y.speak(
					__( 'System status report copied to the clipboard.', 'wp-leadflow-crm' ),
					'polite'
				);
			} catch ( error ) {
				wp.a11y.speak(
					__( 'The report could not be copied. Please select the table and copy it manually.', 'wp-leadflow-crm' ),
					'assertive'
				);
			} finally {
				button.disabled = false;
				button.removeAttribute( 'aria-busy' );
			}
		} );
	};

	/**
	 * Asks for confirmation before destructive links/buttons
	 * (elements with data-lf-confirm).
	 */
	const initConfirm = () => {
		document.addEventListener( 'click', ( event ) => {
			const target = event.target.closest( '[data-lf-confirm]' );

			if ( target && ! window.confirm( target.dataset.lfConfirm ) ) {
				event.preventDefault();
			}
		} );
	};

	/**
	 * Moves focus to the error summary so screen readers announce it.
	 */
	const initErrorSummary = () => {
		const summary = document.querySelector( '[data-lf-error-summary]' );

		if ( summary ) {
			summary.focus();
		}
	};

	/**
	 * Live search for record pickers (e.g. company): refines the options
	 * of the linked <select> through the REST API. The selected option is
	 * always kept.
	 */
	const initRecordSearch = () => {
		document.querySelectorAll( '[data-lf-record-search]' ).forEach( ( input ) => {
			const select = document.getElementById( input.dataset.lfRecordSearch );

			if ( ! select ) {
				return;
			}

			const emptyOption = select.querySelector( 'option[value=""]' );
			let timer = null;
			let controller = null;

			const render = ( items ) => {
				const selected = select.value;
				const keep = selected ? select.querySelector( `option[value="${ CSS.escape( selected ) }"]` ) : null;

				select.textContent = '';

				if ( emptyOption ) {
					select.appendChild( emptyOption );
				}

				if ( keep && ! items.some( ( item ) => String( item.id ) === selected ) ) {
					select.appendChild( keep );
				}

				items.forEach( ( item ) => {
					const option = document.createElement( 'option' );
					option.value = String( item.id );
					option.textContent = item.name;
					select.appendChild( option );
				} );

				select.value = selected;
			};

			input.addEventListener( 'input', () => {
				window.clearTimeout( timer );

				timer = window.setTimeout( async () => {
					if ( controller ) {
						controller.abort();
					}

					controller = new AbortController();

					try {
						const items = await wp.apiFetch( {
							path: wp.url.addQueryArgs( `/${ config.restNamespace }/${ input.dataset.endpoint }`, {
								search: input.value.trim(),
								per_page: 50,
							} ),
							signal: controller.signal,
						} );

						render( items );
						wp.a11y.speak(
							items.length
								? wp.i18n.sprintf(
									/* translators: %d: Number of results. */
									wp.i18n._n( '%d result found.', '%d results found.', items.length, 'wp-leadflow-crm' ),
									items.length
								)
								: __( 'No results found.', 'wp-leadflow-crm' ),
							'polite'
						);
					} catch ( error ) {
						if ( error.name !== 'AbortError' ) {
							wp.a11y.speak( __( 'Search failed. Please try again.', 'wp-leadflow-crm' ), 'assertive' );
						}
					}
				}, 250 );
			} );
		} );
	};

	/**
	 * Pipeline board: drag & drop plus the per-card "Move to" select.
	 * Moves are saved with admin-ajax (nonce + server-side checks) and
	 * reverted if the server refuses them.
	 */
	const initBoard = () => {
		const board = document.querySelector( '[data-lf-board]' );

		if ( ! board || ! window.ajaxurl ) {
			return;
		}

		const message = document.querySelector( '[data-lf-board-message]' );
		let dragged = null;
		let origin = null;

		const zoneOf = ( stage ) =>
			board.querySelector( `.lf-column[data-stage="${ CSS.escape( stage ) }"] [data-lf-dropzone]` );

		const nextCard = ( el ) => {
			let next = el.nextElementSibling;

			while ( next && ! next.classList.contains( 'lf-deal' ) ) {
				next = next.nextElementSibling;
			}

			return next;
		};

		const appendCard = ( zone, card ) => {
			zone.insertBefore( card, zone.querySelector( '.lf-column__empty' ) );
		};

		const refreshEmpty = () => {
			board.querySelectorAll( '[data-lf-dropzone]' ).forEach( ( zone ) => {
				const empty = zone.querySelector( '.lf-column__empty' );

				if ( empty ) {
					empty.hidden = !! zone.querySelector( '.lf-deal' );
				}
			} );
		};

		const showError = ( text ) => {
			wp.a11y.speak( text, 'assertive' );

			if ( message ) {
				message.querySelector( 'p' ).textContent = text;
				message.hidden = false;
				window.clearTimeout( showError.timer );
				showError.timer = window.setTimeout( () => {
					message.hidden = true;
				}, 6000 );
			}
		};

		const applyTotals = ( totals ) => {
			board.querySelectorAll( '.lf-column' ).forEach( ( column ) => {
				const total = totals[ column.dataset.stage ];

				if ( total ) {
					column.querySelector( '[data-lf-count]' ).textContent = new Intl.NumberFormat().format( total.count );
					column.querySelector( '[data-lf-value]' ).textContent = total.value;
				}
			} );
		};

		const save = async ( card, stage, beforeId, revert ) => {
			const body = new FormData();

			body.append( 'action', 'leadflow_crm_move_lead' );
			body.append( '_leadflow_nonce', board.dataset.nonce );
			body.append( 'lead_id', card.dataset.leadId );
			body.append( 'stage', stage );
			body.append( 's', board.dataset.filterS || '' );
			body.append( 'owner', board.dataset.filterOwner || '' );

			if ( beforeId ) {
				body.append( 'before_id', beforeId );
			}

			card.classList.add( 'is-saving' );
			card.setAttribute( 'aria-busy', 'true' );

			try {
				const response = await fetch( window.ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					body,
				} );
				const json = await response.json();

				if ( ! json.success ) {
					throw new Error( json.data && json.data.message ? json.data.message : '' );
				}

				const select = card.querySelector( '[data-lf-move]' );

				if ( select ) {
					select.value = json.data.lead.stage;
				}

				applyTotals( json.data.totals );
				wp.a11y.speak( json.data.message, 'polite' );
			} catch ( error ) {
				revert();
				showError( error.message || __( 'The lead could not be moved. Please try again.', 'wp-leadflow-crm' ) );
			} finally {
				card.classList.remove( 'is-saving' );
				card.removeAttribute( 'aria-busy' );
				refreshEmpty();
			}
		};

		// Drag & drop (mouse / touch-enabled browsers).
		board.addEventListener( 'dragstart', ( event ) => {
			const card = event.target.closest( '.lf-deal.is-movable' );

			if ( ! card ) {
				return;
			}

			dragged = card;
			origin = { zone: card.parentElement, next: card.nextElementSibling, stage: card.closest( '.lf-column' ).dataset.stage };
			card.classList.add( 'is-dragging' );
			event.dataTransfer.effectAllowed = 'move';
			event.dataTransfer.setData( 'text/plain', card.dataset.leadId );
		} );

		board.addEventListener( 'dragover', ( event ) => {
			const zone = dragged ? event.target.closest( '[data-lf-dropzone]' ) : null;

			if ( ! zone ) {
				return;
			}

			event.preventDefault();
			board.querySelectorAll( '.is-drop-target' ).forEach( ( el ) => el.classList.remove( 'is-drop-target' ) );
			zone.closest( '.lf-column' ).classList.add( 'is-drop-target' );

			const after = [ ...zone.querySelectorAll( '.lf-deal:not(.is-dragging)' ) ].find( ( card ) => {
				const box = card.getBoundingClientRect();

				return event.clientY < box.top + box.height / 2;
			} );

			if ( after ) {
				zone.insertBefore( dragged, after );
			} else {
				appendCard( zone, dragged );
			}

			refreshEmpty();
		} );

		board.addEventListener( 'drop', ( event ) => {
			if ( ! dragged ) {
				return;
			}

			event.preventDefault();

			const card = dragged;
			const from = origin;
			const stage = card.closest( '.lf-column' ).dataset.stage;
			const next = nextCard( card );

			if ( card.parentElement === from.zone && card.nextElementSibling === from.next ) {
				return;
			}

			save( card, stage, next ? next.dataset.leadId : null, () => {
				from.zone.insertBefore( card, from.next );
			} );
		} );

		board.addEventListener( 'dragend', () => {
			board.querySelectorAll( '.is-drop-target' ).forEach( ( el ) => el.classList.remove( 'is-drop-target' ) );

			if ( dragged ) {
				dragged.classList.remove( 'is-dragging' );
			}

			dragged = null;
			refreshEmpty();
		} );

		// Keyboard / screen reader: the "Move to" select on each card.
		board.addEventListener( 'change', ( event ) => {
			const select = event.target.closest( '[data-lf-move]' );

			if ( ! select ) {
				return;
			}

			const card = select.closest( '.lf-deal' );
			const zone = zoneOf( select.value );
			const from = { zone: card.parentElement, next: card.nextElementSibling, stage: card.closest( '.lf-column' ).dataset.stage };

			if ( ! zone ) {
				return;
			}

			appendCard( zone, card );
			refreshEmpty();
			select.focus();

			save( card, select.value, null, () => {
				from.zone.insertBefore( card, from.next );
				select.value = from.stage;
			} );
		} );
	};

	/**
	 * POSTs to admin-ajax and returns `data`, or throws with the server message.
	 *
	 * @param {FormData} body Request body (must contain `action`).
	 * @return {Promise<Object>} Response data.
	 */
	const ajax = async ( body ) => {
		const response = await fetch( config.ajaxUrl || window.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		} );

		let json = null;

		try {
			json = await response.json();
		} catch ( error ) {
			throw new Error( __( 'Unexpected server response. Please reload the page.', 'wp-leadflow-crm' ) );
		}

		if ( ! json || ! json.success ) {
			throw new Error(
				json && json.data && json.data.message
					? json.data.message
					: __( 'Something went wrong. Please try again.', 'wp-leadflow-crm' )
			);
		}

		return json.data;
	};

	const formData = ( action, nonce, fields ) => {
		const body = new FormData();

		body.append( 'action', action );
		body.append( '_leadflow_nonce', nonce );
		Object.keys( fields ).forEach( ( key ) => body.append( key, fields[ key ] ) );

		return body;
	};

	/**
	 * Shows an error notice below the page header and announces it.
	 *
	 * @param {string} message Message.
	 */
	const notify = ( message ) => {
		wp.a11y.speak( message, 'assertive' );

		const anchor = document.querySelector( '.lf-wrap .wp-header-end' );

		if ( ! anchor ) {
			return;
		}

		document.querySelectorAll( '[data-lf-notice]' ).forEach( ( el ) => el.remove() );

		const notice = document.createElement( 'div' );
		const text = document.createElement( 'p' );

		notice.className = 'notice notice-error';
		notice.setAttribute( 'data-lf-notice', '' );
		notice.setAttribute( 'role', 'alert' );
		text.textContent = message;
		notice.appendChild( text );
		anchor.after( notice );
	};

	/**
	 * Buttons marked with data-lf-busy show that work is in progress when
	 * their form is submitted (and cannot be clicked twice).
	 */
	const initBusy = () => {
		document.addEventListener( 'submit', ( event ) => {
			const form = event.target;

			if ( ! ( form instanceof HTMLFormElement ) || event.defaultPrevented ) {
				return;
			}

			form.querySelectorAll( '[data-lf-busy]' ).forEach( ( button ) => {
				const label = button.dataset.lfBusy;

				// Let the browser submit first, then lock the button.
				window.setTimeout( () => {
					button.setAttribute( 'aria-busy', 'true' );
					button.classList.add( 'is-busy' );
					button.disabled = true;

					if ( label ) {
						button.dataset.lfLabel = button.textContent.trim();
						button.textContent = label;
						wp.a11y.speak( label, 'polite' );
					}
				}, 0 );
			} );
		} );

		// A download does not reload the page: release the button afterwards.
		window.addEventListener( 'pageshow', () => {
			document.querySelectorAll( '[data-lf-busy].is-busy' ).forEach( ( button ) => {
				button.disabled = false;
				button.classList.remove( 'is-busy' );
				button.removeAttribute( 'aria-busy' );

				if ( button.dataset.lfLabel ) {
					button.textContent = button.dataset.lfLabel;
				}
			} );
		} );
	};

	/**
	 * Record tabs (ARIA tabs pattern: arrows, Home/End, deep links via #hash).
	 */
	const initTabs = () => {
		document.querySelectorAll( '[data-lf-tabs]' ).forEach( ( container ) => {
			const tabs = [ ...container.querySelectorAll( '[role="tab"]' ) ];

			if ( ! tabs.length ) {
				return;
			}

			container.classList.add( 'is-enhanced' );

			const select = ( tab, focus ) => {
				tabs.forEach( ( item ) => {
					const active = item === tab;
					const panel = document.getElementById( item.getAttribute( 'aria-controls' ) );

					item.setAttribute( 'aria-selected', active ? 'true' : 'false' );
					item.tabIndex = active ? 0 : -1;

					if ( panel ) {
						panel.hidden = ! active;
					}
				} );

				if ( focus ) {
					tab.focus();
				}
			};

			const remember = ( tab ) => {
				window.history.replaceState( null, '', `#${ tab.getAttribute( 'aria-controls' ) }` );
			};

			tabs.forEach( ( tab, index ) => {
				tab.addEventListener( 'click', () => {
					select( tab, false );
					remember( tab );
				} );

				tab.addEventListener( 'keydown', ( event ) => {
					const keys = {
						ArrowRight: ( index + 1 ) % tabs.length,
						ArrowLeft: ( index - 1 + tabs.length ) % tabs.length,
						Home: 0,
						End: tabs.length - 1,
					};

					if ( event.key in keys ) {
						event.preventDefault();
						select( tabs[ keys[ event.key ] ], true );
						remember( tabs[ keys[ event.key ] ] );
					}
				} );
			} );

			const fromHash = () => {
				const match = tabs.find( ( tab ) => `#${ tab.getAttribute( 'aria-controls' ) }` === window.location.hash );

				select( match || tabs[ 0 ], false );
			};

			fromHash();
			window.addEventListener( 'hashchange', fromHash );

			document.querySelectorAll( '[data-lf-open-tab]' ).forEach( ( link ) => {
				link.addEventListener( 'click', ( event ) => {
					const tab = tabs.find( ( item ) => item.dataset.lfTab === link.dataset.lfOpenTab );

					if ( tab ) {
						event.preventDefault();
						select( tab, true );
						remember( tab );
						tab.scrollIntoView( { block: 'nearest' } );
					}
				} );
			} );
		} );
	};

	/**
	 * Updates the overdue bubble on the Tasks menu item.
	 *
	 * @param {number} count Overdue tasks of the current user.
	 */
	const updateTaskBubble = ( count ) => {
		const link = document.querySelector( '#adminmenu a[href*="page=leadflow-crm-tasks"]' );
		const bubble = link ? link.querySelector( '.awaiting-mod' ) : null;

		if ( ! bubble ) {
			return;
		}

		if ( count > 0 ) {
			bubble.className = `awaiting-mod count-${ count }`;
			bubble.querySelector( '.pending-count' ).textContent = String( count );
		} else {
			bubble.remove();
		}
	};

	/**
	 * Task completion checkboxes (lists, panels, dashboard, task screen).
	 */
	const initTaskToggles = () => {
		document.addEventListener( 'change', async ( event ) => {
			const box = event.target.closest( '[data-lf-task-toggle]' );

			if ( ! box ) {
				return;
			}

			const done = box.checked;
			const row = box.closest( '[data-task-row]' );

			box.disabled = true;
			box.setAttribute( 'aria-busy', 'true' );

			try {
				const data = await ajax(
					formData( 'leadflow_crm_toggle_task', config.nonces.task, {
						task_id: box.dataset.lfTaskToggle,
						done: done ? '1' : '0',
					} )
				);

				if ( row ) {
					row.classList.toggle( 'is-completed', data.status === 'completed' );
					row.classList.toggle( 'is-overdue', !! data.overdue );
					row.querySelectorAll( '[data-lf-overdue]' ).forEach( ( pill ) => {
						pill.hidden = ! data.overdue;
					} );
				}

				document.querySelectorAll( '[data-lf-task-status] .lf-pill, [data-task-row] .column-status .lf-pill' ).forEach( ( pill ) => {
					if ( ! row || row.contains( pill ) || pill.closest( '[data-lf-task-status]' ) ) {
						pill.className = `lf-pill lf-pill--${ data.status }`;
						pill.textContent = data.status_label;
					}
				} );

				updateTaskBubble( data.overdue_count );
				wp.a11y.speak( data.message, 'polite' );
			} catch ( error ) {
				box.checked = ! done;
				notify( error.message );
			} finally {
				box.disabled = false;
				box.removeAttribute( 'aria-busy' );
			}
		} );
	};

	/**
	 * Timeline: log an activity and load older entries without reloading.
	 */
	const initTimeline = () => {
		document.addEventListener( 'submit', async ( event ) => {
			const form = event.target.closest( '[data-lf-log-activity]' );

			if ( ! form ) {
				return;
			}

			event.preventDefault();

			const button = form.querySelector( '[type="submit"]' );
			const list = form.parentElement.querySelector( '[data-lf-timeline]' );

			button.disabled = true;

			try {
				const data = await ajax( new FormData( form ) );

				list.insertAdjacentHTML( 'afterbegin', data.html );
				list.firstElementChild.classList.add( 'is-new' );
				form.parentElement.querySelectorAll( '[data-lf-timeline-empty]' ).forEach( ( el ) => el.remove() );
				form.reset();
				wp.a11y.speak( data.message, 'polite' );
			} catch ( error ) {
				notify( error.message );
			} finally {
				button.disabled = false;
			}
		} );

		document.addEventListener( 'click', async ( event ) => {
			const link = event.target.closest( '[data-lf-timeline-more]' );

			if ( ! link ) {
				return;
			}

			event.preventDefault();

			const list = link.closest( '.lf-timeline-wrap' ).querySelector( '[data-lf-timeline]' );
			const page = parseInt( list.dataset.page, 10 ) + 1;

			link.setAttribute( 'aria-busy', 'true' );

			try {
				const data = await ajax(
					formData( 'leadflow_crm_timeline', config.nonces.activity, {
						parent: list.dataset.parent,
						parent_id: list.dataset.parentId,
						page: String( page ),
					} )
				);

				list.insertAdjacentHTML( 'beforeend', data.html );
				list.dataset.page = String( page );

				if ( ! data.has_more ) {
					link.parentElement.remove();
				}

				wp.a11y.speak( __( 'Older activity loaded.', 'wp-leadflow-crm' ), 'polite' );
			} catch ( error ) {
				notify( error.message );
			} finally {
				link.removeAttribute( 'aria-busy' );
			}
		} );
	};

	/**
	 * Follow-ups: "Mark as done" without reloading.
	 */
	const initFollowUps = () => {
		document.addEventListener( 'submit', async ( event ) => {
			const form = event.target.closest( '[data-lf-follow-up-complete]' );

			if ( ! form ) {
				return;
			}

			event.preventDefault();

			const item = form.closest( '[data-follow-up-id]' );
			const button = form.querySelector( '[type="submit"]' );

			button.disabled = true;

			try {
				const data = await ajax( new FormData( form ) );

				item.outerHTML = data.html;
				wp.a11y.speak( data.message, 'polite' );
			} catch ( error ) {
				button.disabled = false;
				notify( error.message );
			}
		} );
	};

	/**
	 * Email templates: insert placeholders at the cursor and live preview.
	 */
	const initTemplates = () => {
		const preview = document.querySelector( '[data-lf-template-preview]' );
		const subjectField = document.getElementById( 'lf-field-subject' );
		const bodyField = document.getElementById( 'lf-field-body' );
		let lastField = bodyField;

		[ subjectField, bodyField ].forEach( ( field ) => {
			if ( field ) {
				field.addEventListener( 'focus', () => {
					lastField = field;
				} );
			}
		} );

		document.addEventListener( 'click', ( event ) => {
			const button = event.target.closest( '[data-lf-placeholder]' );

			if ( ! button || ! lastField ) {
				return;
			}

			const token = button.dataset.lfPlaceholder;
			const start = lastField.selectionStart ?? lastField.value.length;
			const end = lastField.selectionEnd ?? lastField.value.length;

			lastField.setRangeText( token, start, end, 'end' );
			lastField.focus();
			lastField.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			wp.a11y.speak( wp.i18n.sprintf( __( '%s inserted.', 'wp-leadflow-crm' ), token ), 'polite' );
		} );

		if ( ! preview ) {
			return;
		}

		const record = preview.querySelector( '[data-lf-preview-record]' );
		const subjectOut = preview.querySelector( '[data-lf-preview-subject]' );
		const bodyOut = preview.querySelector( '[data-lf-preview-body]' );
		const unknownOut = preview.querySelector( '[data-lf-preview-unknown]' );
		// The compose screen previews through its own endpoint (adds the signature).
		const action = preview.dataset.lfPreviewAction || 'leadflow_crm_preview_template';
		const nonce = config.nonces[ preview.dataset.lfPreviewNonce || 'template' ];
		let timer = null;

		const refresh = async () => {
			const subject = subjectField ? subjectField.value : preview.dataset.templateSubject || '';
			const body = bodyField ? bodyField.value : preview.dataset.templateBody || '';

			try {
				const data = await ajax(
					formData( action, nonce, {
						subject,
						body,
						record: record ? record.value : '',
					} )
				);

				subjectOut.textContent = data.subject;
				// The body is sanitized server-side with wp_kses_post().
				bodyOut.innerHTML = data.body;

				if ( unknownOut ) {
					unknownOut.hidden = ! data.unknown.length;
					unknownOut.textContent = data.unknown.length
						? __( 'Unknown placeholders:', 'wp-leadflow-crm' ) + ' ' + data.unknown.map( ( key ) => `{{${ key }}}` ).join( ', ' )
						: '';
				}
			} catch ( error ) {
				subjectOut.textContent = '';
				bodyOut.textContent = error.message;
			}
		};

		const schedule = () => {
			window.clearTimeout( timer );
			timer = window.setTimeout( refresh, 400 );
		};

		[ subjectField, bodyField ].forEach( ( field ) => field && field.addEventListener( 'input', schedule ) );

		if ( record ) {
			record.addEventListener( 'change', refresh );
		}

		if ( subjectField ) {
			refresh();
		}
	};

	/**
	 * Compose screen: fill subject and message from a template, prevent
	 * double sending. Without JavaScript the template form reloads the page.
	 */
	const initCompose = () => {
		const form = document.querySelector( '[data-lf-compose]' );

		if ( ! form ) {
			return;
		}

		const picker = document.querySelector( '[data-lf-compose-template]' );
		const templateId = form.querySelector( '[data-lf-compose-template-id]' );
		const subjectField = document.getElementById( 'lf-field-subject' );
		const bodyField = document.getElementById( 'lf-field-body' );

		if ( picker && subjectField && bodyField ) {
			let templates = {};

			try {
				templates = JSON.parse( picker.dataset.templates || '{}' );
			} catch ( error ) {
				templates = {};
			}

			let previous = picker.value;

			picker.addEventListener( 'change', () => {
				const template = templates[ picker.value ];

				if ( ! template ) {
					templateId.value = '';
					previous = picker.value;
					return;
				}

				const edited = bodyField.value.trim() !== '' && ! Object.values( templates ).some( ( t ) => t.body === bodyField.value );

				// eslint-disable-next-line no-alert
				if ( edited && ! window.confirm( __( 'Replace the subject and message with this template?', 'wp-leadflow-crm' ) ) ) {
					picker.value = previous;
					return;
				}

				subjectField.value = template.subject;
				bodyField.value = template.body;
				templateId.value = picker.value;
				previous = picker.value;
				bodyField.dispatchEvent( new Event( 'input', { bubbles: true } ) );
				wp.a11y.speak( __( 'Template applied.', 'wp-leadflow-crm' ), 'polite' );
			} );
		}

		form.addEventListener( 'submit', () => {
			const button = form.querySelector( '[type="submit"]' );

			// Let the browser submit, then block a second click.
			window.setTimeout( () => {
				button.disabled = true;
				button.setAttribute( 'aria-busy', 'true' );
			}, 0 );
		} );
	};

	/**
	 * CSV import: runs the import in AJAX batches with a progress bar.
	 * Without JavaScript the form imports everything in one request.
	 */
	const initImport = () => {
		document.querySelectorAll( '.lf-mapping select' ).forEach( ( select ) => {
			select.addEventListener( 'change', () => select.classList.toggle( 'is-skipped', '' === select.value ) );
		} );

		const form = document.querySelector( '[data-lf-import-run]' );

		if ( ! form ) {
			return;
		}

		form.addEventListener( 'submit', async ( event ) => {
			event.preventDefault();

			const panel = document.querySelector( '[data-lf-import-progress]' );
			const bar = panel.querySelector( '[data-lf-import-bar]' );
			const status = panel.querySelector( '[data-lf-import-status]' );

			form.querySelector( '[type="submit"]' ).disabled = true;
			panel.hidden = false;

			try {
				for ( ;; ) {
					const data = await ajax(
						formData( 'leadflow_crm_import_batch', config.nonces.import, { import: form.dataset.token } )
					);

					bar.max = data.total;
					bar.value = data.processed;
					status.textContent = wp.i18n.sprintf(
						/* translators: 1: Processed rows, 2: Total rows, 3: Created, 4: Updated, 5: Failed. */
						__( 'Imported %1$d of %2$d rows — %3$d created, %4$d updated, %5$d failed.', 'wp-leadflow-crm' ),
						data.processed,
						data.total,
						data.created,
						data.updated,
						data.failed
					);

					if ( data.done ) {
						wp.a11y.speak( __( 'Import complete.', 'wp-leadflow-crm' ), 'polite' );
						window.location.assign( data.redirect );
						return;
					}
				}
			} catch ( error ) {
				notify( error.message );
				form.querySelector( '[type="submit"]' ).disabled = false;
			}
		} );
	};

	const init = () => {
		initTemplates();
		initCompose();
		initImport();
		initBusy();
		initStatusCopy();
		initConfirm();
		initErrorSummary();
		initRecordSearch();
		initBoard();
		initTabs();
		initTaskToggles();
		initTimeline();
		initFollowUps();
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
