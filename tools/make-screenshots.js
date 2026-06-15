/**
 * Builds the eManager wordpress.org screenshot assets.
 *
 * Renders five full-page HTML reproductions of the dashboard using the plugin's
 * OWN bundled Bootstrap 5 + Bootstrap Icons + Chart.js and emanager.css, then a
 * companion shell command rasterises each to assets/screenshot-N.png with
 * headless Chrome. Run:  node tools/make-screenshots.js
 *
 * These are representative renders of the real UI components and real module /
 * field names (sample project data) — swap in live captures any time.
 */

const fs = require( 'fs' );
const path = require( 'path' );

const ROOT = path.resolve( __dirname, '..' );
const OUT = path.join( ROOT, 'tools', 'screenshots-build' );
fs.mkdirSync( OUT, { recursive: true } );

const V = 'file:///' + path.join( ROOT, 'emanager', 'public', 'vendor' ).replace( /\\/g, '/' );
const CSSDIR = 'file:///' + path.join( ROOT, 'emanager', 'public', 'css' ).replace( /\\/g, '/' );

const HEAD = `<!doctype html><html lang="en" data-bs-theme="light"><head><meta charset="utf-8">
<link rel="stylesheet" href="${ V }/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="${ V }/bootstrap-icons/bootstrap-icons.min.css">
<link rel="stylesheet" href="${ CSSDIR }/emanager.css">
<style>
  body{background:#f6f7f9}
  .em-sidebar{background:#1f2937}
  .em-sidebar .em-section{color:#9aa4b2;font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding:.55rem .9rem .25rem}
  .em-sidebrand{color:#fff}
  .signature-box{font-family:'Segoe Script','Brush Script MT',cursive;font-size:2rem;color:#1d3b8b}
  .kpi .display-6{font-weight:700}
</style></head><body>`;

const SECTIONS = [
	[ 'bi-clipboard-data', 'Preconstruction', [ 'Qualified Bidders', 'Bid Packages', 'Bid Submissions', 'Estimates' ] ],
	[ 'bi-rulers', 'Engineering', [ 'RFIs', 'Submittals', 'Drawings & Specs', 'Meetings' ] ],
	[ 'bi-arrow-left-right', 'Change Management', [ 'PCO Requests', 'Notifications of Change', 'Directives', 'Change Order Requests', 'eTickets' ] ],
	[ 'bi-cone-striped', 'Field', [ 'Daily Reports', 'Punch List', 'Timesheets', 'Photo Library' ] ],
	[ 'bi-patch-check', 'Quality', [ 'Inspections', 'Non-Conformance', 'Deficiencies' ] ],
	[ 'bi-shield-check', 'Safety', [ 'Incidents', 'Toolbox Talks', 'Observations' ] ],
	[ 'bi-currency-dollar', 'Cost', [ 'Cost Summary', 'Schedule of Values', 'Pay Applications', 'Commitments' ] ],
	[ 'bi-journal-check', 'Closeout', [ 'Commissioning', 'Warranties', 'Completion Certs' ] ],
];

function badge( status ) {
	const s = status.toLowerCase();
	let tone = 'secondary';
	if ( /(approved|closed|complete|paid|passed|active|executed|answered|received)/.test( s ) ) tone = 'success';
	else if ( /(pending|open|review|submitted|in progress|draft|forecast)/.test( s ) ) tone = 'warning';
	else if ( /(rejected|void|failed|overdue|deficient|denied)/.test( s ) ) tone = 'danger';
	return `<span class="badge text-bg-${ tone } em-status">${ status }</span>`;
}

function sidebar( active ) {
	const items = SECTIONS.map( ( [ icon, name, mods ] ) => {
		const links = mods.map( ( m ) => {
			const on = ( name + ' / ' + m ) === active;
			return `<a class="nav-link ${ on ? 'active' : '' }" href="#"><i class="bi bi-dot"></i>${ m }</a>`;
		} ).join( '' );
		return `<div class="em-section"><i class="bi ${ icon } me-1"></i>${ name }</div><nav class="nav flex-column">${ links }</nav>`;
	} ).join( '' );
	return `<aside class="em-sidebar"><div class="p-2">${ items }</div></aside>`;
}

function shell( { active, crumbSection, crumbPage, actions, main } ) {
	const crumb = `<ol class="breadcrumb mb-0 me-auto">
		<li class="breadcrumb-item"><a href="#"><i class="bi bi-house-door"></i> Home</a></li>
		${ crumbSection ? `<li class="breadcrumb-item">${ crumbSection }</li>` : '' }
		${ crumbPage ? `<li class="breadcrumb-item active" aria-current="page">${ crumbPage }</li>` : '' }
	</ol>`;
	return HEAD + `<div class="em-app">
	<header class="em-header navbar navbar-expand bg-dark border-bottom border-secondary-subtle" data-bs-theme="dark">
		<div class="container-fluid">
			<a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
				<i class="bi bi-buildings"></i><span>eManager</span>
				<small class="text-secondary fw-normal d-none d-md-inline">Tower at Hudson Yards</small>
			</a>
			<div class="ms-auto d-flex align-items-center gap-3">
				<div class="input-group input-group-sm em-global-search">
					<span class="input-group-text"><i class="bi bi-search"></i></span>
					<input type="search" class="form-control" placeholder="Search current module…">
				</div>
				<button class="btn btn-outline-light btn-sm dropdown-toggle" type="button"><i class="bi bi-person-circle me-1"></i>Sarah Chen</button>
			</div>
		</div>
	</header>
	${ sidebar( active ) }
	<nav class="em-navbar bg-body-tertiary border-bottom px-3 py-2 d-flex align-items-center flex-wrap gap-2">
		${ crumb }
		<div class="d-flex gap-2">${ actions || '' }</div>
	</nav>
	<main class="em-main">${ main }</main>
	<footer class="em-footer bg-body-tertiary border-top px-3 py-2 d-flex flex-wrap align-items-center gap-2 small text-secondary">
		<span>eManager <span>3.19.1</span></span><span class="d-none d-sm-inline">·</span>
		<span class="d-none d-sm-inline">Tower at Hudson Yards · #HY-4471</span>
	</footer>
	</div></body></html>`;
}

/* ---- Screen 1: Home dashboard ---- */
const courtRows = [
	[ 'RFIs', 'RFI-014 — Curtain wall embed conflict at L12', 'Answered' ],
	[ 'Submittals', 'SUB-082 — Structural steel shop drawings', 'A/E Review' ],
	[ 'Change Order Requests', 'COR-009 — Added foundation dewatering', 'Submitted to Owner' ],
].map( ( [ m, t, s ] ) => `<a class="list-group-item list-group-item-action d-flex align-items-center gap-2" href="#">
	<span class="badge text-bg-light">${ m }</span><span class="flex-grow-1 text-truncate">${ t }</span>${ badge( s ) }</a>` ).join( '' );

const roleChips = [ 'PCO Requests', 'Notifications of Change', 'Directives', 'Proposals', 'Change Order Requests', 'eTickets', 'DCRs', 'RFIs' ]
	.map( ( c ) => `<a class="btn btn-sm btn-outline-primary" href="#"><i class="bi bi-arrow-left-right me-1"></i>${ c }</a>` ).join( '' );

const sectionCards = SECTIONS.map( ( [ icon, name, mods ] ) => `
	<div class="col-sm-6 col-lg-4 col-xxl-3">
		<div class="card h-100">
			<div class="card-header d-flex align-items-center gap-2"><i class="bi ${ icon }"></i><strong>${ name }</strong></div>
			<ul class="list-group list-group-flush">
				${ mods.map( ( m ) => `<li class="list-group-item p-0"><a class="d-flex align-items-center gap-2 px-3 py-2 text-body" href="#"><i class="bi bi-dot text-secondary"></i>${ m }</a></li>` ).join( '' ) }
			</ul>
		</div>
	</div>` ).join( '' );

const home = `<div class="em-home">
	<div class="row g-3 mb-3"><div class="col-12">
		<div class="card border-0 bg-primary text-white"><div class="card-body">
			<h2 class="h4 mb-1">Tower at Hudson Yards</h2>
			<p class="mb-0 opacity-75">#HY-4471  ·  500 W 33rd St, New York, NY  ·  Owner: Related Companies</p>
		</div></div></div></div>
	<div class="mb-3"><div class="card border-0 shadow-sm"><div class="card-body">
		<div class="d-flex align-items-center gap-2 mb-2"><span class="badge text-bg-primary">General Contractor</span><span class="text-secondary small">Your change-management workspace</span></div>
		<div class="d-flex flex-wrap gap-2">${ roleChips }</div>
	</div></div></div>
	<div class="row g-3">
		<div class="col-12"><div class="card mb-1">
			<div class="card-header bg-body d-flex align-items-center gap-2"><i class="bi bi-inbox"></i><strong>In my court</strong><span class="badge text-bg-secondary">3</span></div>
			<div class="list-group list-group-flush">${ courtRows }</div>
		</div></div>
		${ sectionCards }
	</div>
</div>`;

/* ---- Screen 2: Module list (RFIs) ---- */
const rfiRows = [
	[ 'RFI-014', 'Curtain wall embed conflict at L12', 'Structural', 'Consultant', 'Answered', 'Jun 12' ],
	[ 'RFI-013', 'Mechanical duct vs. sprinkler main, L8 corridor', 'MEP', 'GC', 'Open', 'Jun 18' ],
	[ 'RFI-012', 'Door hardware set 7 — spec clarification', 'Architectural', 'Consultant', 'Open', 'Jun 16' ],
	[ 'RFI-011', 'Rebar congestion at core wall C3', 'Structural', 'GC', 'Closed', 'Jun 05' ],
	[ 'RFI-010', 'Slab edge detail at curtain wall', 'Architectural', 'Consultant', 'Answered', 'Jun 02' ],
	[ 'RFI-009', 'Elevator pit waterproofing transition', 'Architectural', 'GC', 'Overdue', 'May 28' ],
].map( ( [ n, s, d, b, st, dr ] ) => `<tr>
	<td><input class="form-check-input" type="checkbox"></td>
	<td class="fw-semibold">${ n }</td><td>${ s }</td><td>${ d }</td><td>${ b }</td><td>${ badge( st ) }</td><td>${ dr }</td></tr>` ).join( '' );

const list = `<div class="d-flex align-items-center flex-wrap gap-2 mb-3">
		<select class="form-select form-select-sm" style="width:auto"><option>All statuses</option><option>Open</option><option>Answered</option><option>Closed</option></select>
		<div class="input-group input-group-sm" style="width:240px"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" placeholder="Search RFIs…"></div>
		<span class="text-secondary small ms-1">6 of 14 records</span>
	</div>
	<div class="alert alert-light border d-flex align-items-center gap-2 py-2 d-none"></div>
	<div class="table-responsive"><table class="table table-hover align-middle em-table">
		<thead><tr>
			<th style="width:32px"><input class="form-check-input" type="checkbox"></th>
			<th data-sort class="sorted-asc">RFI #</th><th data-sort>Subject</th><th data-sort>Discipline</th>
			<th data-sort>Ball in court</th><th data-sort>Status</th><th data-sort>Date required</th>
		</tr></thead><tbody>${ rfiRows }</tbody></table></div>
	<div class="d-flex justify-content-between align-items-center">
		<small class="text-secondary">Page 1 of 3</small>
		<ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><span class="page-link">«</span></li>
			<li class="page-item active"><span class="page-link">1</span></li><li class="page-item"><a class="page-link" href="#">2</a></li>
			<li class="page-item"><a class="page-link" href="#">3</a></li><li class="page-item"><a class="page-link" href="#">»</a></li></ul>
	</div>`;

const listActions = `<button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>New RFI</button>
	<button class="btn btn-sm btn-outline-secondary dropdown-toggle"><i class="bi bi-bookmark me-1"></i>Views</button>
	<button class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv me-1"></i>Export</button>`;

/* ---- Screen 3: Record view (RFI-014) ---- */
const wfmap = `<div class="em-wf-map mb-3">
	<span class="em-wf-step em-wf-done">Draft</span><span class="em-wf-sep">›</span>
	<span class="em-wf-step em-wf-done">Open</span><span class="em-wf-sep">›</span>
	<span class="em-wf-step em-wf-current">Answered</span><span class="em-wf-sep">›</span>
	<span class="em-wf-step em-wf-todo">Closed</span></div>`;

const view = `<div class="row g-3 em-view em-workflow">
	<div class="col-lg-7">
		<div class="card mb-3"><div class="card-header d-flex align-items-center justify-content-between">
			<strong>RFI-014 — Curtain wall embed conflict at L12</strong>${ badge( 'Answered' ) }</div>
			<div class="card-body"><dl class="row mb-0">
				<dt class="col-sm-4">Discipline</dt><dd class="col-sm-8">Structural</dd>
				<dt class="col-sm-4">Priority</dt><dd class="col-sm-8">High</dd>
				<dt class="col-sm-4">Ball in court</dt><dd class="col-sm-8">Consultant — Thornton Tomasetti</dd>
				<dt class="col-sm-4">Cost impact</dt><dd class="col-sm-8">Potential — TBD</dd>
				<dt class="col-sm-4">Schedule impact</dt><dd class="col-sm-8">2 days</dd>
				<dt class="col-sm-4">Question</dt><dd class="col-sm-8">Embed plate E-12 clashes with post-tension tendon at gridline F/4. Please advise relocation or alternate detail.</dd>
				<dt class="col-sm-4">Answer</dt><dd class="col-sm-8">Relocate embed 6&quot; east per sketch SK-S-114; tendon profile unaffected.</dd>
			</dl></div></div>
		<div class="card mb-3"><div class="card-header"><i class="bi bi-diagram-3 me-1"></i>Workflow</div><div class="card-body">
			${ wfmap }
			<div class="d-flex flex-wrap gap-2"><button class="btn btn-sm btn-primary">Accept &amp; close</button>
				<button class="btn btn-sm btn-outline-secondary">Reopen / clarify</button>
				<button class="btn btn-sm btn-outline-primary"><i class="bi bi-diagram-2 me-1"></i>Raise Change Event</button></div>
		</div></div>
		<div class="card"><div class="card-header"><i class="bi bi-link-45deg me-1"></i>Related records</div>
			<div class="card-body d-flex flex-wrap gap-2">
				<a class="btn btn-sm btn-outline-secondary" href="#"><i class="bi bi-arrow-up-right me-1"></i>PCO-007 (parent)</a>
				<a class="btn btn-sm btn-outline-secondary" href="#"><i class="bi bi-arrow-down-right me-1"></i>CE-004 Change Event</a>
			</div></div>
	</div>
	<div class="col-lg-5">
		<div class="card mb-3"><div class="card-header d-flex justify-content-between align-items-center"><span><i class="bi bi-clock-history me-1"></i>Activity</span>
			<button class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-pdf me-1"></i>PDF</button></div>
			<div class="card-body"><ul class="em-timeline">
				<li class="em-timeline-item"><div class="small text-secondary">Jun 12, 2026 · 4:02 PM</div><div>Sarah Chen answered the RFI <span class="text-secondary">(Open → Answered)</span></div></li>
				<li class="em-timeline-item"><div class="small text-secondary">Jun 10, 2026 · 9:15 AM</div><div>Mark Reyes forwarded to consultant</div></li>
				<li class="em-timeline-item"><div class="small text-secondary">Jun 09, 2026 · 2:40 PM</div><div>Sarah Chen submitted the RFI <span class="text-secondary">(Draft → Open)</span></div></li>
				<li class="em-timeline-item"><div class="small text-secondary">Jun 09, 2026 · 2:31 PM</div><div>Sarah Chen created the record</div></li>
			</ul></div></div>
		<div class="card em-comments"><div class="card-header"><i class="bi bi-chat-left-text me-1"></i>Comments</div><div class="card-body">
			<div class="em-comment"><div class="small text-secondary">Mark Reyes · Jun 11</div>Field is holding steel erection at L12 until this is resolved.</div>
			<div class="em-comment"><div class="small text-secondary">Sarah Chen · Jun 12</div>Answer received — releasing for fabrication.</div>
			<div class="input-group input-group-sm mt-2"><input class="form-control" placeholder="Add a comment…"><button class="btn btn-primary">Post</button></div>
		</div></div>
	</div>
</div>`;

/* ---- Screen 4: Daily Report form ---- */
const form = `<div style="max-width:820px"><div class="card"><div class="card-header"><strong>New Daily Construction Report</strong></div>
	<div class="card-body"><form class="row g-3">
		<div class="col-md-4"><label class="form-label">Report date</label><input class="form-control" value="2026-06-15"></div>
		<div class="col-md-8"><label class="form-label">Weather <span class="text-secondary small">(auto-filled)</span></label>
			<div class="input-group"><input class="form-control" value="72°F · Partly cloudy · Wind 8 mph NW">
				<button class="btn btn-outline-secondary" type="button"><i class="bi bi-cloud-sun me-1"></i>Fetch weather</button></div></div>
		<div class="col-12"><label class="form-label">Work performed</label>
			<textarea class="form-control" rows="3">L8–L10 deck pour complete (220 cy). Curtain wall install ongoing east face. MEP rough-in L6. Site logistics: tower crane TC-2 down 1 hr for inspection.</textarea></div>
		<div class="col-12"><label class="form-label">Manpower</label>
			<table class="table table-sm align-middle mb-0"><thead><tr><th>Trade</th><th>Company</th><th class="text-end">Workers</th><th class="text-end">Hours</th></tr></thead>
			<tbody><tr><td>Concrete</td><td>Navillus</td><td class="text-end">18</td><td class="text-end">144</td></tr>
				<tr><td>Curtain wall</td><td>Permasteelisa</td><td class="text-end">12</td><td class="text-end">96</td></tr>
				<tr><td>MEP</td><td>WDF / Five Star</td><td class="text-end">22</td><td class="text-end">176</td></tr></tbody></table></div>
		<div class="col-md-6"><label class="form-label">Superintendent signature</label>
			<div class="em-signature border rounded d-flex align-items-center justify-content-center" style="height:96px;background:#fff">
				<span class="signature-box">Sarah Chen</span></div>
			<div class="small text-secondary mt-1">Signed Jun 15, 2026 · 5:48 PM</div></div>
		<div class="col-md-6 d-flex align-items-end"><label class="form-label me-2 mb-0">Photos</label>
			<span class="badge text-bg-light"><i class="bi bi-image me-1"></i>4 attached</span></div>
		<div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="button"><i class="bi bi-send me-1"></i>Submit report</button>
			<button class="btn btn-outline-secondary" type="button">Save draft</button></div>
	</form></div></div></div>`;

/* ---- Screen 5: Reports / statistics ---- */
const kpis = [
	[ 'Open RFIs', '7', 'bi-question-circle', 'primary' ],
	[ 'Pending submittals', '12', 'bi-folder', 'warning' ],
	[ 'Approved CORs', '$1.84M', 'bi-cash-stack', 'success' ],
	[ 'Safety incidents (YTD)', '2', 'bi-shield-exclamation', 'danger' ],
].map( ( [ l, v, i, t ] ) => `<div class="col-sm-6 col-xl-3"><div class="card kpi h-100"><div class="card-body">
	<div class="d-flex align-items-center gap-2 text-${ t }"><i class="bi ${ i }"></i><span class="small text-secondary">${ l }</span></div>
	<div class="display-6 mt-1">${ v }</div></div></div></div>` ).join( '' );

const reports = `<div class="row g-3 mb-3">${ kpis }</div>
	<div class="row g-3">
		<div class="col-lg-7"><div class="card h-100"><div class="card-header"><i class="bi bi-bar-chart me-1"></i>Records by status</div>
			<div class="card-body"><canvas id="c1" height="150"></canvas></div></div></div>
		<div class="col-lg-5"><div class="card h-100"><div class="card-header"><i class="bi bi-pie-chart me-1"></i>Cost roll-up</div>
			<div class="card-body d-flex justify-content-center"><canvas id="c2" height="150"></canvas></div></div></div>
	</div>
	<script src="${ V }/chartjs/chart.umd.js"></script>
	<script>
	const noAnim={animation:false,responsive:true,plugins:{legend:{position:'bottom'}}};
	new Chart(document.getElementById('c1'),{type:'bar',data:{labels:['RFIs','Submittals','PCOs','Change Orders','Inspections','NCRs'],
		datasets:[{label:'Open',data:[7,12,5,4,9,3],backgroundColor:'#ffc107'},{label:'Closed',data:[32,48,18,14,61,11],backgroundColor:'#198754'}]},
		options:Object.assign({scales:{x:{stacked:true},y:{stacked:true}}},noAnim)});
	new Chart(document.getElementById('c2'),{type:'doughnut',data:{labels:['Spent','Committed','Remaining'],
		datasets:[{data:[28.4,41.2,30.4],backgroundColor:['#0d6efd','#6610f2','#dee2e6']}]},options:noAnim});
	</script>`;

const SCREENS = {
	'screenshot-1': shell( { active: '', main: home } ),
	'screenshot-2': shell( { active: 'Engineering / RFIs', crumbSection: 'Engineering', crumbPage: 'RFIs', actions: listActions, main: list } ),
	'screenshot-3': shell( { active: 'Engineering / RFIs', crumbSection: 'Engineering', crumbPage: 'RFI-014', main: view } ),
	'screenshot-4': shell( { active: 'Field / Daily Reports', crumbSection: 'Field', crumbPage: 'New', main: form } ),
	'screenshot-5': shell( { active: '', crumbSection: 'Reports', crumbPage: 'Project Statistics', main: reports } ),
};

for ( const [ name, html ] of Object.entries( SCREENS ) ) {
	fs.writeFileSync( path.join( OUT, name + '.html' ), html );
}
console.log( 'Wrote ' + Object.keys( SCREENS ).length + ' HTML files to ' + OUT );
