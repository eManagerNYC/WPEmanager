/**
 * eManager module: Project Info — read-only project card from plugin settings.
 */
( function ( EM ) {
	'use strict';

	function render( container ) {
		const p = EM.app.boot.project || {};
		const esc = EM.tpl.esc.bind( EM.tpl );
		const row = ( label, value ) => value
			? `<div class="col-sm-6"><dt>${ label }</dt><dd>${ esc( value ) }</dd></div>` : '';

		container.innerHTML = `
			<div class="card em-view">
				<div class="card-header bg-body"><h2 class="h5 mb-0"><i class="bi bi-info-circle me-1" aria-hidden="true"></i> Project Information</h2></div>
				<div class="card-body">
					<dl class="row g-2 mb-0">
						${ row( 'Project name', p.name ) }
						${ row( 'Project number', p.number ) }
						${ row( 'Site address', p.address ) }
						${ row( 'Owner', p.owner ) }
						${ row( 'Architect / Engineer', p.architect ) }
						${ row( 'Start', p.start ) }
						${ row( 'Finish', p.finish ) }
					</dl>
					${ ! p.name ? '<p class="text-secondary mb-0">No project information yet.</p>' : '' }
				</div>
				<div class="card-footer bg-body small text-secondary">
					Project settings are managed by administrators in WordPress Admin → eManager → Settings.
				</div>
			</div>`;
	}

	EM.registerModule( 'project-info', { list: render, view: render, form: render } );
} )( window.EM );
