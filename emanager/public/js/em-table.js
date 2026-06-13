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

			this.buildHead();
			await this.fetch();
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
			const cells = this.columns( module )
				.map( ( f ) => `<th scope="col" data-sort="${ f.name }">${ EM.tpl.esc( f.label ) }</th>` )
				.join( '' );
			refs.head.innerHTML = `<tr>${ cells }<th scope="col" data-sort="status">Status</th><th scope="col" data-sort="created_at">Created</th></tr>`;

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

			if ( ! records.length ) {
				refs.body.innerHTML = `<tr><td colspan="9" class="text-center text-secondary py-4">No records found.</td></tr>`;
			} else {
				refs.body.innerHTML = records.map( ( record ) => {
					const cells = cols.map( ( f ) => `<td>${ EM.tpl.format( f, record[ f.name ] ) }</td>` ).join( '' );
					const created = record.created_at ? new Date( record.created_at ).toLocaleDateString() : '—';
					return `<tr data-id="${ EM.tpl.esc( record.id ) }" tabindex="0">${ cells }<td>${ EM.tpl.statusBadge( record.status ) }</td><td>${ created }</td></tr>`;
				} ).join( '' );

				refs.body.querySelectorAll( 'tr[data-id]' ).forEach( ( row ) => {
					const open = () => { location.hash = `#/${ module.section }/${ module.id }/view/${ row.dataset.id }`; };
					row.addEventListener( 'click', open );
					row.addEventListener( 'keydown', ( e ) => { if ( e.key === 'Enter' ) open(); } );
				} );
			}

			// Count + pagination.
			if ( refs.count ) refs.count.textContent = `${ this.state.total } record${ this.state.total === 1 ? '' : 's' }`;
			this.drawPager();
			EM.app.setRecordCount( this.state.total );
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
