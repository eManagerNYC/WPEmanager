/**
 * eManager — add/edit form generated from the module's field definitions.
 * Bootstrap-validated; supports lookups against Resources modules.
 */
( function ( EM ) {
	'use strict';

	EM.form = {
		/**
		 * Render the add/edit form.
		 *
		 * @param {Element} container Main container.
		 * @param {Object}  module    Module definition.
		 * @param {string}  [id]      Record id when editing.
		 */
		async render( container, module, id ) {
			const caps = EM.app.boot.caps;
			if ( ( id && ! caps.em_update ) || ( ! id && ! caps.em_create ) ) {
				container.innerHTML = `<div class="alert alert-warning">You do not have permission for this action.</div>`;
				return;
			}

			container.innerHTML = await EM.tpl.forModule( module, 'form' );

			const refs = {
				title: container.querySelector( '[data-em="form-title"]' ),
				fields: container.querySelector( '[data-em="form-fields"]' ),
				form: container.querySelector( '[data-em="form"]' ),
				cancel: container.querySelector( '[data-em="btn-cancel"]' ),
			};

			let record = {};
			if ( id ) {
				record = await EM.api.get( module.id, id );
			}

			if ( refs.title ) refs.title.textContent = ( id ? 'Edit — ' : 'New — ' ) + module.name;
			refs.fields.innerHTML = '';

			for ( const field of module.fields || [] ) {
				refs.fields.appendChild( await this.control( field, record[ field.name ] ) );
			}

			// Status select for edits (creation uses the first status automatically).
			if ( id && ( module.statuses || [] ).length ) {
				refs.fields.appendChild( await this.control( {
					name: 'status',
					label: 'Status',
					type: 'select',
					options: module.statuses,
				}, record.status ) );
			}

			if ( refs.cancel ) {
				refs.cancel.addEventListener( 'click', () => history.back() );
			}

			refs.form.addEventListener( 'submit', async ( event ) => {
				event.preventDefault();
				if ( ! refs.form.checkValidity() ) {
					refs.form.classList.add( 'was-validated' );
					return;
				}

				const data = this.collect( refs.form, module );
				const button = refs.form.querySelector( '[type="submit"]' );
				button.disabled = true;

				try {
					const saved = id
						? await EM.api.update( module.id, id, data )
						: await EM.api.create( module.id, data );
					EM.tpl.toast( 'Record saved.' );
					location.hash = `#/${ module.section }/${ module.id }/view/${ saved.id }`;
				} catch ( error ) {
					EM.tpl.toast( error.message, 'danger' );
					button.disabled = false;
				}
			} );
		},

		/**
		 * Build one Bootstrap form control wrapper for a field definition.
		 */
		async control( field, value ) {
			const wrap = document.createElement( 'div' );
			wrap.className = field.width === 'full' || [ 'textarea', 'richtext', 'json' ].includes( field.type )
				? 'col-12' : 'col-md-6';

			const required = field.required ? 'required' : '';
			const requiredMark = field.required ? ' <span class="text-danger">*</span>' : '';
			const fid = 'em-f-' + field.name;
			const val = value ?? field.default ?? '';
			const esc = EM.tpl.esc.bind( EM.tpl );

			let control = '';
			switch ( field.type ) {
				case 'textarea':
				case 'richtext':
					control = `<textarea class="form-control" id="${ fid }" name="${ field.name }" rows="${ field.rows || 4 }" ${ required }>${ esc( val ) }</textarea>`;
					break;
				case 'select': {
					let options = field.options || [];
					// Relational lookup: pull options from another module (Resources).
					if ( field.lookup ) {
						options = await this.lookupOptions( field.lookup );
					}
					const opts = options.map( ( o ) => {
						const v = typeof o === 'object' ? o.value : o;
						const l = typeof o === 'object' ? o.label : o;
						return `<option value="${ esc( v ) }" ${ String( v ) === String( val ) ? 'selected' : '' }>${ esc( l ) }</option>`;
					} ).join( '' );
					control = `<select class="form-select" id="${ fid }" name="${ field.name }" ${ required }><option value="">— select —</option>${ opts }</select>`;
					break;
				}
				case 'checkbox':
					wrap.innerHTML = `<div class="form-check mt-4">
						<input class="form-check-input" type="checkbox" id="${ fid }" name="${ field.name }" ${ val ? 'checked' : '' } />
						<label class="form-check-label" for="${ fid }">${ esc( field.label ) }</label></div>`;
					return wrap;
				case 'signature':
					wrap.className = 'col-12 col-lg-6';
					wrap.innerHTML = `<label class="form-label">${ esc( field.label ) }${ requiredMark }</label>
						<div class="em-signature border rounded">
							<canvas height="160" aria-label="${ esc( field.label ) } signature pad" role="img"></canvas>
						</div>
						<input type="hidden" name="${ field.name }" />
						<div class="d-flex align-items-center gap-2 mt-1">
							<button type="button" class="btn btn-sm btn-outline-secondary" data-sig="clear"><i class="bi bi-eraser" aria-hidden="true"></i> Clear</button>
							<small class="text-secondary">Draw with mouse or finger; saved with the record.</small>
						</div>`;
					this.initSignaturePad( wrap, val );
					return wrap;
				case 'number':
				case 'currency':
					control = `<input class="form-control" type="number" step="${ field.step || 'any' }" id="${ fid }" name="${ field.name }" value="${ esc( val ) }" ${ required } />`;
					break;
				case 'date':
					control = `<input class="form-control" type="date" id="${ fid }" name="${ field.name }" value="${ esc( val ) }" ${ required } />`;
					break;
				case 'datetime':
					control = `<input class="form-control" type="datetime-local" id="${ fid }" name="${ field.name }" value="${ esc( String( val ).slice( 0, 16 ) ) }" ${ required } />`;
					break;
				case 'email':
					control = `<input class="form-control" type="email" id="${ fid }" name="${ field.name }" value="${ esc( val ) }" ${ required } />`;
					break;
				case 'combo': {
					// Text input with autocomplete suggestions from a source
					// (companies / users / another module), but free entry allowed.
					const listId = 'dl-' + fid;
					wrap.innerHTML = `<label class="form-label" for="${ fid }">${ esc( field.label ) }${ requiredMark }</label>
						<input class="form-control" type="text" id="${ fid }" name="${ field.name }" value="${ esc( val ) }" list="${ listId }" autocomplete="off" ${ required } />
						<datalist id="${ listId }"></datalist>
						${ field.help ? `<div class="form-text">${ esc( field.help ) }</div>` : '' }`;
					this.fillCombo( wrap.querySelector( '#' + CSS.escape( listId ) ), field.source );
					return wrap;
				}
				case 'url':
				case 'file':
					// URL/file field with an optional "Upload" button (Media Library).
					wrap.innerHTML = `<label class="form-label" for="${ fid }">${ esc( field.label ) }${ requiredMark }</label>
						<div class="input-group">
							<input class="form-control" type="url" id="${ fid }" name="${ field.name }" value="${ esc( val ) }" placeholder="https://… or upload →" ${ required } />
							<button class="btn btn-outline-secondary" type="button" data-em-upload><i class="bi bi-upload" aria-hidden="true"></i> Upload</button>
							<input type="file" hidden data-em-file />
						</div>
						${ field.help ? `<div class="form-text">${ esc( field.help ) }</div>` : '' }
						<div class="form-text" data-em-upload-status></div>`;
					this.initUpload( wrap );
					return wrap;
				default:
					control = `<input class="form-control" type="text" id="${ fid }" name="${ field.name }" value="${ esc( val ) }" ${ required } />`;
			}

			wrap.innerHTML = `<label class="form-label" for="${ fid }">${ esc( field.label ) }${ requiredMark }</label>
				${ control }
				${ field.help ? `<div class="form-text">${ esc( field.help ) }</div>` : '' }
				<div class="invalid-feedback">Please provide a valid value.</div>`;
			return wrap;
		},

		/**
		 * Populate a <datalist> for a combo field from a source: "users",
		 * "companies", or another module id. Failures are silent (free text still works).
		 *
		 * @param {Element} datalist The datalist element.
		 * @param {string}  source   Source identifier.
		 */
		async fillCombo( datalist, source ) {
			if ( ! datalist || ! source ) return;
			try {
				let values = [];
				if ( 'users' === source ) {
					values = ( await EM.api.users() ).map( ( u ) => u.name );
				} else {
					const res = await EM.api.list( source, { per_page: 200, sort: 'name', order: 'asc' } );
					values = ( res.records || [] ).map( ( r ) => r.name || r.title || '' );
				}
				datalist.innerHTML = values
					.filter( ( v, i, a ) => v && a.indexOf( v ) === i )
					.map( ( v ) => `<option value="${ EM.tpl.esc( v ) }"></option>` )
					.join( '' );
			} catch ( e ) {
				/* free text still works without suggestions */
			}
		},

		/**
		 * Wire the "Upload" button on a URL/file field: pick a file, send it to
		 * the Media Library upload endpoint, and drop the returned URL into the
		 * text input. The user can still paste an external URL instead.
		 *
		 * @param {Element} wrap The field wrapper.
		 */
		initUpload( wrap ) {
			const urlInput = wrap.querySelector( 'input[type="url"]' );
			const fileInput = wrap.querySelector( '[data-em-file]' );
			const button = wrap.querySelector( '[data-em-upload]' );
			const status = wrap.querySelector( '[data-em-upload-status]' );
			if ( ! urlInput || ! fileInput || ! button ) return;

			button.addEventListener( 'click', () => fileInput.click() );
			fileInput.addEventListener( 'change', async () => {
				const file = fileInput.files && fileInput.files[ 0 ];
				if ( ! file ) return;
				button.disabled = true;
				status.textContent = 'Uploading…';
				try {
					const res = await EM.api.upload( file );
					urlInput.value = res.url;
					status.innerHTML = `<span class="text-success"><i class="bi bi-check-lg"></i> ${ EM.tpl.esc( res.filename ) }</span>`;
				} catch ( error ) {
					status.innerHTML = `<span class="text-danger">${ EM.tpl.esc( error.message ) }</span>`;
				} finally {
					button.disabled = false;
				}
			} );
		},

		/**
		 * Wire a vanilla canvas signature pad (pointer events, touch friendly).
		 * The PNG data URL is mirrored into the hidden input on every stroke.
		 *
		 * @param {Element} wrap     The field wrapper produced by control().
		 * @param {string}  existing Existing signature data URL (when editing).
		 */
		initSignaturePad( wrap, existing ) {
			const canvas = wrap.querySelector( 'canvas' );
			const hidden = wrap.querySelector( 'input[type="hidden"]' );
			const ctx = canvas.getContext( '2d' );
			let drawing = false;
			let dirty = false;

			const blank = () => {
				ctx.fillStyle = '#ffffff';
				ctx.fillRect( 0, 0, canvas.width, canvas.height );
				ctx.strokeStyle = '#1a1d21';
				ctx.lineWidth = 2;
				ctx.lineCap = 'round';
				ctx.lineJoin = 'round';
			};

			// Size the canvas once it is laid out, then restore any existing signature.
			const observer = new ResizeObserver( () => {
				const width = canvas.parentElement.clientWidth;
				if ( ! width || canvas.width === width ) return;
				canvas.width = width;
				blank();
				if ( existing && /^data:image\/png;base64,/.test( existing ) ) {
					const img = new Image();
					img.onload = () => {
						ctx.drawImage( img, 0, 0, Math.min( img.width, canvas.width ), Math.min( img.height, canvas.height ) );
						hidden.value = existing;
					};
					img.src = existing;
				}
			} );
			observer.observe( canvas.parentElement );

			const point = ( event ) => {
				const rect = canvas.getBoundingClientRect();
				return [ event.clientX - rect.left, event.clientY - rect.top ];
			};

			canvas.addEventListener( 'pointerdown', ( event ) => {
				drawing = true;
				canvas.setPointerCapture( event.pointerId );
				ctx.beginPath();
				ctx.moveTo( ...point( event ) );
			} );
			canvas.addEventListener( 'pointermove', ( event ) => {
				if ( ! drawing ) return;
				ctx.lineTo( ...point( event ) );
				ctx.stroke();
				dirty = true;
			} );
			const stop = () => {
				if ( drawing && dirty ) hidden.value = canvas.toDataURL( 'image/png' );
				drawing = false;
			};
			canvas.addEventListener( 'pointerup', stop );
			canvas.addEventListener( 'pointercancel', stop );

			wrap.querySelector( '[data-sig="clear"]' ).addEventListener( 'click', () => {
				blank();
				hidden.value = '';
				dirty = false;
				existing = '';
			} );
		},

		/**
		 * Options for a relational lookup (e.g. cost codes, locations).
		 * lookup = { module: "cost-codes", value: "code", label: "title" }
		 */
		async lookupOptions( lookup ) {
			try {
				const result = await EM.api.list( lookup.module, { per_page: 200, sort: lookup.label, order: 'asc' } );
				return ( result.records || [] ).map( ( r ) => ( {
					value: r[ lookup.value || 'id' ],
					label: r[ lookup.label || 'title' ],
				} ) );
			} catch ( e ) {
				return [];
			}
		},

		/** Collect form values typed per the module definition. */
		collect( form, module ) {
			const data = {};
			const fields = [ ...( module.fields || [] ), { name: 'status', type: 'select' } ];
			for ( const field of fields ) {
				const input = form.elements[ field.name ];
				if ( ! input ) continue;
				if ( field.type === 'checkbox' ) {
					data[ field.name ] = input.checked;
				} else if ( field.type === 'signature' ) {
					data[ field.name ] = input.value || null;
				} else if ( [ 'number', 'currency' ].includes( field.type ) ) {
					data[ field.name ] = input.value === '' ? null : Number( input.value );
				} else {
					data[ field.name ] = input.value;
				}
			}
			return data;
		},
	};
} )( window.EM );
