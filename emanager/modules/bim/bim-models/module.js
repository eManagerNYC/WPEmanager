/**
 * eManager module: 3D Models.
 *
 * By default this shows a link to open/download the .IFC file (the distributed
 * plugin makes NO external requests, per the WordPress.org guidelines).
 *
 * An optional in-browser 3D viewer renders the IFC with three.js / web-ifc.
 * Because those libraries are not bundled, it is OFF by default and loads them
 * from a CDN only when a site owner explicitly opts in:
 *
 *   add_filter( 'em_enable_ifc_viewer', '__return_true' );
 *
 * For a fully self-hosted build, bundle three.js + web-ifc locally and point
 * the URLs below at plugin-local files.
 */
( function ( EM ) {
	'use strict';

	const THREE_URL = 'https://cdn.jsdelivr.net/npm/three@0.149.0/+esm';
	const IFC_URL = 'https://cdn.jsdelivr.net/npm/web-ifc-three@0.0.126/+esm';
	const ORBIT_URL = 'https://cdn.jsdelivr.net/npm/three@0.149.0/examples/jsm/controls/OrbitControls.js/+esm';
	const WASM_PATH = 'https://cdn.jsdelivr.net/npm/web-ifc@0.0.36/';

	async function view( container, module, id ) {
		await EM.view.render( container, module, id ); // Standard record view first.

		const record = await EM.api.get( module.id, id );
		if ( ! record.ifc_url ) return;

		const viewerEnabled = !! ( EM.app.boot.flags && EM.app.boot.flags.ifc_viewer );

		// Default (compliant) path: just a link to the model, no external code.
		if ( ! viewerEnabled ) {
			const link = document.createElement( 'div' );
			link.className = 'card mb-3 em-no-print';
			link.innerHTML = `
				<div class="card-header bg-body"><h3 class="h6 mb-0"><i class="bi bi-box me-1" aria-hidden="true"></i> 3D Model (IFC)</h3></div>
				<div class="card-body">
					<a class="btn btn-outline-primary" href="${ EM.tpl.esc( record.ifc_url ) }" target="_blank" rel="noopener">
						<i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>Open / download IFC file</a>
					<p class="small text-secondary mt-2 mb-0">The in-browser 3D viewer is optional and disabled by default. A site administrator can enable it with the <code>em_enable_ifc_viewer</code> filter.</p>
				</div>`;
			container.querySelector( '.em-comments' )?.before( link );
			return;
		}

		const card = document.createElement( 'div' );
		card.className = 'card mb-3 em-no-print';
		card.innerHTML = `
			<div class="card-header bg-body d-flex align-items-center gap-2">
				<h3 class="h6 mb-0"><i class="bi bi-box me-1" aria-hidden="true"></i> IFC Viewer</h3>
				<span class="small text-secondary ms-auto" data-bim="status">Loading 3D engine…</span>
			</div>
			<div class="card-body p-0">
				<div data-bim="canvas" style="height:480px;width:100%;background:#1a1d21;"></div>
			</div>`;
		container.querySelector( '.em-comments' )?.before( card );

		const host = card.querySelector( '[data-bim="canvas"]' );
		const status = card.querySelector( '[data-bim="status"]' );

		try {
			const [ THREE, { IFCLoader }, { OrbitControls } ] = await Promise.all( [
				import( THREE_URL ), import( IFC_URL ), import( ORBIT_URL ),
			] );

			const scene = new THREE.Scene();
			scene.background = new THREE.Color( 0x1a1d21 );

			const camera = new THREE.PerspectiveCamera( 60, host.clientWidth / host.clientHeight, 0.1, 2000 );
			camera.position.set( 15, 12, 15 );

			const renderer = new THREE.WebGLRenderer( { antialias: true } );
			renderer.setSize( host.clientWidth, host.clientHeight );
			renderer.setPixelRatio( Math.min( window.devicePixelRatio, 2 ) );
			host.appendChild( renderer.domElement );

			scene.add( new THREE.AmbientLight( 0xffffff, 0.6 ) );
			const sun = new THREE.DirectionalLight( 0xffffff, 0.9 );
			sun.position.set( 10, 20, 10 );
			scene.add( sun );
			scene.add( new THREE.GridHelper( 50, 50, 0x444444, 0x2a2d31 ) );

			const controls = new OrbitControls( camera, renderer.domElement );
			controls.enableDamping = true;

			status.textContent = 'Loading IFC model…';
			const loader = new IFCLoader();
			await loader.ifcManager.setWasmPath( WASM_PATH );
			loader.load(
				record.ifc_url,
				( model ) => {
					scene.add( model );
					// Frame the model.
					const box = new THREE.Box3().setFromObject( model );
					const center = box.getCenter( new THREE.Vector3() );
					const size = box.getSize( new THREE.Vector3() ).length() || 20;
					controls.target.copy( center );
					camera.position.copy( center ).add( new THREE.Vector3( size / 2, size / 3, size / 2 ) );
					status.textContent = 'Drag to orbit · scroll to zoom · right-drag to pan';
				},
				( progress ) => {
					if ( progress.total ) {
						status.textContent = `Loading model… ${ Math.round( ( progress.loaded / progress.total ) * 100 ) }%`;
					}
				},
				() => { status.textContent = 'Could not load the IFC file (check the URL and CORS).'; }
			);

			( function animate() {
				requestAnimationFrame( animate );
				controls.update();
				renderer.render( scene, camera );
			} )();

			new ResizeObserver( () => {
				camera.aspect = host.clientWidth / host.clientHeight;
				camera.updateProjectionMatrix();
				renderer.setSize( host.clientWidth, host.clientHeight );
			} ).observe( host );
		} catch ( error ) {
			status.textContent = '3D viewer unavailable.';
			host.innerHTML = `<div class="p-4 text-center text-light">
				<p>The in-browser viewer could not start (${ EM.tpl.esc( error.message ) }).</p>
				<a class="btn btn-outline-light btn-sm" href="${ EM.tpl.esc( record.ifc_url ) }" target="_blank" rel="noopener">Download IFC file</a>
			</div>`;
		}
	}

	EM.registerModule( 'bim-models', { view } );
} )( window.EM );
