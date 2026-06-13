/**
 * eManager module: Linear / Takt Schedule — Line-of-Balance chart.
 *
 * Renders the Empire State Building-style location-based schedule: a
 * Line-of-Balance chart plotting location (Y, floor/zone order) against time
 * (X). Each activity is drawn as a sloped band from its planned-start line to
 * its planned-finish line across locations — the slope is the production rate,
 * and parallel non-crossing lines mean trades flow without interference. The
 * standard sortable/filterable table is shown beneath.
 */
( function ( EM ) {
	'use strict';

	const PALETTE = [ '#0d6efd', '#198754', '#dc3545', '#fd7e14', '#6f42c1', '#0dcaf0', '#d63384', '#20c997', '#ffc107', '#6610f2' ];

	async function list( container, module ) {
		container.innerHTML = `
			<div class="card mb-3">
				<div class="card-header bg-body"><h2 class="h6 mb-0"><i class="bi bi-graph-up me-1" aria-hidden="true"></i> Line of Balance — location vs. time</h2></div>
				<div class="card-body"><canvas data-lob="chart" height="260" aria-label="Line of Balance schedule chart"></canvas>
					<p class="small text-secondary mt-2 mb-0">Each line is an activity rising through locations over time; the slope is its production rate. Set each row's <em>Location order</em> to position it on the vertical axis.</p>
				</div>
			</div>
			<div data-lob="table"></div>`;

		// Standard CRUD table underneath.
		await EM.table.render( container.querySelector( '[data-lob="table"]' ), module );

		// Pull all rows for the chart (planned dates + location order).
		let rows = [];
		try {
			const res = await EM.api.list( module.id, { per_page: 500, sort: 'location_index', order: 'asc' } );
			rows = ( res.records || [] ).filter( ( r ) => r.planned_start && r.location_index !== null && r.location_index !== '' );
		} catch ( e ) {
			rows = [];
		}

		const canvas = container.querySelector( '[data-lob="chart"]' );
		if ( ! rows.length ) {
			canvas.parentElement.innerHTML = '<p class="text-secondary small mb-0">Add activities with a planned start/finish and a location order to plot the Line of Balance.</p>';
			return;
		}

		// X axis = whole days from the earliest planned start.
		const allDates = [];
		rows.forEach( ( r ) => {
			allDates.push( new Date( r.planned_start ) );
			if ( r.planned_finish ) allDates.push( new Date( r.planned_finish ) );
		} );
		const minDate = new Date( Math.min.apply( null, allDates ) );
		const day = ( d ) => Math.round( ( new Date( d ) - minDate ) / 86400000 );
		const dateForDay = ( n ) => {
			const d = new Date( minDate.getTime() + n * 86400000 );
			return d.toLocaleDateString();
		};

		// Group rows by activity; each activity becomes two connected lines
		// (start edge and finish edge) across its locations.
		const groups = {};
		rows.forEach( ( r ) => {
			( groups[ r.activity ] = groups[ r.activity ] || [] ).push( r );
		} );

		const datasets = [];
		Object.keys( groups ).forEach( ( activity, i ) => {
			const color = PALETTE[ i % PALETTE.length ];
			const recs = groups[ activity ].slice().sort( ( a, b ) => a.location_index - b.location_index );
			datasets.push( {
				label: activity + ' (start)',
				data: recs.map( ( r ) => ( { x: day( r.planned_start ), y: Number( r.location_index ) } ) ),
				borderColor: color,
				backgroundColor: color,
				showLine: true,
				tension: 0,
			} );
			if ( recs.some( ( r ) => r.planned_finish ) ) {
				datasets.push( {
					label: activity + ' (finish)',
					data: recs.map( ( r ) => ( { x: day( r.planned_finish || r.planned_start ), y: Number( r.location_index ) } ) ),
					borderColor: color,
					backgroundColor: color,
					borderDash: [ 5, 4 ],
					showLine: true,
					tension: 0,
					pointStyle: 'rectRot',
				} );
			}
		} );

		new Chart( canvas, {
			type: 'scatter',
			data: { datasets },
			options: {
				plugins: {
					legend: { labels: { filter: ( item ) => item.text.endsWith( '(start)' ), boxWidth: 12 } },
					tooltip: { callbacks: { label: ( ctx ) => `${ ctx.dataset.label }: loc ${ ctx.parsed.y }, ${ dateForDay( ctx.parsed.x ) }` } },
				},
				scales: {
					x: { title: { display: true, text: 'Time' }, ticks: { callback: ( v ) => dateForDay( v ) } },
					y: { title: { display: true, text: 'Location (floor / zone order)' }, ticks: { precision: 0 } },
				},
			},
		} );
	}

	EM.registerModule( 'linear-schedule', { list } );
} )( window.EM );
