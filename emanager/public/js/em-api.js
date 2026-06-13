/**
 * eManager — REST API client.
 * Talks to the WordPress REST API (em/v1); records live in custom WP tables.
 */
window.EM = window.EM || {};

( function ( EM ) {
	'use strict';

	const config = window.EM_CONFIG || {};

	async function request( method, path, body ) {
		const options = {
			method,
			headers: { 'X-WP-Nonce': config.nonce },
			credentials: 'same-origin',
		};
		if ( body !== undefined ) {
			options.headers[ 'Content-Type' ] = 'application/json';
			options.body = JSON.stringify( body );
		}

		const response = await fetch( config.apiRoot + path, options );
		const json = await response.json().catch( () => ( {} ) );

		if ( ! response.ok ) {
			throw new Error( json.message || 'Request failed (' + response.status + ')' );
		}
		return json;
	}

	EM.api = {
		config,

		boot() {
			return request( 'GET', '/boot' );
		},

		list( moduleId, params = {} ) {
			const query = new URLSearchParams();
			for ( const [ key, value ] of Object.entries( params ) ) {
				if ( value === '' || value === undefined || value === null ) continue;
				if ( key === 'filters' ) {
					for ( const [ col, val ] of Object.entries( value ) ) {
						if ( val !== '' ) query.set( `filters[${ col }]`, val );
					}
				} else {
					query.set( key, value );
				}
			}
			return request( 'GET', `/modules/${ moduleId }/records?` + query.toString() );
		},

		get( moduleId, id ) {
			return request( 'GET', `/modules/${ moduleId }/records/${ id }` );
		},

		create( moduleId, data ) {
			return request( 'POST', `/modules/${ moduleId }/records`, data );
		},

		update( moduleId, id, data ) {
			return request( 'PUT', `/modules/${ moduleId }/records/${ id }`, data );
		},

		remove( moduleId, id ) {
			return request( 'DELETE', `/modules/${ moduleId }/records/${ id }` );
		},

		comments( moduleId, id ) {
			return request( 'GET', `/modules/${ moduleId }/records/${ id }/comments` );
		},

		addComment( moduleId, id, body ) {
			return request( 'POST', `/modules/${ moduleId }/records/${ id }/comments`, { body } );
		},

		transition( moduleId, id, payload ) {
			return request( 'POST', `/modules/${ moduleId }/records/${ id }/transition`, payload );
		},

		activity( moduleId, id ) {
			return request( 'GET', `/modules/${ moduleId }/records/${ id }/activity` );
		},

		spawn( moduleId, id, target ) {
			return request( 'POST', `/modules/${ moduleId }/records/${ id }/spawn`, { target } );
		},

		weather( lat, lon ) {
			return request( 'GET', `/weather?lat=${ lat }&lon=${ lon }` );
		},

		stats() {
			return request( 'GET', '/reports/stats' );
		},

		costSummary() {
			return request( 'GET', '/reports/cost-summary' );
		},
	};
} )( window.EM );
