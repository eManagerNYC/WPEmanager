/**
 * eManager — application shell & hash router.
 *
 * Routes:
 *   #/                                  home (section overview)
 *   #/<section>/<module>                list
 *   #/<section>/<module>/view/<id>      record view
 *   #/<section>/<module>/form           new record
 *   #/<section>/<module>/form/<id>      edit record
 *
 * Custom modules may take over rendering by shipping a module.js that calls:
 *   EM.registerModule( 'module-id', { list, view, form } )
 */
( function ( EM ) {
	'use strict';

	const custom = new Map(); // module-id => custom renderers
	const loadedAssets = new Set();

	EM.registerModule = function ( id, renderers ) {
		custom.set( id, renderers || {} );
	};

	EM.app = {
		boot: null,
		root: null,
		main: null,
		registry: [],

		async start() {
			this.root = document.getElementById( 'em-app' );
			if ( ! this.root ) return;

			try {
				this.boot = await EM.api.boot();
			} catch ( error ) {
				this.root.innerHTML = `<div class="alert alert-danger m-4">${ EM.tpl.esc( error.message ) }</div>`;
				return;
			}
			this.registry = this.boot.registry || [];

			await this.layout();
			this.bindChrome();
			this.buildSidebar();

			window.addEventListener( 'hashchange', () => this.route() );
			await this.route();
		},

		/** Assemble the shell from separate partial files (parallel fetch). */
		async layout() {
			const base = EM.api.config.partialsUrl;
			const [ header, navbar, sidebar, footer ] = await Promise.all(
				[ 'header.html', 'navbar.html', 'sidebar.html', 'footer.html' ].map( ( f ) => EM.tpl.load( base + f ) )
			);
			this.root.innerHTML = header + sidebar + navbar + '<main class="em-main" id="em-main" tabindex="-1"></main>' + footer;
			this.root.removeAttribute( 'data-loading' );
			this.main = document.getElementById( 'em-main' );
		},

		/** Wire header/footer widgets. */
		bindChrome() {
			const { user, project } = this.boot;
			const set = ( sel, text ) => {
				const el = this.root.querySelector( `[data-em="${ sel }"]` );
				if ( el ) el.textContent = text || '';
			};

			set( 'user-name', user.name );
			set( 'user-email', user.email );
			set( 'user-role', 'Role: ' + ( user.roles || [] ).join( ', ' ).replace( /em_/g, '' ) );
			set( 'project-name', project.name );
			set( 'project-footer', [ project.name, project.number ].filter( Boolean ).join( ' · ' ) );

			const logout = this.root.querySelector( '[data-em="logout"]' );
			if ( logout ) {
				logout.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					window.location.href = EM.api.config.logoutUrl;
				} );
			}

			// Mobile sidebar toggle.
			const toggle = this.root.querySelector( '[data-em-toggle="sidebar"]' );
			const sidebar = this.root.querySelector( '.em-sidebar' );
			if ( toggle && sidebar ) {
				toggle.addEventListener( 'click', () => sidebar.classList.toggle( 'show' ) );
				sidebar.addEventListener( 'click', ( e ) => {
					if ( e.target.closest( 'a' ) ) sidebar.classList.remove( 'show' );
				} );
			}

			// Global search proxies into the list search box.
			const search = this.root.querySelector( '[data-em="global-search"]' );
			if ( search ) {
				search.addEventListener( 'input', () => {
					const listSearch = this.main.querySelector( '[data-em="list-search"]' );
					if ( listSearch ) {
						listSearch.value = search.value;
						listSearch.dispatchEvent( new Event( 'input' ) );
					}
				} );
			}
		},

		/** Build collapsible sections + module links in the sidebar. */
		buildSidebar() {
			const host = this.root.querySelector( '[data-em="sidebar-sections"]' );
			if ( ! host ) return;
			host.innerHTML = '';

			this.registry.forEach( ( section, index ) => {
				const collapseId = 'em-sec-' + section.id;
				const li = document.createElement( 'li' );
				li.innerHTML = `
					<button class="em-section-toggle" type="button" data-bs-toggle="collapse"
						data-bs-target="#${ collapseId }" aria-expanded="${ index === 0 }" aria-controls="${ collapseId }">
						<i class="bi ${ EM.tpl.esc( section.icon ) }" aria-hidden="true"></i>
						<span>${ EM.tpl.esc( section.name ) }</span>
						<i class="bi bi-chevron-down" aria-hidden="true"></i>
					</button>
					<ul class="nav flex-column collapse ${ index === 0 ? 'show' : '' }" id="${ collapseId }">
						${ section.modules.map( ( m ) => `
							<li class="nav-item">
								<a class="nav-link" href="#/${ m.section }/${ m.id }" data-module="${ m.id }">
									<i class="bi ${ EM.tpl.esc( m.icon || 'bi-dot' ) }" aria-hidden="true"></i>
									<span>${ EM.tpl.esc( m.name ) }</span>
								</a>
							</li>` ).join( '' ) }
					</ul>`;
				host.appendChild( li );
			} );
		},

		findModule( id ) {
			for ( const section of this.registry ) {
				const found = section.modules.find( ( m ) => m.id === id );
				if ( found ) return found;
			}
			return null;
		},

		/** Load a module's optional module.js / module.css once. */
		async loadModuleAssets( module ) {
			const assets = module.assets || {};
			if ( assets[ 'module.css' ] && ! loadedAssets.has( assets[ 'module.css' ] ) ) {
				loadedAssets.add( assets[ 'module.css' ] );
				const link = document.createElement( 'link' );
				link.rel = 'stylesheet';
				link.href = assets[ 'module.css' ];
				document.head.appendChild( link );
			}
			if ( assets[ 'module.js' ] && ! loadedAssets.has( assets[ 'module.js' ] ) ) {
				loadedAssets.add( assets[ 'module.js' ] );
				await new Promise( ( resolve, reject ) => {
					const script = document.createElement( 'script' );
					script.src = assets[ 'module.js' ];
					script.onload = resolve;
					script.onerror = reject;
					document.head.appendChild( script );
				} );
			}
		},

		setBreadcrumb( sectionName, pageName ) {
			const section = this.root.querySelector( '[data-em="crumb-section"]' );
			const page = this.root.querySelector( '[data-em="crumb-page"]' );
			if ( section ) {
				section.textContent = sectionName || '';
				section.classList.toggle( 'd-none', ! sectionName );
			}
			if ( page ) {
				page.textContent = pageName || '';
				page.classList.toggle( 'd-none', ! pageName );
			}
		},

		setRecordCount( total ) {
			const el = this.root.querySelector( '[data-em="record-count"]' );
			if ( el ) el.textContent = total === undefined ? '' : `${ total } records`;
		},

		setActiveNav( moduleId ) {
			this.root.querySelectorAll( '.em-sidebar .nav-link' ).forEach( ( link ) => {
				link.classList.toggle( 'active', link.dataset.module === moduleId );
			} );
		},

		/** Hash router. */
		async route() {
			const parts = location.hash.replace( /^#\/?/, '' ).split( '/' ).filter( Boolean );
			this.setRecordCount( undefined );
			this.main.innerHTML = `<div class="text-center py-5"><span class="spinner-border text-primary"></span></div>`;

			// Home.
			if ( ! parts.length ) {
				this.setActiveNav( null );
				this.setBreadcrumb( null, null );
				await this.renderHome();
				return;
			}

			const [ , moduleId, action = 'list', id ] = [ parts[ 0 ], parts[ 1 ], parts[ 2 ], parts[ 3 ] ];
			const module = this.findModule( moduleId );
			if ( ! module ) {
				this.main.innerHTML = `<div class="alert alert-warning">Module not found.</div>`;
				return;
			}

			const sectionName = ( this.registry.find( ( s ) => s.id === module.section ) || {} ).name;
			this.setActiveNav( module.id );
			this.setBreadcrumb( sectionName, module.name );
			this.main.focus();

			try {
				await this.loadModuleAssets( module );
				const overrides = custom.get( module.id ) || {};
				const page = action === 'view' ? 'view' : ( action === 'form' ? 'form' : 'list' );

				if ( typeof overrides[ page ] === 'function' ) {
					await overrides[ page ]( this.main, module, id );
				} else if ( page === 'view' ) {
					await EM.view.render( this.main, module, id );
				} else if ( page === 'form' ) {
					await EM.form.render( this.main, module, id );
				} else {
					await EM.table.render( this.main, module );
				}
			} catch ( error ) {
				this.main.innerHTML = `<div class="alert alert-danger">${ EM.tpl.esc( error.message ) }</div>`;
			}
		},

		/** Home: project banner + a card per section. */
		/**
		 * Patent: "deploys information to tailored dashboards for the Owner, GC,
		 * Subcontractors and Consultants." Renders a role-specific quick-action
		 * panel of the Change Management steps that party typically drives.
		 */
		renderRolePanel() {
			const host = this.main.querySelector( '[data-em="home-role"]' );
			if ( ! host ) return;

			const party = ( this.boot.user || {} ).party_role || '';
			const labels = {
				gc: 'General Contractor', owner: 'Owner', rep: "Owner's Representative",
				consultant: 'Consultant', subcontractor: 'Subcontractor',
			};
			// Which Change Management modules each party acts on first.
			const focus = {
				gc: [ 'pco-requests', 'nocs', 'directives', 'proposals', 'cors', 'etickets', 'dcrs', 'rfis' ],
				owner: [ 'nocs', 'cors' ],
				rep: [ 'nocs', 'ssi', 'cors' ],
				consultant: [ 'rfis', 'ssi' ],
				subcontractor: [ 'directives', 'proposals', 'etickets', 'dcrs' ],
			};
			if ( ! party || ! focus[ party ] ) {
				host.classList.add( 'd-none' );
				return;
			}

			const index = {};
			this.registry.forEach( ( s ) => s.modules.forEach( ( m ) => {
				index[ m.id ] = m;
			} ) );

			const chips = focus[ party ]
				.map( ( mid ) => index[ mid ] )
				.filter( Boolean )
				.map( ( m ) => `<a class="btn btn-sm btn-outline-primary" href="#/${ m.section }/${ m.id }">
					<i class="bi ${ EM.tpl.esc( m.icon || 'bi-dot' ) } me-1" aria-hidden="true"></i>${ EM.tpl.esc( m.name ) }</a>` )
				.join( '' );

			host.classList.remove( 'd-none' );
			host.innerHTML = `
				<div class="card border-0 shadow-sm">
					<div class="card-body">
						<div class="d-flex align-items-center gap-2 mb-2">
							<span class="badge text-bg-primary">${ EM.tpl.esc( labels[ party ] ) }</span>
							<span class="text-secondary small">Your change-management workspace</span>
						</div>
						<div class="d-flex flex-wrap gap-2">${ chips || '<span class="text-secondary small">No assigned steps.</span>' }</div>
					</div>
				</div>`;
		},

		async renderHome() {
			this.main.innerHTML = await EM.tpl.load( EM.api.config.partialsUrl + 'home.html' );
			const { project } = this.boot;

			const name = this.main.querySelector( '[data-em="home-project-name"]' );
			const meta = this.main.querySelector( '[data-em="home-project-meta"]' );
			if ( name && project.name ) name.textContent = project.name;
			if ( meta ) {
				meta.textContent = [ project.number, project.address, project.owner ? 'Owner: ' + project.owner : '' ]
					.filter( Boolean ).join( '  ·  ' );
			}

			this.renderRolePanel();

			const host = this.main.querySelector( '[data-em="home-sections"]' );
			if ( ! host ) return;
			host.innerHTML = this.registry.map( ( section ) => `
				<div class="col-sm-6 col-lg-4 col-xxl-3">
					<div class="card h-100">
						<div class="card-header d-flex align-items-center gap-2">
							<i class="bi ${ EM.tpl.esc( section.icon ) }" aria-hidden="true"></i>
							<strong>${ EM.tpl.esc( section.name ) }</strong>
						</div>
						<ul class="list-group list-group-flush">
							${ section.modules.map( ( m ) => `
								<li class="list-group-item p-0">
									<a class="d-flex align-items-center gap-2 px-3 py-2 text-body" href="#/${ m.section }/${ m.id }">
										<i class="bi ${ EM.tpl.esc( m.icon || 'bi-dot' ) } text-secondary" aria-hidden="true"></i>
										${ EM.tpl.esc( m.name ) }
									</a>
								</li>` ).join( '' ) }
						</ul>
					</div>
				</div>` ).join( '' );
		},
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', () => EM.app.start() );
	} else {
		EM.app.start();
	}
} )( window.EM );
