/**
 * eManager module: Schedule of Values (AIA G703 line item).
 *
 * Auto-computes the derived G703 columns on the form so each line stays
 * internally consistent:
 *   Col G  Total completed & stored = D (previous) + E (this period) + F (stored)
 *   %      = G / C (scheduled value)
 *   Col H  Balance to finish        = C - G
 * Retainage (Col I) is left to the user (rate varies by contract / line).
 */
( function ( EM ) {
	'use strict';

	function form( container, module, id ) {
		return EM.form.render( container, module, id ).then( function () {
			const get = ( name ) => container.querySelector( `[name="${ name }"]` );
			const num = ( name ) => {
				const el = get( name );
				return el && el.value !== '' ? Number( el.value ) : 0;
			};

			const scheduled = get( 'scheduled_value' );
			const previous = get( 'previous_completed' );
			const thisPeriod = get( 'this_period' );
			const stored = get( 'materials_stored' );
			const total = get( 'total_completed' );
			const percent = get( 'percent_complete' );
			const balance = get( 'balance_to_finish' );
			if ( ! total ) return;

			// Derived columns are computed, not typed.
			[ total, percent, balance ].forEach( ( el ) => {
				if ( el ) {
					el.readOnly = true;
					el.classList.add( 'bg-body-secondary' );
				}
			} );

			function recompute() {
				const c = num( 'scheduled_value' );
				const g = num( 'previous_completed' ) + num( 'this_period' ) + num( 'materials_stored' );
				if ( total ) total.value = g.toFixed( 2 );
				if ( percent ) percent.value = c ? ( ( g / c ) * 100 ).toFixed( 1 ) : '0';
				if ( balance ) balance.value = ( c - g ).toFixed( 2 );
			}

			[ scheduled, previous, thisPeriod, stored ].forEach( ( el ) => {
				if ( el ) el.addEventListener( 'input', recompute );
			} );
			recompute();
		} );
	}

	EM.registerModule( 'schedule-of-values', { form } );
} )( window.EM );
