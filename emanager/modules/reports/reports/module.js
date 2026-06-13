/**
 * eManager module: Reports — per-module record & status statistics
 * with Chart.js visualization and PDF / CSV export.
 */
( function ( EM ) {
	'use strict';

	let cache = null;

	async function render( container ) {
		container.innerHTML = `
			<div class="card mb-3">
				<div class="card-header bg-body d-flex flex-wrap align-items-center gap-2">
					<h2 class="h5 mb-0 me-auto"><i class="bi bi-graph-up me-1" aria-hidden="true"></i> Project Statistics</h2>
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-outline-secondary" data-rep="csv"><i class="bi bi-filetype-csv" aria-hidden="true"></i> CSV</button>
						<button type="button" class="btn btn-outline-secondary" data-rep="pdf"><i class="bi bi-filetype-pdf" aria-hidden="true"></i> PDF</button>
						<button type="button" class="btn btn-outline-secondary" data-rep="refresh"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i></button>
					</div>
				</div>
				<div class="card-body">
					<div class="row g-3 mb-3" data-rep="cards"></div>
					<div class="row g-3">
						<div class="col-lg-7"><canvas data-rep="chart" height="220" aria-label="Records per module"></canvas></div>
						<div class="col-lg-5"><div class="table-responsive" data-rep="table"></div></div>
					</div>
				</div>
			</div>`;

		const refs = {
			cards: container.querySelector( '[data-rep="cards"]' ),
			chart: container.querySelector( '[data-rep="chart"]' ),
			table: container.querySelector( '[data-rep="table"]' ),
		};

		container.querySelector( '[data-rep="refresh"]' ).addEventListener( 'click', () => { cache = null; render( container ); } );
		container.querySelector( '[data-rep="csv"]' ).addEventListener( 'click', exportCsv );
		container.querySelector( '[data-rep="pdf"]' ).addEventListener( 'click', exportPdf );

		refs.cards.innerHTML = `<div class="col text-center py-4"><span class="spinner-border text-primary"></span></div>`;

		try {
			cache = cache || await EM.api.stats();
		} catch ( error ) {
			refs.cards.innerHTML = `<div class="col"><div class="alert alert-danger">${ EM.tpl.esc( error.message ) }</div></div>`;
			return;
		}

		const stats = cache.filter( ( s ) => s.id !== 'reports' );
		const total = stats.reduce( ( sum, s ) => sum + s.counts.total, 0 );
		const busiest = [ ...stats ].sort( ( a, b ) => b.counts.total - a.counts.total )[ 0 ];

		refs.cards.innerHTML = [
			[ 'Total records', total, 'bi-database' ],
			[ 'Modules', stats.length, 'bi-grid' ],
			[ 'Most active module', busiest && busiest.counts.total ? busiest.name : '—', 'bi-fire' ],
		].map( ( [ label, value, icon ] ) => `
			<div class="col-sm-4">
				<div class="card text-center h-100">
					<div class="card-body">
						<i class="bi ${ icon } fs-3 text-primary" aria-hidden="true"></i>
						<div class="fs-4 fw-bold">${ EM.tpl.esc( value ) }</div>
						<div class="text-secondary small">${ label }</div>
					</div>
				</div>
			</div>` ).join( '' );

		// Bar chart: records per module (top 15).
		const top = [ ...stats ].sort( ( a, b ) => b.counts.total - a.counts.total ).slice( 0, 15 );
		new Chart( refs.chart, {
			type: 'bar',
			data: {
				labels: top.map( ( s ) => s.name ),
				datasets: [ { label: 'Records', data: top.map( ( s ) => s.counts.total ), backgroundColor: '#0d6efd' } ],
			},
			options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { precision: 0 } } } },
		} );

		// Status breakdown table.
		refs.table.innerHTML = `
			<table class="table table-sm align-middle">
				<thead class="table-light"><tr><th>Module</th><th class="text-end">Records</th><th>Status breakdown</th></tr></thead>
				<tbody>
					${ stats.map( ( s ) => `
						<tr>
							<td>${ EM.tpl.esc( s.name ) }</td>
							<td class="text-end">${ s.counts.total }</td>
							<td>${ Object.entries( s.counts.by_status )
								.map( ( [ status, count ] ) => `${ EM.tpl.statusBadge( status ) } <small>${ count }</small>` )
								.join( ' ' ) }</td>
						</tr>` ).join( '' ) }
				</tbody>
			</table>`;
	}

	function rows() {
		return ( cache || [] ).map( ( s ) => [
			s.name,
			s.section,
			s.counts.total,
			Object.entries( s.counts.by_status ).map( ( [ k, v ] ) => `${ k }: ${ v }` ).join( '; ' ),
		] );
	}

	function exportCsv() {
		const escape = ( v ) => `"${ String( v ?? '' ).replace( /"/g, '""' ) }"`;
		const lines = [ [ 'Module', 'Section', 'Records', 'Statuses' ], ...rows() ].map( ( r ) => r.map( escape ).join( ',' ) );
		const blob = new Blob( [ '﻿' + lines.join( '\r\n' ) ], { type: 'text/csv;charset=utf-8' } );
		const a = document.createElement( 'a' );
		a.href = URL.createObjectURL( blob );
		a.download = 'emanager-statistics.csv';
		a.click();
		URL.revokeObjectURL( a.href );
	}

	function exportPdf() {
		EM.pdf.fromRows( 'Project Statistics', [ 'Module', 'Section', 'Records', 'Statuses' ], rows(), 'emanager-statistics.pdf' );
	}

	EM.registerModule( 'reports', { list: render, view: render, form: render } );
} )( window.EM );
