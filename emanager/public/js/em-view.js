/**
 * eManager — record view page: field display, comments, PDF export, edit/delete.
 */
( function ( EM ) {
	'use strict';

	EM.view = {
		async render( container, module, id ) {
			const caps = EM.app.boot.caps;
			container.innerHTML = await EM.tpl.forModule( module, 'view' );

			const refs = {
				title: container.querySelector( '[data-em="view-title"]' ),
				status: container.querySelector( '[data-em="view-status"]' ),
				fields: container.querySelector( '[data-em="view-fields"]' ),
				meta: container.querySelector( '[data-em="view-meta"]' ),
				edit: container.querySelector( '[data-em="btn-edit"]' ),
				del: container.querySelector( '[data-em="btn-delete"]' ),
				pdf: container.querySelector( '[data-em="btn-pdf"]' ),
				back: container.querySelector( '[data-em="btn-back"]' ),
				comments: container.querySelector( '[data-em="comments"]' ),
				commentForm: container.querySelector( '[data-em="comment-form"]' ),
				workflow: container.querySelector( '[data-em="workflow"]' ),
				workflowState: container.querySelector( '[data-em="workflow-state"]' ),
				workflowActions: container.querySelector( '[data-em="workflow-actions"]' ),
				workflowLinks: container.querySelector( '[data-em="workflow-links"]' ),
				workflowMap: container.querySelector( '[data-em="workflow-map"]' ),
				activityCard: container.querySelector( '[data-em="activity-card"]' ),
				activity: container.querySelector( '[data-em="activity"]' ),
				linksCard: container.querySelector( '[data-em="links-card"]' ),
				links: container.querySelector( '[data-em="links"]' ),
			};

			let record;
			try {
				record = await EM.api.get( module.id, id );
			} catch ( error ) {
				container.innerHTML = `<div class="alert alert-danger">${ EM.tpl.esc( error.message ) }</div>`;
				return;
			}

			const titleField = ( module.fields || [] )[ 0 ];
			if ( refs.title ) refs.title.textContent = record[ titleField?.name ] || module.name + ' record';
			if ( refs.status ) refs.status.innerHTML = EM.tpl.statusBadge( record.status );

			// Field grid.
			refs.fields.innerHTML = ( module.fields || [] ).map( ( field ) => `
				<div class="col-sm-6 ${ [ 'textarea', 'richtext' ].includes( field.type ) ? 'col-12' : '' }">
					<dt>${ EM.tpl.esc( field.label ) }</dt>
					<dd>${ EM.tpl.format( field, record[ field.name ] ) }</dd>
				</div>` ).join( '' );

			if ( refs.meta ) {
				const created = record.created_at ? new Date( record.created_at ).toLocaleString() : '—';
				refs.meta.textContent = `Created ${ created } by ${ record.created_by_name || 'unknown' }`;
			}

			// Related records (parent + spawned children).
			this.renderLinks( refs, record._links );

			// Actions.
			if ( refs.back ) refs.back.addEventListener( 'click', () => { location.hash = `#/${ module.section }/${ module.id }`; } );

			if ( refs.edit ) {
				if ( caps.em_update ) {
					refs.edit.addEventListener( 'click', () => {
						location.hash = `#/${ module.section }/${ module.id }/form/${ id }`;
					} );
				} else {
					refs.edit.classList.add( 'd-none' );
				}
			}

			if ( refs.del ) {
				if ( record._can_delete ) {
					refs.del.addEventListener( 'click', async () => {
						if ( ! window.confirm( 'Delete this record? This cannot be undone.' ) ) return;
						try {
							await EM.api.remove( module.id, id );
							EM.tpl.toast( 'Record deleted.' );
							location.hash = `#/${ module.section }/${ module.id }`;
						} catch ( error ) {
							EM.tpl.toast( error.message, 'danger' );
						}
					} );
				} else {
					refs.del.classList.add( 'd-none' );
				}
			}

			if ( refs.pdf ) {
				refs.pdf.addEventListener( 'click', () => EM.pdf.fromRecord( module, record ) );
			}

			// Workflow actions, record linking and activity timeline.
			if ( module.workflow ) {
				this.renderWorkflow( refs, module, id, record );
				await this.loadActivity( refs, module, id );
			}

			// Comments.
			await this.loadComments( refs, module, id );

			if ( refs.commentForm ) {
				refs.commentForm.addEventListener( 'submit', async ( event ) => {
					event.preventDefault();
					const textarea = refs.commentForm.querySelector( 'textarea' );
					const body = textarea.value.trim();
					if ( ! body ) return;
					try {
						await EM.api.addComment( module.id, id, body );
						textarea.value = '';
						await this.loadComments( refs, module, id );
					} catch ( error ) {
						EM.tpl.toast( error.message, 'danger' );
					}
				} );
			}
		},

		/**
		 * Render the related-records panel: the parent this record was spawned
		 * from, and any children spawned from it — each clickable.
		 *
		 * @param {Object} refs  View refs.
		 * @param {Object} links { parent, children } from the API.
		 */
		renderLinks( refs, links ) {
			if ( ! refs.linksCard || ! links ) return;
			const parent = links.parent;
			const children = links.children || [];
			if ( ! parent && ! children.length ) return; // Leave hidden.

			const chip = ( rel ) => `
				<a class="d-flex align-items-center gap-2 text-body text-decoration-none border rounded px-2 py-1 mb-1"
					href="#/${ rel.section }/${ rel.module }/view/${ rel.id }">
					<span class="badge text-bg-secondary">${ EM.tpl.esc( rel.name ) }</span>
					<span class="flex-grow-1 text-truncate">${ EM.tpl.esc( rel.title ) }</span>
					${ rel.status ? EM.tpl.statusBadge( rel.status ) : '' }
					<i class="bi bi-chevron-right text-secondary" aria-hidden="true"></i>
				</a>`;

			let html = '';
			if ( parent ) {
				html += `<div class="small text-secondary mb-1">Spawned from</div>${ chip( parent ) }`;
			}
			if ( children.length ) {
				html += `<div class="small text-secondary mb-1 mt-2">Spawned records (${ children.length })</div>` +
					children.map( ( c ) => chip( c ) ).join( '' );
			}
			refs.links.innerHTML = html;
			refs.linksCard.classList.remove( 'd-none' );
		},

		/**
		 * Render workflow transition buttons (role-gated) + record-linking
		 * ("convert to…") buttons, driven by the server's _transitions list.
		 */
		renderWorkflow( refs, module, id, record ) {
			if ( ! refs.workflow ) return;
			refs.workflow.classList.remove( 'd-none' );
			if ( refs.workflowState ) {
				refs.workflowState.innerHTML = 'Current: ' + EM.tpl.statusBadge( record.status );
			}

			// Workflow map: the state path with the current step highlighted.
			if ( refs.workflowMap ) {
				const states = ( module.workflow && module.workflow.states )
					? Object.keys( module.workflow.states ) : [];
				const curIdx = states.indexOf( record.status );
				refs.workflowMap.innerHTML = states.map( ( s, i ) => {
					const tone = i < curIdx ? 'done' : ( i === curIdx ? 'current' : 'todo' );
					const sep = i < states.length - 1 ? '<span class="em-wf-sep" aria-hidden="true">→</span>' : '';
					return `<span class="em-wf-step em-wf-${ tone }">${ EM.tpl.esc( s ) }</span>${ sep }`;
				} ).join( '' );
			}

			const transitions = record._transitions || [];
			if ( ! transitions.length ) {
				refs.workflowActions.innerHTML = '<span class="text-secondary small">No further actions from this status.</span>';
			} else {
				refs.workflowActions.innerHTML = '';
				transitions.forEach( ( t ) => {
					// Required fields still empty on this record block the step.
					const unmet = ( t.requires || [] ).filter( ( name ) => {
						const v = record[ name ];
						return v === '' || v === null || v === undefined;
					} );
					const labelFor = ( name ) => {
						const fld = ( module.fields || [] ).find( ( f ) => f.name === name );
						return fld ? fld.label : name;
					};

					const btn = document.createElement( 'button' );
					btn.type = 'button';
					const blocked = unmet.length > 0;
					btn.className = 'btn btn-sm ' + ( t.allowed && ! blocked ? 'btn-primary' : 'btn-outline-secondary' );
					btn.disabled = ! t.allowed || blocked;
					const flag = blocked ? '<i class="bi bi-exclamation-triangle ms-1" aria-hidden="true"></i>' : '';
					btn.innerHTML = `<i class="bi bi-arrow-right-circle me-1" aria-hidden="true"></i>${ EM.tpl.esc( t.label || t.to ) }${ flag }`;
					if ( blocked ) {
						btn.title = 'Complete first: ' + unmet.map( labelFor ).join( ', ' );
					} else if ( ! t.allowed && t.reason ) {
						btn.title = t.reason;
					}
					if ( t.allowed && ! blocked ) {
						btn.addEventListener( 'click', () => this.doTransition( module, id, t ) );
					}
					refs.workflowActions.appendChild( btn );
				} );
			}

			// Record-linking ("convert to…") buttons.
			if ( refs.workflowLinks ) {
				const relations = module.relations || [];
				refs.workflowLinks.innerHTML = '';
				if ( relations.length && EM.app.boot.caps.em_create ) {
					relations.forEach( ( rel ) => {
						const btn = document.createElement( 'button' );
						btn.type = 'button';
						btn.className = 'btn btn-sm btn-outline-primary';
						btn.innerHTML = `<i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>${ EM.tpl.esc( rel.label || rel.spawn ) }`;
						btn.addEventListener( 'click', () => this.doSpawn( module, id, rel ) );
						refs.workflowLinks.appendChild( btn );
					} );
				}
			}
		},

		/** Perform a transition, prompting for a direction when required. */
		async doTransition( module, id, transition ) {
			const payload = { to: transition.to };
			if ( transition.directions && transition.directions.length ) {
				const choice = window.prompt(
					'Direction for "' + ( transition.label || transition.to ) + '"\nEnter one of: ' + transition.directions.join( ', ' ) +
					'\n(P&P = Proceed & Pricing, PO = Pricing Only, DNP = Do Not Proceed)',
					transition.directions[ 0 ]
				);
				if ( choice === null ) return;
				if ( ! transition.directions.includes( choice ) ) {
					EM.tpl.toast( 'Invalid direction.', 'danger' );
					return;
				}
				payload.direction = choice;
			}
			try {
				await EM.api.transition( module.id, id, payload );
				EM.tpl.toast( 'Status updated to “' + transition.to + '”.' );
				EM.app.route(); // Re-render the view with the new state.
			} catch ( error ) {
				EM.tpl.toast( error.message, 'danger' );
			}
		},

		/** Convert this record into a linked record in another module. */
		async doSpawn( module, id, rel ) {
			if ( ! window.confirm( ( rel.label || 'Create linked record' ) + '?' ) ) return;
			try {
				const result = await EM.api.spawn( module.id, id, rel.spawn );
				EM.tpl.toast( 'Created linked record.' );
				location.hash = `#/${ result.section }/${ result.module }/view/${ result.record.id }`;
			} catch ( error ) {
				EM.tpl.toast( error.message, 'danger' );
			}
		},

		/** Load and render the activity timeline. */
		async loadActivity( refs, module, id ) {
			if ( ! refs.activity || ! refs.activityCard ) return;
			try {
				const events = await EM.api.activity( module.id, id );
				refs.activityCard.classList.remove( 'd-none' );
				if ( ! events.length ) {
					refs.activity.innerHTML = '<li class="text-secondary small">No activity yet.</li>';
					return;
				}
				refs.activity.innerHTML = events.map( ( e ) => {
					const when = new Date( e.created_at ).toLocaleString();
					let summary;
					if ( e.action === 'transition' ) {
						summary = `moved <strong>${ EM.tpl.esc( e.from_status || '—' ) }</strong> → ${ EM.tpl.statusBadge( e.to_status ) }`;
					} else if ( e.action === 'created' ) {
						summary = 'created' + ( e.to_status ? ' as ' + EM.tpl.statusBadge( e.to_status ) : '' );
					} else if ( e.action === 'linked' ) {
						summary = EM.tpl.esc( e.note || 'linked a record' );
					} else {
						summary = EM.tpl.esc( e.action );
					}
					const note = ( e.note && e.action === 'transition' ) ? ` <span class="text-secondary">— ${ EM.tpl.esc( e.note ) }</span>` : '';
					return `<li class="em-timeline-item">
						<div class="small"><strong>${ EM.tpl.esc( e.actor_name || 'Someone' ) }</strong> ${ summary }${ note }</div>
						<div class="text-secondary" style="font-size:.78rem">${ when }</div>
					</li>`;
				} ).join( '' );
			} catch ( e ) {
				refs.activityCard.classList.add( 'd-none' );
			}
		},

		async loadComments( refs, module, id ) {
			if ( ! refs.comments ) return;
			try {
				const comments = await EM.api.comments( module.id, id );
				if ( ! comments.length ) {
					refs.comments.innerHTML = '<p class="text-secondary small mb-0">No comments yet.</p>';
					return;
				}
				refs.comments.innerHTML = comments.map( ( c ) => `
					<div class="em-comment">
						<div class="d-flex justify-content-between small text-secondary">
							<strong>${ EM.tpl.esc( c.author_name ) }</strong>
							<span>${ new Date( c.created_at ).toLocaleString() }</span>
						</div>
						<div>${ EM.tpl.esc( c.body ).replace( /\n/g, '<br>' ) }</div>
					</div>` ).join( '' );
			} catch ( e ) {
				refs.comments.innerHTML = '<p class="text-danger small mb-0">Could not load comments.</p>';
			}
		},
	};
} )( window.EM );
