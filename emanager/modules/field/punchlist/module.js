/**
 * eManager module: Punch List — ball-in-court KPIs.
 *
 * Open / work-required / ready-for-review / overdue counts above the register,
 * so closeout pressure is visible at a glance. Overdue = not Verified and past
 * the due date.
 */
( function ( EM ) {
	'use strict';

	const OPEN = [ 'Open', 'Work Required', 'Ready for Review' ];

	async function list( container, module ) {
		container.innerHTML = '<div data-pl="kpis" class="row g-2 mb-3"></div><div data-pl="table"></div>';
		await EM.table.render( container.querySelector( '[data-pl="table"]' ), module );

		const host = container.querySelector( '[data-pl="kpis"]' );
		let rows = [];
		try {
			rows = ( await EM.api.list( module.id, { per_page: 500 } ) ).records || [];
		} catch ( e ) {
			host.remove();
			return;
		}

		const today = new Date();
		today.setHours( 0, 0, 0, 0 );
		let open = 0;
		let ready = 0;
		let overdue = 0;
		rows.forEach( ( r ) => {
			if ( OPEN.includes( r.status ) ) {
				open++;
				if ( 'Ready for Review' === r.status ) ready++;
				if ( r.due_date && new Date( r.due_date ) < today ) overdue++;
			}
		} );

		const card = ( label, value, tone ) => `
			<div class="col-6 col-lg-3"><div class="card text-center h-100 border-${ tone }">
				<div class="card-body p-2"><div class="fs-4 fw-bold text-${ tone }">${ value }</div>
				<div class="small text-secondary">${ label }</div></div></div></div>`;

		host.innerHTML =
			card( 'Total items', rows.length, 'secondary' ) +
			card( 'Open', open, 'primary' ) +
			card( 'Ready for review', ready, 'info' ) +
			card( 'Overdue', overdue, overdue ? 'danger' : 'success' );
	}

	EM.registerModule( 'punchlist', { list } );
} )( window.EM );
