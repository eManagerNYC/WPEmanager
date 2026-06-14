/**
 * eManager module: Photo Library — thumbnail gallery.
 *
 * Replaces the default table list with a responsive image grid (filterable by
 * album via the standard search), each tile linking to the record view. Falls
 * back gracefully if a photo URL fails to load.
 */
( function ( EM ) {
	'use strict';

	async function list( container, module ) {
		container.innerHTML = `
			<div class="card mb-3">
				<div class="card-header bg-body d-flex flex-wrap align-items-center gap-2">
					<h2 class="h5 mb-0 me-auto"><i class="bi bi-camera me-1" aria-hidden="true"></i> Photo Library</h2>
					<input type="search" class="form-control form-control-sm w-auto" data-gal="search" placeholder="Search album / title / tags…" aria-label="Search photos" />
					<button type="button" class="btn btn-sm btn-primary" data-gal="new"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add</button>
				</div>
				<div class="card-body"><div class="row g-3" data-gal="grid"></div></div>
			</div>`;

		const grid = container.querySelector( '[data-gal="grid"]' );
		const search = container.querySelector( '[data-gal="search"]' );
		if ( EM.app.boot.caps.em_create ) {
			container.querySelector( '[data-gal="new"]' ).addEventListener( 'click', () => {
				location.hash = `#/${ module.section }/${ module.id }/form`;
			} );
		} else {
			container.querySelector( '[data-gal="new"]' ).classList.add( 'd-none' );
		}

		async function load( term ) {
			grid.innerHTML = '<div class="col text-center py-4"><span class="spinner-border text-primary"></span></div>';
			let rows = [];
			try {
				rows = ( await EM.api.list( module.id, { per_page: 200, search: term || '', sort: 'taken_on', order: 'desc' } ) ).records || [];
			} catch ( e ) {
				grid.innerHTML = `<div class="col"><div class="alert alert-danger mb-0">${ EM.tpl.esc( e.message ) }</div></div>`;
				return;
			}
			if ( ! rows.length ) {
				grid.innerHTML = '<div class="col"><p class="text-secondary mb-0">No photos yet.</p></div>';
				return;
			}
			grid.innerHTML = rows.map( ( r ) => `
				<div class="col-6 col-md-4 col-lg-3">
					<a class="card h-100 text-decoration-none" href="#/${ module.section }/${ module.id }/view/${ r.id }">
						<div class="ratio ratio-4x3 bg-body-secondary rounded-top overflow-hidden">
							<img src="${ EM.tpl.esc( r.photo_url ) }" alt="${ EM.tpl.esc( r.title || 'Photo' ) }" loading="lazy"
								style="object-fit:cover;width:100%;height:100%" onerror="this.style.display='none'" />
						</div>
						<div class="card-body p-2">
							<div class="small fw-semibold text-truncate">${ EM.tpl.esc( r.title || '—' ) }</div>
							<div class="small text-secondary text-truncate">${ EM.tpl.esc( r.album || '' ) }${ r.taken_on ? ' · ' + new Date( r.taken_on ).toLocaleDateString() : '' }</div>
						</div>
					</a>
				</div>` ).join( '' );
		}

		let timer;
		search.addEventListener( 'input', () => {
			clearTimeout( timer );
			timer = setTimeout( () => load( search.value.trim() ), 300 );
		} );
		await load( '' );
	}

	EM.registerModule( 'photo-library', { list } );
} )( window.EM );
