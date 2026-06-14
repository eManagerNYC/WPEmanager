/**
 * eManager module: Bid Submissions — leveling summary.
 *
 * Shows low / high / spread / count across submitted bids above the register
 * (the list is sortable by base bid), so the GC can level bids at a glance.
 * Counts only bids that have actually been submitted (not invited/declined).
 */
( function ( EM ) {
	'use strict';

	const money = ( n ) => Number( n || 0 ).toLocaleString( undefined, { style: 'currency', currency: 'USD', maximumFractionDigits: 0 } );
	const COUNTED = [ 'Submitted', 'Shortlisted', 'Awarded' ];

	async function list( container, module ) {
		container.innerHTML = '<div data-bl="kpis" class="row g-2 mb-3"></div><div data-bl="table"></div>';
		await EM.table.render( container.querySelector( '[data-bl="table"]' ), module );

		const host = container.querySelector( '[data-bl="kpis"]' );
		let rows = [];
		try {
			rows = ( await EM.api.list( module.id, { per_page: 500 } ) ).records || [];
		} catch ( e ) {
			host.remove();
			return;
		}

		const bids = rows
			.filter( ( r ) => COUNTED.includes( r.status ) && r.base_bid )
			.map( ( r ) => Number( r.base_bid ) );

		if ( ! bids.length ) {
			host.innerHTML = '<div class="col"><p class="text-secondary small mb-0">No submitted bids to level yet.</p></div>';
			return;
		}

		const low = Math.min.apply( null, bids );
		const high = Math.max.apply( null, bids );
		const spread = high - low;
		const spreadPct = low ? Math.round( ( spread / low ) * 100 ) : 0;

		const card = ( label, value, tone ) => `
			<div class="col-6 col-lg-3"><div class="card text-center h-100 border-${ tone }">
				<div class="card-body p-2"><div class="fs-5 fw-bold text-${ tone }">${ value }</div>
				<div class="small text-secondary">${ label }</div></div></div></div>`;

		host.innerHTML =
			card( 'Bids in', bids.length, 'secondary' ) +
			card( 'Low bid', money( low ), 'success' ) +
			card( 'High bid', money( high ), 'primary' ) +
			card( 'Spread', money( spread ) + ' (' + spreadPct + '%)', spreadPct > 25 ? 'danger' : 'info' );
	}

	EM.registerModule( 'bid-submissions', { list } );
} )( window.EM );
