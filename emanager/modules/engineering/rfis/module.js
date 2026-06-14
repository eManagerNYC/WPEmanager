/**
 * eManager module: RFIs — log with aging KPIs.
 *
 * Adds an open / overdue / average-age strip above the standard register so the
 * team can see ball-in-court pressure at a glance. "Overdue" = not Closed/Void
 * and past the response-required date.
 */
( function ( EM ) {
	'use strict';

	const OPEN_STATES = [ 'Draft', 'Open', 'Answered' ];

	async function list( container, module ) {
		container.innerHTML = '<div data-rfi="kpis" class="row g-2 mb-3"></div><div data-rfi="table"></div>';
		await EM.table.render( container.querySelector( '[data-rfi="table"]' ), module );

		const kpis = container.querySelector( '[data-rfi="kpis"]' );
		let rows = [];
		try {
			rows = ( await EM.api.list( module.id, { per_page: 500 } ) ).records || [];
		} catch ( e ) {
			kpis.remove();
			return;
		}

		const today = new Date();
		today.setHours( 0, 0, 0, 0 );
		let open = 0;
		let overdue = 0;
		let ageSum = 0;
		let ageCount = 0;

		rows.forEach( ( r ) => {
			const isOpen = OPEN_STATES.includes( r.status );
			if ( isOpen ) {
				open++;
				if ( r.date_required && new Date( r.date_required ) < today ) overdue++;
			}
			if ( r.date_submitted ) {
				ageSum += Math.max( 0, Math.round( ( today - new Date( r.date_submitted ) ) / 86400000 ) );
				ageCount++;
			}
		} );

		const card = ( label, value, tone ) => `
			<div class="col-6 col-lg-3">
				<div class="card text-center h-100 border-${ tone }">
					<div class="card-body p-2">
						<div class="fs-4 fw-bold text-${ tone }">${ value }</div>
						<div class="small text-secondary">${ label }</div>
					</div>
				</div>
			</div>`;

		kpis.innerHTML =
			card( 'Total RFIs', rows.length, 'secondary' ) +
			card( 'Open', open, 'primary' ) +
			card( 'Overdue', overdue, overdue ? 'danger' : 'success' ) +
			card( 'Avg age (days)', ageCount ? Math.round( ageSum / ageCount ) : 0, 'info' );
	}

	EM.registerModule( 'rfis', { list } );
} )( window.EM );
