/**
 * eManager module: Incidents — safety KPIs.
 *
 * Total / OSHA-recordable / lost-time / open-investigation counts above the
 * register, so the safety picture is immediate.
 */
( function ( EM ) {
	'use strict';

	const OPEN = [ 'Reported', 'Investigating', 'Corrective Action' ];

	async function list( container, module ) {
		container.innerHTML = '<div data-inc="kpis" class="row g-2 mb-3"></div><div data-inc="table"></div>';
		await EM.table.render( container.querySelector( '[data-inc="table"]' ), module );

		const host = container.querySelector( '[data-inc="kpis"]' );
		let rows = [];
		try {
			rows = ( await EM.api.list( module.id, { per_page: 500 } ) ).records || [];
		} catch ( e ) {
			host.remove();
			return;
		}

		let recordable = 0;
		let lostTime = 0;
		let open = 0;
		rows.forEach( ( r ) => {
			if ( r.osha_recordable ) recordable++;
			if ( 'Lost Time' === r.incident_type || Number( r.lost_days || 0 ) > 0 ) lostTime++;
			if ( OPEN.includes( r.status ) ) open++;
		} );

		const card = ( label, value, tone ) => `
			<div class="col-6 col-lg-3"><div class="card text-center h-100 border-${ tone }">
				<div class="card-body p-2"><div class="fs-4 fw-bold text-${ tone }">${ value }</div>
				<div class="small text-secondary">${ label }</div></div></div></div>`;

		host.innerHTML =
			card( 'Total incidents', rows.length, 'secondary' ) +
			card( 'OSHA recordable', recordable, recordable ? 'danger' : 'success' ) +
			card( 'Lost-time', lostTime, lostTime ? 'danger' : 'success' ) +
			card( 'Open investigations', open, open ? 'warning' : 'success' );
	}

	EM.registerModule( 'incidents', { list } );
} )( window.EM );
