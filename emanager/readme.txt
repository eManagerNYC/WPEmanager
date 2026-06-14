=== eManager — Construction Management Dashboard ===
Contributors: emanager
Tags: construction, project management, dashboard, change orders, workflow
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 3.9.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Run a whole construction project in WordPress: RFIs, submittals, change orders, costs (G702/G703), safety, scheduling, closeout — 100+ modules.

== Description ==

eManager turns a WordPress site into a complete construction-management dashboard for a building project. It connects the general contractor, owner, owner's representative, design consultants and subcontractors on one site and runs the day-to-day processes of a jobsite — from the first bid package through design coordination, field work, cost control, safety and final handover.

**What it does, on a real jobsite**

* The **estimating/preconstruction** team prequalifies bidders, sends invitations to bid, levels the bids and locks the budget.
* **Design & engineering** logs RFIs, submittals, drawings, specs, design reviews and a long-lead procurement schedule.
* In the **field**, superintendents file daily reports (with automatic site weather), manpower/delivery/visitor logs, photos, a punch list and a Gantt or Line-of-Balance schedule; crews log timesheets and installed quantities.
* **Quality & safety** captures inspections, non-conformance reports, incidents, toolbox talks and observations — with drawn signatures.
* When something changes, the **change-order workflow** carries it from a field PCO request → owner notification → directive → subcontractor pricing → change-order/approval letter → time-and-material tickets, each step gated by the acting party's role and written to an activity timeline.
* The **cost** tools track the budget, commitments, change events, direct costs and invoices, roll them into a live budget-vs-committed-vs-actual-vs-forecast summary, and produce G702/G703 payment applications as PDFs.
* At the end, **closeout** handles commissioning, as-builts, owner training, completion certificates and the asset register for handover.

Every business process is a module described by one `module.json` file. A shared engine renders sortable/filterable lists, validated forms, and record views with comments, electronic signatures and PDF export — so the platform is lightweight and you can add your own modules without writing PHP.

**Trademarks & standards**

This plugin is an independent project and is not affiliated with, endorsed by, or sponsored by any third party. Product and standard names are used only to describe compatibility or format: AIA® and G702®/G703® are trademarks of The American Institute of Architects; LEED® is a trademark of the U.S. Green Building Council. eManager does not include or distribute those organizations' official documents.

**The change-order workflow (role-gated logic gates)**

Following the construction process, work flows through linked records, each step gated by the acting party's role:

* **PCO Request → NOC** — field/GC raises a Potential Change Order; once reviewed it is issued as a Notification of Change to the owner, who returns it with a direction (Proceed & Pricing, Pricing Only, or Do Not Proceed).
* **Supplemental Scope Info (SSI) → SSID** — consultant/owner-originated scope routed through owner review, contract assignment, PCO# & funding, manager review and issuance.
* **Scope / PCO Directives** — issued to subcontractors; spawn a Proposal (Pricing Only) or an eTicket (Proceed & Pricing).
* **Subcontractor Proposals → Change Order Request (COR/AL)** — pricing submitted, reconciled, bundled into a COR/Approval Letter, reviewed and approved in-app by the owner.
* **eTickets (T&M)** — subcontractors build tickets against their own labor/material/equipment rates with automatic totals; signed by the superintendent then the GC.
* **Daily Construction Reports (DCR)** and **RFIs** — completed in the field and routed for review/answer.

Every transition is written to an activity timeline — the electronic replacement for paper routing slips.

**Features**

* 100+ built-in modules across 14 sections covering the full project lifecycle: preconstruction & procurement, design/PM, quality, safety, field productivity, full cost model, sustainability, and closeout & handover
* G702/G703-format pay applications: Schedule of Values + an Application & Certificate for Payment builder with formatted PDF export
* Cost Summary financial roll-up dashboard: budget vs. committed vs. actual vs. forecast with projected over/under
* Scheduling visuals: Gantt chart and an Empire State Building-style Line-of-Balance (location-based/takt) chart, plus just-in-time Site Logistics and a long-lead Procurement Log
* Two role dimensions: five CRUD capability roles (Administrator/Editor/Contributor/Viewer/Restricted) plus five project "party" roles (GC, Owner, Owner's Rep, Consultant, Subcontractor) that drive workflow logic gates and tailored dashboards
* Server-enforced workflow state machine with role-gated transitions and a full audit trail
* One-click record linking that carries data forward across the change-order chain
* Stored entirely in the WordPress database — one indexed table per module, built to scale to tens of thousands of records; no external services or accounts
* Comments and PDF export on every record; CSV export on every list
* Electronic signature capture (draw on canvas / touch) on signature-enabled forms
* eTicket builder with automatic labor/material/equipment totals from the project rate tables
* Daily Reports with automatic site weather (Open-Meteo)
* Reports module with statistics charts and PDF/CSV export
* 3D BIM viewer for .IFC model files
* Add your own modules as ZIP packages — no PHP required; the database table is created automatically
* Front-end login and registration; each user is assigned a company and party role

**No external database or accounts required.** All data lives in custom tables in your WordPress database, created automatically on activation.

== Installation ==

1. Upload the plugin ZIP via Plugins → Add New → Upload, then activate. All database tables are created automatically.
2. Go to **eManager → Settings** and fill in the project information (name, number, address, coordinates for weather).
3. Assign each user an eManager role, a **party role** (GC, Owner, Rep, Consultant, Subcontractor) and a company under **eManager → Users**, and manage companies under **eManager → Companies**.
4. Open the auto-created *eManager Dashboard* page.

== Frequently Asked Questions ==

= Where is my data stored? =

In your WordPress site's own database. eManager creates one custom table per module (prefixed like the rest of WordPress, e.g. wp_em_rfis) plus shared tables for companies, comments and the activity log. Uninstalling the plugin keeps these tables by default; to drop them, define EM_DROP_TABLES_ON_UNINSTALL as true in wp-config.php before uninstalling.

= How do I add my own module? =

Create a folder with a `module.json` manifest describing the fields and statuses, zip it, and upload it under eManager → Modules. Its database table is created automatically. See `docs/MODULE-DEVELOPMENT.md` in the plugin folder for the full guide. Module packages may not contain PHP.

= Are electronic signatures legally binding? =

eManager captures drawn signatures with a timestamp and the signing user's identity, which suits day-to-day field workflows (pre-task plans, T&M tickets, orientations, lien waiver routing). It is not a qualified e-signature service (ESIGN/eIDAS certification, audit trails, certificates). For legally critical executions, use a dedicated e-signature provider and attach the executed document URL to the record.

= Which external services does the plugin call? =

Data never leaves your server. The only optional outbound call is the Open-Meteo weather API, server-side, and only when a user fetches site weather in Daily Reports (no API key, no personal data sent). The optional BIM viewer loads the three.js/web-ifc 3D engine in the browser from the jsDelivr CDN only when a user opens a 3D model record.

== Screenshots ==

1. Dashboard home with section overview
2. Module list with sorting, filtering and exports
3. Record view with comments and PDF export
4. Daily Report form with weather autofill and signature pad
5. Reports module with project statistics

== Changelog ==

= 3.9.0 =
* Bespoke build-out of Resources & BIM (final section batch): Coordination Issues now spawn an RFI when design input is needed; 3D Models gain revision control (Current → Superseded → Archived); rate tables (Labor/Material/Equipment) support versioning (Active → Superseded) and Locations/CSI Divisions/Cost Codes get active/inactive workflows. All 14 sections are now bespoke — role-gated workflows with cross-module linking throughout.

= 3.8.0 =
* Bespoke build-out of Preconstruction/Procurement: Qualified Bidders & Bid Manual (workflows); Bid Packages → issue Invitation to Bid; Bid Solicitations → add bidders; Bid Submissions (leveling pipeline with low/high/spread KPIs) → award converts to a subcontract Commitment; Estimates mature by phase and seed a Budget line (estimate→budget hand-off)

= 3.7.0 =
* Bespoke build-out of Closeout/Handover: O&M Manuals, As-Builts, Attic Stock and Owner Training (ball-in-court / submit workflows); Completion Certificates now start a Warranty (period-begin trigger); Commissioning issues spawn a Deficiency or Action Item; Asset Register registers Warranties; Warranties list shows active / expiring-≤90-days / expired KPIs

= 3.6.0 =
* Bespoke build-out of Cost/Financials: Potential Changes (Identified→Pricing→Submitted→Approved→Converted, one-click convert to Change Order), Change Orders (owner-approval workflow, budget cost-code), Direct Costs (approve→pay workflow, coded to budget line), Approval Letters and T&M Tickets (workflows), Commitments (coded to budget line, bill-against-commitment → Subcontractor Invoice)

= 3.5.0 =
* Bespoke build-out of Quality & Safety: Inspections (failed finding → Deficiency or NCR, pass-rate KPIs), Non-Conformance Reports (disposition Rework/Repair/Use-as-is/Scrap → corrective + preventive action → verification, corrective-action hand-off), Deficiencies (ball-in-court rework loop), Test Records (pass/fail/retest workflow), Incidents (OSHA recordable/lost-time fields, RCA, corrective-action hand-off, safety KPIs), Safety Violations & Observations (workflows; at-risk observation → corrective action)

= 3.4.0 =
* Bespoke build-out of the Field section: Daily Reports (submit → approve workflow, expanded sub-logs), Punch List (Procore-style ball-in-court: Open → Work Required → Ready for Review → Verified, with priority, backcharge and open/overdue KPIs), Checklists (issue → complete → review workflow), Photo Library (thumbnail gallery view with album/tag search)
* Added a workflow-diagram section to the README and an IMPROVEMENT-PLAN roadmap
* Fix: rebuilding tables (and the admin "Rebuild tables" action) now re-scans modules from disk so newly-added module fields are reliably created on upgrade

= 3.3.0 =
* Built out the Engineering / document-control modules into full bespoke workflows (grounded in industry RFI and CSI submittal practice), matching the depth of the change-order chain:
* RFIs: ball-in-court lifecycle (Draft → Open → Answered → Closed), discipline/priority, cost & schedule impact, distribution, and one-click "raise Change Event" linking; list shows open/overdue/avg-age KPIs
* Submittals: subcontractor → GC → A/E review with dispositions (Approved / Approved as Noted / Revise & Resubmit / Rejected), spec section, type, and required-on-site aging KPIs
* Drawings & Specifications: revision-control workflow (Current → Superseded) with issued-set tracking
* Permitting, Meetings (agenda → minutes, spawn action items) and Transmittals: role-gated workflows
* Fix (upgrade safety): module tables now reliably gain newly-added fields on upgrade via an explicit column-sync (dbDelta does not add backticked columns); object caching added to single-row reads

= 3.2.1 =
* WordPress.org submission readiness: passes Plugin Check with zero errors; bundled all libraries locally; gated the optional IFC 3D viewer off by default; added LICENSE.txt and a bundled-library manifest; fixed plugin headers (Plugin URI, Tested up to)

= 3.2.0 =
* AIA G702/G703: new Schedule of Values module (auto-computed continuation-sheet columns) and a Pay Applications builder that rolls the SOV into a G702 Application & Certificate for Payment (retainage, previous certificates, current payment due, balance to finish) with a formatted G702 + G703 PDF export
* Empire State Building-inspired scheduling: a Linear/Takt Schedule with a Line-of-Balance chart (location vs. time), a Gantt chart on the Schedule module, just-in-time Site Logistics (crane/hoist/laydown booking) and a long-lead Procurement Log
* Performance: the module registry is now cached in a transient instead of re-scanning 100+ module.json files on every request (auto-invalidated on activation, module install/uninstall and Rebuild tables)
* Fix: tightened the records PUT/PATCH route method declaration

= 3.1.0 =
* Expanded to 90+ modules across 14 sections for full project-lifecycle coverage
* Preconstruction: Prequalification, Bid Solicitations (ITB), Bid Submissions (leveling), Estimates, Value Engineering
* New Quality section: Inspections, Non-Conformance Reports, Deficiencies, Test Records
* Safety: Incidents (OSHA), Safety Violations, Toolbox Talks
* Field productivity: Timesheets, Crews, Production Quantities, Manpower/Delivery/Visitor/Equipment logs
* Full cost model: Commitments (POs), Change Events, Owner Invoices, Subcontractor Invoices, Funding Sources, Allowances, Contingency Log — plus a Cost Summary roll-up dashboard
* Engineering: Issues, Action Items, Correspondence, Design Reviews
* New Sustainability section: LEED/Green Credits, Waste Diversion, Environmental Monitoring
* Closeout/handover: Commissioning, As-Builts, Owner Training, Completion Certificates, Asset Register

= 3.0.0 =
* Replaced the Supabase backend with the native WordPress database: one indexed custom table per module (plus shared tables for companies, comments and the activity log), created automatically via dbDelta
* No external services, accounts or connection setup — data never leaves your server
* Tables are indexed (status, created_at, created_by, project+status, linked records, and every list column) to scale to tens of thousands of records per module; reports use a single indexed GROUP BY per table
* Added eManager → Settings → Rebuild tables (re-runs dbDelta after upgrades/changes, never drops data)
* Removed the Supabase connection settings, service-role key storage and connection test

= 2.0.0 =
* Renamed the project to eManager (general-contracting portal for mega projects)
* Added the Change Management section with the full patent change-order workflow: PCO Requests, Notifications of Change, Supplemental Scope Info, Scope/PCO Directives, Subcontractor Proposals, Change Order Requests, eTickets and Daily Construction Reports
* Added a server-enforced workflow state machine with role-gated transitions, direction handling (P&P/PO/DNP) and a full activity audit trail (em_activity)
* Added project "party" roles (GC, Owner, Rep, Consultant, Subcontractor) that drive workflow logic gates and role-tailored dashboards
* Added one-click record linking that carries data forward across the change-order chain
* Added an eTicket T&M builder with automatic labor/material/equipment totals from the project rate tables

= 1.1.0 =
* Added electronic signature field type (canvas pad, PNG embedded in PDF exports)
* Bundled all front-end libraries locally (no CDN) per WordPress.org guidelines
* WordPress Coding Standards compliance pass (PHPCS WordPress ruleset clean)

= 1.0.0 =
* Initial release: 47 modules, role system, config-driven CRUD engine, comments, PDF/CSV export, ZIP module installer

== Upgrade Notice ==

= 3.0.0 =
Major change: data now lives in the WordPress database instead of Supabase. Tables are created automatically on update. This is a backend replacement — records stored in a previous Supabase-backed install are not migrated automatically.

= 2.0.0 =
Adds the change-order workflow, party roles and activity log.
