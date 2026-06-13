/**
 * eManager module: Cost Summary — project financial roll-up.
 *
 * Mirrors Procore's Budget view: original budget, approved changes, revised
 * budget, committed cost, actual cost to date, forecast at completion and the
 * projected over/under, with a budget-vs-committed-vs-actual-vs-forecast chart.
 * Data comes from /reports/cost-summary (one indexed SUM per cost module).
 */
( function ( EM ) {
	'use strict';

	const money = ( n ) => Number( n || 0 ).toLocaleString( undefined, { style: 'currency', currency: 'USD', maximumFractionDigits: 0 } );

	async function render( container ) {
		container.innerHTML = `
			<div class="card mb-3">
				<div class="card-header bg-body d-flex flex-wrap align-items-center gap-2">
					<h2 class="h5 mb-0 me-auto"><i class="bi bi-cash-coin me-1" aria-hidden="true"></i> Cost Summary</h2>
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-outline-secondary" data-cs="pdf"><i class="bi bi-filetype-pdf" aria-hidden="true"></i> PDF</button>
						<button type="button" class="btn btn-outline-secondary" data-cs="refresh"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i></button>
					</div>
				</div>
				<div class="card-body">
					<div class="row g-3 mb-3" data-cs="cards"></div>
					<div class="row g-3">
						<div class="col-lg-7"><canvas data-cs="chart" height="220" aria-label="Budget vs committed vs actual vs forecast"></canvas></div>
						<div class="col-lg-5"><div class="table-responsive" data-cs="table"></div></div>
					</div>
					<p class="small text-secondary mt-3 mb-0">Figures roll up from the Budget &amp; Forecast, Change Orders, Commitments, Direct Costs, Subcontractor Invoices, T&amp;M Tickets and Owner Invoices modules.</p>
				</div>
			</div>`;

		const refs = {
			cards: container.querySelector( '[data-cs="cards"]' ),
			chart: container.querySelector( '[data-cs="chart"]' ),
			table: container.querySelector( '[data-cs="table"]' ),
		};

		container.querySelector( '[data-cs="refresh"]' ).addEventListener( 'click', () => render( container ) );

		refs.cards.innerHTML = `<div class="col text-center py-4"><span class="spinner-border text-primary"></span></div>`;

		let data;
		try {
			data = await EM.api.costSummary();
		} catch ( error ) {
			refs.cards.innerHTML = `<div class="col"><div class="alert alert-danger">${ EM.tpl.esc( error.message ) }</div></div>`;
			return;
		}

		const fig = {};
		data.figures.forEach( ( f ) => { fig[ f.key ] = f; } );

		// Headline KPI cards.
		const cards = [
			[ 'revised_budget', 'primary' ],
			[ 'committed', 'info' ],
			[ 'actual', 'secondary' ],
			[ 'forecast', 'warning' ],
			[ 'variance', ( fig.variance && fig.variance.value > 0 ) ? 'danger' : 'success' ],
		];
		refs.cards.innerHTML = cards.map( ( [ key, tone ] ) => {
			const f = fig[ key ];
			if ( ! f ) return '';
			return `<div class="col-6 col-lg">
				<div class="card text-center h-100 border-${ tone }">
					<div class="card-body p-2">
						<div class="fs-5 fw-bold text-${ tone }">${ money( f.value ) }</div>
						<div class="text-secondary small">${ EM.tpl.esc( f.label ) }</div>
					</div>
				</div>
			</div>`;
		} ).join( '' );

		// Comparison chart.
		new Chart( refs.chart, {
			type: 'bar',
			data: {
				labels: [ 'Revised budget', 'Committed', 'Actual', 'Forecast' ],
				datasets: [ {
					label: 'USD',
					data: [ fig.revised_budget, fig.committed, fig.actual, fig.forecast ].map( ( f ) => ( f ? f.value : 0 ) ),
					backgroundColor: [ '#0d6efd', '#0dcaf0', '#6c757d', '#ffc107' ],
				} ],
			},
			options: {
				plugins: { legend: { display: false } },
				scales: { y: { ticks: { callback: ( v ) => '$' + Number( v ).toLocaleString() } } },
			},
		} );

		// Full figures table.
		refs.table.innerHTML = `
			<table class="table table-sm align-middle">
				<tbody>
					${ data.figures.map( ( f ) => `<tr>
						<td>${ EM.tpl.esc( f.label ) }</td>
						<td class="text-end fw-${ f.key === 'variance' ? 'bold' : 'normal' }">${ money( f.value ) }</td>
					</tr>` ).join( '' ) }
				</tbody>
			</table>`;

		container.querySelector( '[data-cs="pdf"]' ).addEventListener( 'click', () => {
			EM.pdf.fromRows(
				'Cost Summary',
				[ 'Figure', 'Amount (USD)' ],
				data.figures.map( ( f ) => [ f.label, money( f.value ) ] ),
				'cost-summary.pdf'
			);
		} );
	}

	EM.registerModule( 'cost-summary', { list: render, view: render, form: render } );
} )( window.EM );
