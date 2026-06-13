/**
 * eManager module: eTickets — Time & Materials line-item builder.
 *
 * Implements the patent's eTicket: a subcontractor builds line items against
 * their own labor types and rates (pulled from the Resources rate tables) and
 * the ticket totals calculate automatically. The line items are stored in the
 * `line_items` JSON field; labor/material/equipment subtotals and the grand
 * total are written to their currency fields on save.
 */
( function ( EM ) {
	'use strict';

	const KINDS = [
		{ key: 'labor', label: 'Labor', module: 'labor-rates', nameField: 'classification', rateField: 'burden_rate', altRate: 'base_rate', unit: 'hrs' },
		{ key: 'material', label: 'Material', module: 'material-rates', nameField: 'material', rateField: 'unit_cost', unit: 'unit' },
		{ key: 'equipment', label: 'Equipment', module: 'equipment-rates', nameField: 'equipment', rateField: 'rate', unit: 'unit' },
	];

	async function form( container, module, id ) {
		await EM.form.render( container, module, id ); // Default form first.

		const fieldsHost = container.querySelector( '[data-em="form-fields"]' );
		const jsonInput = container.querySelector( '[name="line_items"]' );
		if ( ! fieldsHost || ! jsonInput ) return;

		// Hide the raw JSON textarea; we drive it from the builder.
		const jsonWrap = jsonInput.closest( '.col-12, .col-md-6' );
		if ( jsonWrap ) jsonWrap.classList.add( 'd-none' );

		// Load the project rate tables once (best-effort).
		const rates = {};
		await Promise.all( KINDS.map( async ( kind ) => {
			try {
				const res = await EM.api.list( kind.module, { per_page: 200, sort: kind.nameField, order: 'asc' } );
				rates[ kind.key ] = res.records || [];
			} catch ( e ) {
				rates[ kind.key ] = [];
			}
		} ) );

		// Seed from existing value (editing) or start with one labor row.
		let lines = [];
		try {
			lines = JSON.parse( jsonInput.value || '[]' );
		} catch ( e ) {
			lines = [];
		}
		if ( ! Array.isArray( lines ) || ! lines.length ) {
			lines = [ { kind: 'labor', desc: '', qty: 1, rate: 0 } ];
		}

		const builder = document.createElement( 'div' );
		builder.className = 'col-12 em-eticket';
		builder.innerHTML = `
			<label class="form-label">Ticket line items</label>
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-2">
					<thead class="table-light">
						<tr><th style="width:120px">Type</th><th>Description</th><th style="width:90px">Qty</th><th style="width:120px">Rate</th><th style="width:120px" class="text-end">Amount</th><th></th></tr>
					</thead>
					<tbody data-em="eticket-rows"></tbody>
					<tfoot>
						<tr><td colspan="4" class="text-end fw-semibold">Labor</td><td class="text-end" data-em="sum-labor">$0.00</td><td></td></tr>
						<tr><td colspan="4" class="text-end fw-semibold">Material</td><td class="text-end" data-em="sum-material">$0.00</td><td></td></tr>
						<tr><td colspan="4" class="text-end fw-semibold">Equipment</td><td class="text-end" data-em="sum-equipment">$0.00</td><td></td></tr>
						<tr class="table-active"><td colspan="4" class="text-end fw-bold">Ticket total</td><td class="text-end fw-bold" data-em="sum-total">$0.00</td><td></td></tr>
					</tfoot>
				</table>
			</div>
			<button type="button" class="btn btn-sm btn-outline-primary" data-em="eticket-add"><i class="bi bi-plus-lg"></i> Add line</button>`;
		jsonWrap ? jsonWrap.after( builder ) : fieldsHost.appendChild( builder );

		const rowsHost = builder.querySelector( '[data-em="eticket-rows"]' );
		const money = ( n ) => '$' + Number( n || 0 ).toLocaleString( undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 } );

		function recalc() {
			const totals = { labor: 0, material: 0, equipment: 0 };
			lines.forEach( ( ln ) => {
				totals[ ln.kind ] = ( totals[ ln.kind ] || 0 ) + Number( ln.qty || 0 ) * Number( ln.rate || 0 );
			} );
			const grand = totals.labor + totals.material + totals.equipment;
			builder.querySelector( '[data-em="sum-labor"]' ).textContent = money( totals.labor );
			builder.querySelector( '[data-em="sum-material"]' ).textContent = money( totals.material );
			builder.querySelector( '[data-em="sum-equipment"]' ).textContent = money( totals.equipment );
			builder.querySelector( '[data-em="sum-total"]' ).textContent = money( grand );

			// Mirror into the hidden JSON + total fields so the default collector saves them.
			jsonInput.value = JSON.stringify( lines );
			const set = ( name, val ) => {
				const el = container.querySelector( `[name="${ name }"]` );
				if ( el ) el.value = val.toFixed( 2 );
			};
			set( 'labor_total', totals.labor );
			set( 'material_total', totals.material );
			set( 'equipment_total', totals.equipment );
			set( 'total', grand );
		}

		function draw() {
			rowsHost.innerHTML = '';
			lines.forEach( ( ln, i ) => {
				const kind = KINDS.find( ( k ) => k.key === ln.kind ) || KINDS[ 0 ];
				const tr = document.createElement( 'tr' );
				const options = ( rates[ ln.kind ] || [] ).map( ( r ) => {
					const rate = r[ kind.rateField ] || r[ kind.altRate ] || 0;
					return `<option value="${ EM.tpl.esc( r[ kind.nameField ] ) }" data-rate="${ rate }">${ EM.tpl.esc( r[ kind.nameField ] ) } (${ money( rate ) })</option>`;
				} ).join( '' );
				tr.innerHTML = `
					<td>
						<select class="form-select form-select-sm" data-row="${ i }" data-k="kind">
							${ KINDS.map( ( k ) => `<option value="${ k.key }" ${ k.key === ln.kind ? 'selected' : '' }>${ k.label }</option>` ).join( '' ) }
						</select>
					</td>
					<td>
						<input list="em-rate-list-${ i }" class="form-control form-control-sm" data-row="${ i }" data-k="desc" value="${ EM.tpl.esc( ln.desc || '' ) }" placeholder="Select or type…" />
						<datalist id="em-rate-list-${ i }">${ options }</datalist>
					</td>
					<td><input type="number" step="any" class="form-control form-control-sm" data-row="${ i }" data-k="qty" value="${ ln.qty }" /></td>
					<td><input type="number" step="any" class="form-control form-control-sm" data-row="${ i }" data-k="rate" value="${ ln.rate }" /></td>
					<td class="text-end">${ money( Number( ln.qty || 0 ) * Number( ln.rate || 0 ) ) }</td>
					<td><button type="button" class="btn btn-sm btn-outline-danger" data-row="${ i }" data-k="del" aria-label="Remove"><i class="bi bi-x-lg"></i></button></td>`;
				rowsHost.appendChild( tr );
			} );
			recalc();
		}

		// Delegated input/change handling.
		builder.addEventListener( 'input', ( e ) => {
			const el = e.target.closest( '[data-row]' );
			if ( ! el ) return;
			const i = Number( el.dataset.row );
			const k = el.dataset.k;
			if ( k === 'qty' || k === 'rate' ) {
				lines[ i ][ k ] = el.value;
				draw();
			} else if ( k === 'desc' ) {
				lines[ i ].desc = el.value;
				// Auto-fill rate when the description matches a rate-table entry.
				const opt = el.parentElement.querySelector( `option[value="${ CSS.escape( el.value ) }"]` );
				if ( opt && opt.dataset.rate ) {
					lines[ i ].rate = Number( opt.dataset.rate );
					draw();
				} else {
					recalc();
				}
			}
		} );
		builder.addEventListener( 'change', ( e ) => {
			const el = e.target.closest( '[data-k="kind"]' );
			if ( ! el ) return;
			lines[ Number( el.dataset.row ) ].kind = el.value;
			draw();
		} );
		builder.addEventListener( 'click', ( e ) => {
			if ( e.target.closest( '[data-em="eticket-add"]' ) ) {
				lines.push( { kind: 'labor', desc: '', qty: 1, rate: 0 } );
				draw();
			}
			const del = e.target.closest( '[data-k="del"]' );
			if ( del ) {
				lines.splice( Number( del.dataset.row ), 1 );
				if ( ! lines.length ) lines.push( { kind: 'labor', desc: '', qty: 1, rate: 0 } );
				draw();
			}
		} );

		draw();
	}

	EM.registerModule( 'etickets', { form } );
} )( window.EM );
