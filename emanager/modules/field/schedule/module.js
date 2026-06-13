/**
 * eManager module: Schedule — Gantt chart.
 *
 * Renders the activity list as a Gantt (floating horizontal bars from planned
 * start to finish, shaded by % complete) above the standard sortable table.
 * A lightweight, dependency-free visual scheduler built on the already-loaded
 * Chart.js; complements the Linear/Takt Line-of-Balance view.
 */
( function ( EM ) {
	'use strict';

	async function list( container, module ) {
		container.innerHTML = `
			<div class="card mb-3">
				<div class="card-header bg-body"><h2 class="h6 mb-0"><i class="bi bi-bar-chart-steps me-1" aria-hidden="true"></i> Gantt</h2></div>
				<div class="card-body"><canvas data-gantt="chart" aria-label="Gantt schedule chart"></canvas>
					<p class="small text-secondary mt-2 mb-0">Bars run from planned start to finish; the darker fill is % complete.</p>
				</div>
			</div>
			<div data-gantt="table"></div>`;

		await EM.table.render( container.querySelector( '[data-gantt="table"]' ), module );

		let rows = [];
		try {
			const res = await EM.api.list( module.id, { per_page: 500, sort: 'start_date', order: 'asc' } );
			rows = ( res.records || [] ).filter( ( r ) => r.start_date && r.finish_date );
		} catch ( e ) {
			rows = [];
		}

		const canvas = container.querySelector( '[data-gantt="chart"]' );
		if ( ! rows.length ) {
			canvas.parentElement.innerHTML = '<p class="text-secondary small mb-0">Add activities with a start and finish date to draw the Gantt.</p>';
			return;
		}

		const dates = [];
		rows.forEach( ( r ) => {
			dates.push( new Date( r.start_date ), new Date( r.finish_date ) );
		} );
		const minDate = new Date( Math.min.apply( null, dates ) );
		const day = ( d ) => Math.round( ( new Date( d ) - minDate ) / 86400000 );
		const dateForDay = ( n ) => new Date( minDate.getTime() + n * 86400000 ).toLocaleDateString();

		const labels = rows.map( ( r ) => r.activity || r.activity_id || '—' );
		const spans = rows.map( ( r ) => [ day( r.start_date ), Math.max( day( r.finish_date ), day( r.start_date ) + 1 ) ] );
		const progress = rows.map( ( r ) => {
			const s = day( r.start_date );
			const e = Math.max( day( r.finish_date ), s + 1 );
			const pct = Math.min( 100, Math.max( 0, Number( r.percent_complete || 0 ) ) ) / 100;
			return [ s, s + ( e - s ) * pct ];
		} );

		// Size the canvas to the number of activities.
		canvas.height = Math.max( 160, rows.length * 28 + 40 );

		new Chart( canvas, {
			type: 'bar',
			data: {
				labels,
				datasets: [
					{ label: 'Planned', data: spans, backgroundColor: 'rgba(13,110,253,.25)', borderColor: '#0d6efd', borderWidth: 1, borderSkipped: false, borderRadius: 3 },
					{ label: 'Complete', data: progress, backgroundColor: '#0d6efd', borderRadius: 3, barPercentage: 0.5 },
				],
			},
			options: {
				indexAxis: 'y',
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: ( ctx ) => {
								const r = rows[ ctx.dataIndex ];
								if ( ctx.datasetIndex === 1 ) return ( r.percent_complete || 0 ) + '% complete';
								return `${ dateForDay( ctx.parsed._custom ? ctx.parsed._custom.min : ctx.raw[ 0 ] ) } → ${ dateForDay( ctx.raw[ 1 ] ) }`;
							},
						},
					},
				},
				scales: {
					x: { stacked: false, title: { display: true, text: 'Time' }, ticks: { callback: ( v ) => dateForDay( v ) } },
					y: { stacked: true, ticks: { autoSkip: false, font: { size: 10 } } },
				},
			},
		} );
	}

	EM.registerModule( 'schedule', { list } );
} )( window.EM );
