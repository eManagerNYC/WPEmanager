/**
 * eManager — WP-Admin behaviors.
 */
( function () {
	'use strict';

	// Confirmation dialogs for destructive links.
	document.addEventListener( 'click', function ( event ) {
		const link = event.target.closest( '.em-confirm' );
		if ( link && ! window.confirm( link.dataset.confirm || 'Are you sure?' ) ) {
			event.preventDefault();
		}
	} );
} )();
