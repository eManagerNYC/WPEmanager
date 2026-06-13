/**
 * eManager — template loader & helpers.
 * Fetches .html partials once, caches them, and provides escaping/formatting.
 */
( function ( EM ) {
	'use strict';

	const cache = new Map();

	EM.tpl = {
		/**
		 * Fetch an HTML template by URL (cached for the session).
		 */
		async load( url ) {
			if ( ! cache.has( url ) ) {
				const response = await fetch( url, { credentials: 'same-origin' } );
				if ( ! response.ok ) throw new Error( 'Template not found: ' + url );
				cache.set( url, await response.text() );
			}
			return cache.get( url );
		},

		/**
		 * Resolve a module template: module override first, shared default otherwise.
		 *
		 * @param {Object} module Module definition from the registry.
		 * @param {string} name   "list" | "view" | "form".
		 */
		forModule( module, name ) {
			const file = name + '.html';
			const url = ( module.assets && module.assets[ file ] )
				? module.assets[ file ]
				: EM.api.config.defaultsUrl + file;
			return this.load( url );
		},

		/** Escape a value for HTML output. */
		esc( value ) {
			const div = document.createElement( 'div' );
			div.textContent = value === null || value === undefined ? '' : String( value );
			return div.innerHTML;
		},

		/** Format a field value for display. */
		format( field, value ) {
			if ( value === null || value === undefined || value === '' ) return '—';
			switch ( field.type ) {
				case 'date':
					return new Date( value + 'T00:00:00' ).toLocaleDateString();
				case 'datetime':
					return new Date( value ).toLocaleString();
				case 'currency':
					return Number( value ).toLocaleString( undefined, { style: 'currency', currency: 'USD' } );
				case 'checkbox':
					return value ? 'Yes' : 'No';
				case 'textarea':
				case 'richtext':
					return this.esc( value ).replace( /\n/g, '<br>' );
				case 'signature':
					// Only render strictly validated PNG data URLs (same rule as the server).
					if ( /^data:image\/png;base64,[A-Za-z0-9+/]+={0,2}$/.test( value ) ) {
						return `<img src="${ value }" alt="Signature" class="em-signature-img border rounded bg-white" />`;
					}
					return '—';
				default:
					return this.esc( value );
			}
		},

		/** Bootstrap color for a status keyword. */
		statusColor( status ) {
			const s = String( status || '' ).toLowerCase();
			if ( /(approved|closed|complete|paid|passed|active|executed|answered|received)/.test( s ) ) return 'success';
			if ( /(pending|open|review|submitted|in progress|draft|forecast)/.test( s ) ) return 'warning';
			if ( /(rejected|void|failed|overdue|deficient|denied)/.test( s ) ) return 'danger';
			return 'secondary';
		},

		statusBadge( status ) {
			if ( ! status ) return '';
			return `<span class="badge text-bg-${ this.statusColor( status ) } em-status">${ this.esc( status ) }</span>`;
		},

		/** Toast helper. */
		toast( message, tone = 'success' ) {
			let host = document.getElementById( 'em-toasts' );
			if ( ! host ) {
				host = document.createElement( 'div' );
				host.id = 'em-toasts';
				host.className = 'toast-container position-fixed bottom-0 end-0 p-3';
				document.body.appendChild( host );
			}
			const el = document.createElement( 'div' );
			el.className = `toast align-items-center text-bg-${ tone } border-0`;
			el.setAttribute( 'role', 'status' );
			el.innerHTML = `<div class="d-flex"><div class="toast-body">${ this.esc( message ) }</div>
				<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
			host.appendChild( el );
			const toast = new bootstrap.Toast( el, { delay: 3500 } );
			toast.show();
			el.addEventListener( 'hidden.bs.toast', () => el.remove() );
		},
	};
} )( window.EM );
