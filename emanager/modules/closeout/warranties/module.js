/**
 * eManager module: Warranties — expiry KPIs.
 *
 * Active / expiring-soon (≤90 days) / expired counts above the register, so the
 * owner can act before coverage lapses during the warranty period.
 */
( function ( EM ) {
	'use strict';

	async function list( container, module ) {
		container.innerHTML = '<div data-w="kpis" class="row g-2 mb-3"></div><div data-w="table"></div>';
		await EM.table.render( container.querySelector( '[data-w="table"]' ), module );

		const host = container.querySelector( '[data-w="kpis"]' );
		let rows = [];
		try {
			rows = ( await EM.api.list( module.id, { per_page: 500 } ) ).records || [];
		} catch ( e ) {
			host.remove();
			return;
		}

		const today = new Date();
		today.setHours( 0, 0, 0, 0 );
		const soon = new Date( today.getTime() + 90 * 86400000 );
		let active = 0;
		let expiring = 0;
		let expired = 0;
		rows.forEach( ( r ) => {
			const end = r.end_date ? new Date( r.end_date ) : null;
			if ( 'Expired' === r.status || ( end && end < today ) ) {
				expired++;
			} else if ( 'Active' === r.status ) {
				active++;
				if ( end && end <= soon ) expiring++;
			}
		} );

		const card = ( label, value, tone ) => `
			<div class="col-6 col-lg-3"><div class="card text-center h-100 border-${ tone }">
				<div class="card-body p-2"><div class="fs-4 fw-bold text-${ tone }">${ value }</div>
				<div class="small text-secondary">${ label }</div></div></div></div>`;

		host.innerHTML =
			card( 'Total', rows.length, 'secondary' ) +
			card( 'Active', active, 'success' ) +
			card( 'Expiring ≤90 days', expiring, expiring ? 'warning' : 'success' ) +
			card( 'Expired', expired, expired ? 'danger' : 'secondary' );
	}

	EM.registerModule( 'warranties', { list } );
} )( window.EM );
