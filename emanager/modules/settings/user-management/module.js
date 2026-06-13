/**
 * eManager module: User Management — role matrix overview with links to
 * the WordPress admin screens where users, roles and companies are managed.
 */
( function ( EM ) {
	'use strict';

	const ROLES = [
		[ 'Administrator', '✔', '✔', '✔', '✔ any record' ],
		[ 'Editor', '✔', '✔', '✔', 'own records only' ],
		[ 'Contributor', '✔', '✔', '—', 'own records only' ],
		[ 'Viewer', '—', '✔', '—', '—' ],
		[ 'Restricted', '—', '—', '—', '—' ],
	];

	function render( container ) {
		const isManager = EM.app.boot.caps.em_manage;
		const adminBase = EM.api.config.apiRoot.replace( /wp-json.*$/, 'wp-admin/' );

		container.innerHTML = `
			<div class="card">
				<div class="card-header bg-body"><h2 class="h5 mb-0"><i class="bi bi-people me-1" aria-hidden="true"></i> User Management</h2></div>
				<div class="card-body">
					<p>Each user is assigned one eManager role and one company. Users may always delete <strong>their own</strong> records if they can create; only Administrators delete any record.</p>
					<div class="table-responsive">
						<table class="table table-bordered align-middle">
							<thead class="table-light"><tr><th>Role</th><th>Create</th><th>Read</th><th>Update</th><th>Delete</th></tr></thead>
							<tbody>${ ROLES.map( ( r ) => `<tr>${ r.map( ( c, i ) => i ? `<td class="text-center">${ c }</td>` : `<th scope="row">${ c }</th>` ).join( '' ) }</tr>` ).join( '' ) }</tbody>
						</table>
					</div>
					${ isManager ? `
					<div class="d-flex flex-wrap gap-2">
						<a class="btn btn-primary" href="${ adminBase }admin.php?page=emanager-users" target="_blank" rel="noopener"><i class="bi bi-person-gear" aria-hidden="true"></i> Manage users & roles</a>
						<a class="btn btn-outline-primary" href="${ adminBase }user-new.php" target="_blank" rel="noopener"><i class="bi bi-person-plus" aria-hidden="true"></i> Add user</a>
						<a class="btn btn-outline-primary" href="${ adminBase }admin.php?page=emanager-companies" target="_blank" rel="noopener"><i class="bi bi-building" aria-hidden="true"></i> Manage companies</a>
					</div>` : '<p class="text-secondary mb-0">Contact a project administrator to change roles or companies.</p>' }
				</div>
			</div>`;
	}

	EM.registerModule( 'user-management', { list: render, view: render, form: render } );
} )( window.EM );
