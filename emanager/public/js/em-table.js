/**
 * eManager — list page (sortable, filterable, searchable, paginated CRUD table).
 * Renders the module's list.html template (or the shared default).
 */
( function ( EM ) {
	'use strict';

	const PER_PAGE = 25;

	EM.table = {
		state: null,

		/**
		 * Render the list page for a module into the main container.
		 */
		async render( container, module ) {
			const caps = EM.app.boot.caps;
			container.innerHTML = await EM.tpl.forModule( module, 'list' );

			const refs = {
				title: container.querySelector( '[data-em="list-title"]' ),
				head: container.querySelector( '[data-em="list-head"]' ),
				body: container.querySelector( '[data-em="list-body"]' ),
				search: container.querySelector( '[data-em="list-search"]' ),
				status: container.querySelector( '[data-em="filter-status"]' ),
				pages: container.querySelector( '[data-em="list-pages"]' ),
				count: container.querySelector( '[data-em="list-count"]' ),
				newBtn: container.querySelector( '[data-em="btn-new"]' ),
				csvBtn: container.querySelector( '[data-em="btn-csv"]' ),
				pdfBtn: container.querySelector( '[data-em="btn-pdf"]' ),
				viewsMenu: container.querySelector( '[data-em="views-menu"]' ),
				bulkBar: container.querySelector( '[data-em="bulk-bar"]' ),
				bulkCount: container.querySelector( '[data-em="bulk-count"]' ),
				bulkDelete: container.querySelector( '[data-em="bulk-delete"]' ),
				bulkClear: container.querySelector( '[data-em="bulk-clear"]' ),
			};

			this.state = {
				module,
				refs,
				sort: 'created_at',
				order: 'desc',
				page: 1,
				search: '',
				status: '',
				records: [],
				total: 0,
				selected: new Set(),
			};

			if ( refs.title ) refs.title.textContent = module.name;

			// "New" button only for users who can create.
			if ( refs.newBtn ) {
				if ( caps.em_create ) {
					refs.newBtn.addEventListener( 'click', () => {
						location.hash = `#/${ module.section }/${ module.id }/form`;
					} );
				} else {
					refs.newBtn.classList.add( 'd-none' );
				}
			}

			// Status filter options from the module definition.
			if ( refs.status ) {
				( module.statuses || [] ).forEach( ( status ) => {
					const option = document.createElement( 'option' );
					option.value = status;
					option.textContent = status;
					refs.status.appendChild( option );
				} );
				refs.status.addEventListener( 'change', () => {
					this.state.status = refs.status.value;
					this.state.page = 1;
					this.fetch();
				} );
				if ( ! ( module.statuses || [] ).length ) refs.status.classList.add( 'd-none' );
			}

			// Debounced search.
			if ( refs.search ) {
				let timer;
				refs.search.addEventListener( 'input', () => {
					clearTimeout( timer );
					timer = setTimeout( () => {
						this.state.search = refs.search.value.trim();
						this.state.page = 1;
						this.fetch();
					}, 300 );
				} );
			}

			// Exports.
			if ( refs.csvBtn ) refs.csvBtn.addEventListener( 'click', () => this.exportCsv() );
			if ( refs.pdfBtn ) refs.pdfBtn.addEventListener( 'click', () => EM.pdf.fromList( this.state ) );

			// Bulk action bar.
			if ( refs.bulkDelete ) refs.bulkDelete.addEventListener( 'click', () => this.bulkDelete() );
			if ( refs.bulkClear ) {
				refs.bulkClear.addEventListener( 'click', () => {
					this.state.selected.clear();
					this.draw();
				} );
			}

			// Saved views.
			this.loadViews();

			this.buildHead();
			await this.fetch();
		},

		/** Load and render this user's saved views for the current module. */
		async loadViews() {
			const { module, refs } = this.state;
			if ( ! refs.viewsMenu ) return;
			let all = {};
			try {
				all = await EM.api.views();
			} catch ( e ) {
				all = {};
			}
			const list = ( all && all[ module.id ] ) || [];

			const items = list.map( ( v, i ) => `
				<li class="d-flex align-items-center">
					<button type="button" class="dropdown-item" data-view="${ i }">${ EM.tpl.esc( v.name ) }</button>
					<button type="button" class="btn btn-sm btn-link text-danger pe-3" data-view-del="${ i }" aria-label="Delete view">&times;</button>
				</li>` ).join( '' );

			refs.viewsMenu.innerHTML =
				( items || '<li><span class="dropdown-item-text text-secondary small">No saved views</span></li>' ) +
				'<li><hr class="dropdown-divider"></li>' +
				'<li><button type="button" class="dropdown-item" data-view-save><i class="bi bi-bookmark-plus me-1"></i>Save current view…</button></li>';

			refs.viewsMenu.querySelectorAll( '[data-view]' ).forEach( ( btn ) => {
				btn.addEventListener( 'click', () => this.applyView( list[ btn.dataset.view ].params ) );
			} );
			refs.viewsMenu.querySelectorAll( '[data-view-del]' ).forEach( ( btn ) => {
				btn.addEventListener( 'click', async ( e ) => {
					e.stopPropagation();
					await EM.api.saveView( { op: 'delete', module: module.id, name: list[ btn.dataset.viewDel ].name } );
					this.loadViews();
				} );
			} );
			const saveBtn = refs.viewsMenu.querySelector( '[data-view-save]' );
			if ( saveBtn ) saveBtn.addEventListener( 'click', () => this.saveCurrentView() );
		},

		/** Apply a saved view's params and refetch. */
		applyView( params ) {
			const { refs } = this.state;
			this.state.status = params.status || '';
			this.state.search = params.search || '';
			this.state.sort = params.sort || 'created_at';
			this.state.order = params.order || 'desc';
			this.state.page = 1;
			if ( refs.status ) refs.status.value = this.state.status;
			if ( refs.search ) refs.search.value = this.state.search;
			this.fetch();
		},

		/** Prompt for a name and save the current filter/sort as a view. */
		async saveCurrentView() {
			const name = ( window.prompt( 'Save this view as:' ) || '' ).trim();
			if ( ! name ) return;
			try {
				await EM.api.saveView( {
					op: 'save',
					module: this.state.module.id,
					name,
					params: {
						status: this.state.status,
						search: this.state.search,
						sort: this.state.sort,
						order: this.state.order,
					},
				} );
				EM.tpl.toast( 'View saved.' );
				this.loadViews();
			} catch ( error ) {
				EM.tpl.toast( error.message, 'danger' );
			}
		},

		/** Columns flagged "list": true in module.json (max 6 + status). */
		columns( module ) {
			const cols = ( module.fields || [] ).filter( ( f ) => f.list ).slice( 0, 6 );
			if ( ! cols.length ) {
				return ( module.fields || [] ).slice( 0, 4 );
			}
			return cols;
		},

		buildHead() {
			const { module, refs } = this.state;
			const canDelete = EM.app.boot.caps.em_delete || EM.app.boot.caps.em_create;
			const checkCol = canDelete ? '<th scope="col" style="width:1%"><input type="checkbox" class="form-check-input" data-em="select-all" aria-label="Select all"></th>' : '';
			const cells = this.columns( module )
				.map( ( f ) => `<th scope="col" data-sort="${ f.name }">${ EM.tpl.esc( f.label ) }</th>` )
				.join( '' );
			refs.head.innerHTML = `<tr>${ checkCol }${ cells }<th scope="col" data-sort="status">Status</th><th scope="col" data-sort="created_at">Created</th></tr>`;

			const selectAll = refs.head.querySelector( '[data-em="select-all"]' );
			if ( selectAll ) {
				selectAll.addEventListener( 'change', () => {
					this.state.records.forEach( ( r ) => {
						if ( selectAll.checked ) {
							this.state.selected.add( String( r.id ) );
						} else {
							this.state.selected.delete( String( r.id ) );
						}
					} );
					this.draw();
				} );
			}

			refs.head.querySelectorAll( 'th[data-sort]' ).forEach( ( th ) => {
				th.addEventListener( 'click', () => {
					const column = th.dataset.sort;
					if ( this.state.sort === column ) {
						this.state.order = this.state.order === 'asc' ? 'desc' : 'asc';
					} else {
						this.state.sort = column;
						this.state.order = 'asc';
					}
					this.fetch();
				} );
			} );
		},

		async fetch() {
			const { module, refs } = this.state;
			refs.body.innerHTML = `<tr><td colspan="9" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>`;

			try {
				const result = await EM.api.list( module.id, {
					sort: this.state.sort,
					order: this.state.order,
					page: this.state.page,
					per_page: PER_PAGE,
					search: this.state.search,
					status: this.state.status,
				} );
				this.state.records = result.records || [];
				this.state.total = result.total || this.state.records.length;
				this.draw();
			} catch ( error ) {
				refs.body.innerHTML = `<tr><td colspan="9"><div class="alert alert-danger mb-0">${ EM.tpl.esc( error.message ) }</div></td></tr>`;
			}
		},

		draw() {
			const { module, refs, records } = this.state;
			const cols = this.columns( module );

			// Sort indicators.
			refs.head.querySelectorAll( 'th[data-sort]' ).forEach( ( th ) => {
				th.classList.toggle( 'sorted-asc', th.dataset.sort === this.state.sort && this.state.order === 'asc' );
				th.classList.toggle( 'sorted-desc', th.dataset.sort === this.state.sort && this.state.order === 'desc' );
			} );

			const bulk = !! refs.head.querySelector( '[data-em="select-all"]' );

			if ( ! records.length ) {
				refs.body.innerHTML = `<tr><td colspan="12" class="text-center text-secondary py-4">No records found.</td></tr>`;
			} else {
				refs.body.innerHTML = records.map( ( record ) => {
					const id = String( record.id );
					const check = bulk
						? `<td><input type="checkbox" class="form-check-input" data-check="${ EM.tpl.esc( id ) }" ${ this.state.selected.has( id ) ? 'checked' : '' } aria-label="Select row"></td>`
						: '';
					const cells = cols.map( ( f ) => `<td>${ EM.tpl.format( f, record[ f.name ] ) }</td>` ).join( '' );
					const created = record.created_at ? new Date( record.created_at ).toLocaleDateString() : '—';
					return `<tr data-id="${ EM.tpl.esc( id ) }" tabindex="0">${ check }${ cells }<td>${ EM.tpl.statusBadge( record.status ) }</td><td>${ created }</td></tr>`;
				} ).join( '' );

				refs.body.querySelectorAll( 'tr[data-id]' ).forEach( ( row ) => {
					const open = () => { location.hash = `#/${ module.section }/${ module.id }/view/${ row.dataset.id }`; };
					row.addEventListener( 'click', ( e ) => {
						if ( e.target.closest( '[data-check]' ) ) return; // Checkbox handled separately.
						open();
					} );
					row.addEventListener( 'keydown', ( e ) => { if ( e.key === 'Enter' ) open(); } );
				} );
				refs.body.querySelectorAll( '[data-check]' ).forEach( ( cb ) => {
					cb.addEventListener( 'change', () => {
						if ( cb.checked ) {
							this.state.selected.add( cb.dataset.check );
						} else {
							this.state.selected.delete( cb.dataset.check );
						}
						this.updateBulkBar();
					} );
				} );
			}

			this.updateBulkBar();

			// Count + pagination.
			if ( refs.count ) refs.count.textContent = `${ this.state.total } record${ this.state.total === 1 ? '' : 's' }`;
			this.drawPager();
			EM.app.setRecordCount( this.state.total );
		},

		/** Show/hide the bulk action bar based on the current selection. */
		updateBulkBar() {
			const { refs } = this.state;
			if ( ! refs.bulkBar ) return;
			const n = this.state.selected.size;
			refs.bulkBar.classList.toggle( 'd-none', 0 === n );
			refs.bulkBar.classList.toggle( 'd-flex', n > 0 );
			if ( refs.bulkCount ) refs.bulkCount.textContent = `${ n } selected`;
			const selectAll = refs.head && refs.head.querySelector( '[data-em="select-all"]' );
			if ( selectAll ) {
				const ids = this.state.records.map( ( r ) => String( r.id ) );
				selectAll.checked = ids.length > 0 && ids.every( ( id ) => this.state.selected.has( id ) );
			}
		},

		/** Delete the selected records (server enforces per-record ownership). */
		async bulkDelete() {
			const { module } = this.state;
			const ids = Array.from( this.state.selected );
			if ( ! ids.length ) return;
			if ( ! window.confirm( `Delete ${ ids.length } record${ ids.length === 1 ? '' : 's' }? This cannot be undone.` ) ) return;

			let ok = 0;
			let failed = 0;
			for ( const id of ids ) {
				try {
					await EM.api.remove( module.id, id );
					ok++;
				} catch ( e ) {
					failed++;
				}
			}
			this.state.selected.clear();
			EM.tpl.toast( `Deleted ${ ok }${ failed ? `, ${ failed } skipped (not permitted)` : '' }.`, failed ? 'warning' : 'success' );
			this.fetch();
		},

		drawPager() {
			const { refs } = this.state;
			if ( ! refs.pages ) return;
			const pages = Math.max( 1, Math.ceil( this.state.total / PER_PAGE ) );
			refs.pages.innerHTML = '';
			if ( pages <= 1 ) return;

			const add = ( label, page, disabled, active ) => {
				const li = document.createElement( 'li' );
				li.className = `page-item${ disabled ? ' disabled' : '' }${ active ? ' active' : '' }`;
				li.innerHTML = `<button type="button" class="page-link">${ label }</button>`;
				if ( ! disabled && ! active ) {
					li.querySelector( 'button' ).addEventListener( 'click', () => {
						this.state.page = page;
						this.fetch();
					} );
				}
				refs.pages.appendChild( li );
			};

			add( '«', this.state.page - 1, this.state.page === 1, false );
			for ( let p = 1; p <= pages; p++ ) {
				if ( pages > 9 && Math.abs( p - this.state.page ) > 3 && p !== 1 && p !== pages ) continue;
				add( String( p ), p, false, p === this.state.page );
			}
			add( '»', this.state.page + 1, this.state.page === pages, false );
		},

		/** CSV export of the current result set (all visible columns). */
		exportCsv() {
			const { module, records } = this.state;
			const cols = [ ...this.columns( module ), { name: 'status', label: 'Status' }, { name: 'created_at', label: 'Created' }, { name: 'created_by_name', label: 'Created by' } ];
			const escape = ( v ) => `"${ String( v ?? '' ).replace( /"/g, '""' ) }"`;
			const lines = [
				cols.map( ( c ) => escape( c.label ) ).join( ',' ),
				...records.map( ( r ) => cols.map( ( c ) => escape( r[ c.name ] ) ).join( ',' ) ),
			];
			const blob = new Blob( [ '﻿' + lines.join( '\r\n' ) ], { type: 'text/csv;charset=utf-8' } );
			const a = document.createElement( 'a' );
			a.href = URL.createObjectURL( blob );
			a.download = `${ module.id }-${ new Date().toISOString().slice( 0, 10 ) }.csv`;
			a.click();
			URL.revokeObjectURL( a.href );
		},
	};
} )( window.EM );
