/**
 * eManager module: Submittals — register with required-on-site aging.
 *
 * Surfaces how many submittals are still in review and how many are at risk of
 * missing their required-on-site date (lead-time pressure), above the standard
 * register. "In review" = not Closed and not a final Rejected disposition.
 */
( function ( EM ) {
	'use strict';

	const IN_REVIEW = [ 'Draft', 'Submitted', 'GC Review', 'A/E Review', 'Returned' ];

	async function list( container, module ) {
		container.innerHTML = '<div data-sub="kpis" class="row g-2 mb-3"></div><div data-sub="table"></div>';
		await EM.table.render( container.querySelector( '[data-sub="table"]' ), module );

		const kpis = container.querySelector( '[data-sub="kpis"]' );
		let rows = [];
		try {
			rows = ( await EM.api.list( module.id, { per_page: 500 } ) ).records || [];
		} catch ( e ) {
			kpis.remove();
			return;
		}

		const today = new Date();
		today.setHours( 0, 0, 0, 0 );
		const soon = new Date( today.getTime() + 14 * 86400000 ); // next 2 weeks
		let inReview = 0;
		let atRisk = 0;
		let approved = 0;

		rows.forEach( ( r ) => {
			const reviewing = IN_REVIEW.includes( r.status );
			if ( reviewing ) {
				inReview++;
				if ( r.required_on_site && new Date( r.required_on_site ) <= soon ) atRisk++;
			}
			if ( 'Closed' === r.status || /Approved/.test( r.direction || '' ) ) approved++;
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
			card( 'Total submittals', rows.length, 'secondary' ) +
			card( 'In review', inReview, 'primary' ) +
			card( 'Lead-time at risk', atRisk, atRisk ? 'danger' : 'success' ) +
			card( 'Approved / closed', approved, 'success' );
	}

	EM.registerModule( 'submittals', { list } );
} )( window.EM );
