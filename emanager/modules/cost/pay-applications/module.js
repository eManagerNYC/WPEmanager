/**
 * eManager module: Pay Applications (AIA G702).
 *
 * Rolls the Schedule of Values (G703) lines up into a G702 Application and
 * Certificate for Payment. The standard AIA math is applied live:
 *
 *   Line 1  Original contract sum         = Contract sum to date − net change orders
 *   Line 2  Net change by change orders   = approved change orders (from Cost Summary)
 *   Line 3  Contract sum to date          = Σ scheduled value (Col C)
 *   Line 4  Total completed & stored      = Σ Col G (D+E+F)
 *   Line 5  Retainage                     = a% of completed work + b% of stored material
 *   Line 6  Total earned less retainage   = 4 − 5
 *   Line 7  Less previous certificates    = (entered)
 *   Line 8  Current payment due           = 6 − 7
 *   Line 9  Balance to finish incl. ret.  = 3 − 6
 *
 * Exports a formatted G702 cover + G703 continuation sheet to PDF.
 */
( function ( EM ) {
	'use strict';

	const money = ( n ) => Number( n || 0 ).toLocaleString( undefined, { style: 'currency', currency: 'USD' } );
	const numOf = ( r, k ) => ( r[ k ] === null || r[ k ] === undefined || r[ k ] === '' ? 0 : Number( r[ k ] ) );

	async function render( container ) {
		container.innerHTML = `
			<div class="card mb-3">
				<div class="card-header bg-body d-flex flex-wrap align-items-center gap-2">
					<h2 class="h5 mb-0 me-auto"><i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i> Application &amp; Certificate for Payment (G702)</h2>
					<button type="button" class="btn btn-sm btn-outline-secondary" data-pa="refresh"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i></button>
					<button type="button" class="btn btn-sm btn-primary" data-pa="pdf"><i class="bi bi-filetype-pdf" aria-hidden="true"></i> Export G702 + G703</button>
				</div>
				<div class="card-body" data-pa="body">
					<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
				</div>
			</div>`;

		const body = container.querySelector( '[data-pa="body"]' );
		container.querySelector( '[data-pa="refresh"]' ).addEventListener( 'click', () => render( container ) );

		let lines = [];
		let netChangeOrders = 0;
		try {
			const res = await EM.api.list( 'schedule-of-values', { per_page: 500, sort: 'item_no', order: 'asc' } );
			lines = res.records || [];
			try {
				const cs = await EM.api.costSummary();
				const co = ( cs.figures || [] ).find( ( x ) => x.key === 'approved_changes' );
				netChangeOrders = co ? Number( co.value ) : 0;
			} catch ( e ) { netChangeOrders = 0; }
		} catch ( error ) {
			body.innerHTML = `<div class="alert alert-danger">${ EM.tpl.esc( error.message ) }</div>`;
			return;
		}

		if ( ! lines.length ) {
			body.innerHTML = `<div class="alert alert-info mb-0">No Schedule of Values lines yet. Add lines in
				<a href="#/cost/schedule-of-values">Schedule of Values (G703)</a> first.</div>`;
			return;
		}

		const project = EM.app.boot.project || {};
		const today = new Date().toISOString().slice( 0, 10 );

		body.innerHTML = `
			<div class="row g-2 mb-3">
				<div class="col-sm-3"><label class="form-label small">Application #</label><input class="form-control form-control-sm" data-pa="app_no" value="1" /></div>
				<div class="col-sm-3"><label class="form-label small">Period to</label><input type="date" class="form-control form-control-sm" data-pa="period" value="${ today }" /></div>
				<div class="col-sm-2"><label class="form-label small">Retainage % (work)</label><input type="number" step="0.1" class="form-control form-control-sm" data-pa="ret_work" value="10" /></div>
				<div class="col-sm-2"><label class="form-label small">Retainage % (stored)</label><input type="number" step="0.1" class="form-control form-control-sm" data-pa="ret_stored" value="10" /></div>
				<div class="col-sm-2"><label class="form-label small">Less previous certs</label><input type="number" step="0.01" class="form-control form-control-sm" data-pa="previous" value="0" /></div>
			</div>

			<div class="table-responsive mb-3">
				<table class="table table-sm table-bordered align-middle small mb-0">
					<thead class="table-light">
						<tr>
							<th>A</th><th>B — Description</th><th class="text-end">C — Sched. value</th>
							<th class="text-end">D — Previous</th><th class="text-end">E — This period</th>
							<th class="text-end">F — Stored</th><th class="text-end">G — Completed+stored</th>
							<th class="text-end">%</th><th class="text-end">H — Balance</th><th class="text-end">I — Retainage</th>
						</tr>
					</thead>
					<tbody data-pa="rows"></tbody>
					<tfoot class="table-light fw-semibold"><tr data-pa="totals"></tr></tfoot>
				</table>
			</div>

			<div class="row justify-content-end">
				<div class="col-lg-6">
					<table class="table table-sm align-middle"><tbody data-pa="g702"></tbody></table>
				</div>
			</div>`;

		const inputs = [ 'ret_work', 'ret_stored', 'previous' ].map( ( k ) => body.querySelector( `[data-pa="${ k }"]` ) );

		const state = {};

		function compute() {
			const retWorkPct = Number( body.querySelector( '[data-pa="ret_work"]' ).value || 0 ) / 100;
			const retStoredPct = Number( body.querySelector( '[data-pa="ret_stored"]' ).value || 0 ) / 100;
			const previousCerts = Number( body.querySelector( '[data-pa="previous"]' ).value || 0 );

			let C = 0, D = 0, E = 0, F = 0, I = 0;
			const rows = lines.map( ( ln ) => {
				const c = numOf( ln, 'scheduled_value' );
				const d = numOf( ln, 'previous_completed' );
				const e = numOf( ln, 'this_period' );
				const fst = numOf( ln, 'materials_stored' );
				const g = d + e + fst;
				const pct = c ? ( g / c ) * 100 : 0;
				const h = c - g;
				const ret = numOf( ln, 'retainage' ) || ( retWorkPct * ( d + e ) + retStoredPct * fst );
				C += c; D += d; E += e; F += fst; I += ret;
				return { ln, c, d, e, fst, g, pct, h, ret };
			} );

			const G = D + E + F;
			const contractSumToDate = C;
			const totalEarnedLessRet = G - I;
			const currentDue = totalEarnedLessRet - previousCerts;
			const balanceToFinish = contractSumToDate - totalEarnedLessRet;

			Object.assign( state, {
				rows, C, D, E, F, G, I,
				netChangeOrders,
				originalContract: contractSumToDate - netChangeOrders,
				contractSumToDate,
				totalEarnedLessRet, previousCerts, currentDue, balanceToFinish,
				appNo: body.querySelector( '[data-pa="app_no"]' ).value,
				period: body.querySelector( '[data-pa="period"]' ).value,
			} );

			body.querySelector( '[data-pa="rows"]' ).innerHTML = rows.map( ( r ) => `
				<tr>
					<td>${ EM.tpl.esc( r.ln.item_no || '' ) }</td>
					<td>${ EM.tpl.esc( r.ln.description || '' ) }</td>
					<td class="text-end">${ money( r.c ) }</td>
					<td class="text-end">${ money( r.d ) }</td>
					<td class="text-end">${ money( r.e ) }</td>
					<td class="text-end">${ money( r.fst ) }</td>
					<td class="text-end">${ money( r.g ) }</td>
					<td class="text-end">${ r.pct.toFixed( 1 ) }%</td>
					<td class="text-end">${ money( r.h ) }</td>
					<td class="text-end">${ money( r.ret ) }</td>
				</tr>` ).join( '' );

			body.querySelector( '[data-pa="totals"]' ).innerHTML = `
				<td colspan="2">Grand total</td>
				<td class="text-end">${ money( C ) }</td>
				<td class="text-end">${ money( D ) }</td>
				<td class="text-end">${ money( E ) }</td>
				<td class="text-end">${ money( F ) }</td>
				<td class="text-end">${ money( G ) }</td>
				<td class="text-end">${ C ? ( ( G / C ) * 100 ).toFixed( 1 ) : '0' }%</td>
				<td class="text-end">${ money( C - G ) }</td>
				<td class="text-end">${ money( I ) }</td>`;

			const g702 = [
				[ '1. Original contract sum', state.originalContract ],
				[ '2. Net change by change orders', state.netChangeOrders ],
				[ '3. Contract sum to date', state.contractSumToDate ],
				[ '4. Total completed &amp; stored to date', state.G ],
				[ '5. Retainage', state.I ],
				[ '6. Total earned less retainage', state.totalEarnedLessRet ],
				[ '7. Less previous certificates for payment', state.previousCerts ],
				[ '8. CURRENT PAYMENT DUE', state.currentDue, true ],
				[ '9. Balance to finish, including retainage', state.balanceToFinish ],
			];
			body.querySelector( '[data-pa="g702"]' ).innerHTML = g702.map( ( [ label, val, strong ] ) => `
				<tr class="${ strong ? 'table-primary fw-bold' : '' }">
					<td>${ label }</td><td class="text-end">${ money( val ) }</td>
				</tr>` ).join( '' );
		}

		inputs.forEach( ( el ) => el.addEventListener( 'input', compute ) );
		body.querySelector( '[data-pa="app_no"]' ).addEventListener( 'input', compute );
		body.querySelector( '[data-pa="period"]' ).addEventListener( 'input', compute );
		compute();

		container.querySelector( '[data-pa="pdf"]' ).addEventListener( 'click', () => exportPdf( state, project ) );
	}

	function exportPdf( s, project ) {
		const { jsPDF } = window.jspdf;
		const pdf = new jsPDF( { unit: 'pt', format: 'letter' } );
		const m = ( n ) => '$' + Number( n || 0 ).toLocaleString( undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 } );

		// --- G702 cover ---
		pdf.setFontSize( 14 );
		pdf.text( 'APPLICATION AND CERTIFICATE FOR PAYMENT', 40, 46 );
		pdf.setFontSize( 9 );
		pdf.setTextColor( 110 );
		pdf.text( 'AIA Document G702 (format)', 40, 60 );
		pdf.setTextColor( 0 );
		pdf.text(
			[ project.name, project.number, 'Application No: ' + s.appNo, 'Period to: ' + s.period ].filter( Boolean ).join( '    ' ),
			40, 76
		);

		pdf.autoTable( {
			startY: 90,
			body: [
				[ '1. Original contract sum', m( s.originalContract ) ],
				[ '2. Net change by change orders', m( s.netChangeOrders ) ],
				[ '3. Contract sum to date (1 ± 2)', m( s.contractSumToDate ) ],
				[ '4. Total completed & stored to date (G703 Col G)', m( s.G ) ],
				[ '5. Retainage (G703 Col I)', m( s.I ) ],
				[ '6. Total earned less retainage (4 − 5)', m( s.totalEarnedLessRet ) ],
				[ '7. Less previous certificates for payment', m( s.previousCerts ) ],
				[ '8. CURRENT PAYMENT DUE (6 − 7)', m( s.currentDue ) ],
				[ '9. Balance to finish, including retainage (3 − 6)', m( s.balanceToFinish ) ],
			],
			styles: { fontSize: 9, cellPadding: 4 },
			columnStyles: { 0: { cellWidth: 360 }, 1: { halign: 'right' } },
			theme: 'grid',
		} );

		// --- G703 continuation sheet ---
		pdf.addPage( 'letter', 'landscape' );
		pdf.setFontSize( 12 );
		pdf.text( 'CONTINUATION SHEET — Schedule of Values (AIA G703 format)', 40, 40 );
		pdf.autoTable( {
			startY: 54,
			head: [ [ 'A', 'B — Description', 'C — Sched. value', 'D — Previous', 'E — This period', 'F — Stored', 'G — Compl.+stored', '%', 'H — Balance', 'I — Retainage' ] ],
			body: s.rows.map( ( r ) => [
				r.ln.item_no || '', r.ln.description || '', m( r.c ), m( r.d ), m( r.e ), m( r.fst ), m( r.g ), r.pct.toFixed( 1 ) + '%', m( r.h ), m( r.ret ),
			] ),
			foot: [ [ '', 'GRAND TOTAL', m( s.C ), m( s.D ), m( s.E ), m( s.F ), m( s.G ), ( s.C ? ( ( s.G / s.C ) * 100 ).toFixed( 1 ) : '0' ) + '%', m( s.C - s.G ), m( s.I ) ] ],
			styles: { fontSize: 7, cellPadding: 3 },
			headStyles: { fillColor: [ 33, 37, 41 ], fontSize: 7 },
			footStyles: { fillColor: [ 233, 236, 239 ], textColor: 0, fontStyle: 'bold' },
			columnStyles: { 2: { halign: 'right' }, 3: { halign: 'right' }, 4: { halign: 'right' }, 5: { halign: 'right' }, 6: { halign: 'right' }, 7: { halign: 'right' }, 8: { halign: 'right' }, 9: { halign: 'right' } },
			theme: 'grid',
		} );

		pdf.save( 'pay-application-G702-' + ( s.appNo || '1' ) + '.pdf' );
	}

	EM.registerModule( 'pay-applications', { list: render, view: render, form: render } );
} )( window.EM );
