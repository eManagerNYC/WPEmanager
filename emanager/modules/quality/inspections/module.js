/**
 * eManager module: Inspections — pass-rate KPIs.
 *
 * Scheduled / passed / failed / overdue counts above the register so QC status
 * is visible at a glance. Overdue = still scheduled and past the scheduled date.
 */
( function ( EM ) {
	'use strict';

	async function list( container, module ) {
		container.innerHTML = '<div data-i="kpis" class="row g-2 mb-3"></div><div data-i="table"></div>';
		await EM.table.render( container.querySelector( '[data-i="table"]' ), module );

		const host = container.querySelector( '[data-i="kpis"]' );
		let rows = [];
		try {
			rows = ( await EM.api.list( module.id, { per_page: 500 } ) ).records || [];
		} catch ( e ) {
			host.remove();
			return;
		}

		const today = new Date();
		today.setHours( 0, 0, 0, 0 );
		let scheduled = 0;
		let passed = 0;
		let failed = 0;
		let overdue = 0;
		rows.forEach( ( r ) => {
			if ( 'Passed' === r.status || 'Closed' === r.status ) passed++;
			if ( 'Failed' === r.status || 'Reinspection' === r.status ) failed++;
			if ( [ 'Scheduled', 'In Progress', 'Reinspection' ].includes( r.status ) ) {
				scheduled++;
				if ( r.scheduled_date && new Date( r.scheduled_date ) < today ) overdue++;
			}
		} );
		const total = passed + failed || 1;

		const card = ( label, value, tone ) => `
			<div class="col-6 col-lg-3"><div class="card text-center h-100 border-${ tone }">
				<div class="card-body p-2"><div class="fs-4 fw-bold text-${ tone }">${ value }</div>
				<div class="small text-secondary">${ label }</div></div></div></div>`;

		host.innerHTML =
			card( 'Open / scheduled', scheduled, 'primary' ) +
			card( 'Overdue', overdue, overdue ? 'danger' : 'success' ) +
			card( 'Passed', passed, 'success' ) +
			card( 'Pass rate', Math.round( ( passed / total ) * 100 ) + '%', 'info' );
	}

	EM.registerModule( 'inspections', { list } );
} )( window.EM );
