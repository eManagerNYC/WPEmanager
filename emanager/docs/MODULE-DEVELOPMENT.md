# eManager — Module Development Guide

A eManager module is a **folder with a `module.json` manifest**. No PHP, no build step.
The shared engine gives every module a sortable/filterable list page, a record view with
comments and PDF export, and a validated Bootstrap form — all generated from the manifest.

## 1. Anatomy of a module

```
my-module/
├── module.json     REQUIRED — everything the engine needs
├── list.html       optional — overrides modules/_defaults/list.html
├── view.html       optional — overrides modules/_defaults/view.html
├── form.html       optional — overrides modules/_defaults/form.html
├── module.js       optional — custom behavior (loaded when the module is opened)
└── module.css      optional — module-scoped styles (loaded with module.js)
```

Only `.json`, `.html`, `.js`, `.css`, `.md`, `.sql` and image files are allowed in module
packages — **PHP is rejected** by the installer for security.

## 2. module.json reference

```json
{
	"id": "equipment-log",
	"name": "Equipment Log",
	"section": "field",
	"icon": "bi-truck",
	"table": "em_equipment_log",
	"version": "1.0.0",
	"statuses": [ "On Site", "In Use", "Down", "Off Rented" ],
	"fields": [
		{ "name": "equipment", "label": "Equipment", "type": "text", "required": true, "list": true },
		{ "name": "vendor", "label": "Vendor", "type": "text", "list": true },
		{ "name": "arrival", "label": "Arrived on site", "type": "date", "list": true },
		{ "name": "rate", "label": "Monthly rate", "type": "currency" },
		{ "name": "cost_code", "label": "Cost code", "type": "select",
		  "lookup": { "module": "cost-codes", "value": "code", "label": "title" } },
		{ "name": "notes", "label": "Notes", "type": "textarea" }
	]
}
```

| Key | Required | Notes |
|---|---|---|
| `id` | ✔ | Unique slug (lowercase, hyphens). Routes become `#/<section>/<id>`. |
| `name` | ✔ | Shown in the sidebar, lists, PDFs. |
| `section` | ✔ | One of the ids in `modules/sections.json` — or any new slug (a section is created automatically). |
| `icon` | – | A [Bootstrap Icons](https://icons.getbootstrap.com) class, e.g. `bi-truck`. |
| `table` | ✔ | Logical table name (the WordPress prefix is added automatically). Convention: `em_<id with underscores>`. |
| `version` | – | Required to upgrade an already-installed module via ZIP. |
| `statuses` | – | Ordered list. The **first status is applied to new records**; lists get a status filter; the view shows a colored badge. |
| `fields` | ✔ | Array of field objects (below). The **first field is used as the record title**. |

### Field object

| Key | Notes |
|---|---|
| `name` | Column name in the module table (snake_case). |
| `label` | Form/list label. |
| `type` | `text` · `textarea` · `richtext` · `number` · `currency` · `date` · `datetime` · `checkbox` · `select` · `combo` · `email` · `url` · `file` · `json` · `signature` |

> A `combo` field is a text input with autocomplete suggestions from a `source`
> (`"companies"`, `"users"`, or another module id), while still allowing free text —
> e.g. `{ "name": "subcontractor", "type": "combo", "source": "companies" }`.

> `url` and `file` fields show an **Upload** button that stores the file in the WordPress
> Media Library and fills in the URL (users can still paste an external link). They render as
> a link — or an image thumbnail — in the record view.
| `required` | `true` adds HTML5 + Bootstrap validation. |
| `list` | `true` shows the column in the list table (first 6 used; all columns stay sortable). |
| `options` | For `select`: array of strings. |
| `lookup` | For `select`: pull options from another module — `{ "module": "cost-codes", "value": "code", "label": "title" }`. This is how the **Resources** section feeds other forms. |
| `help` | Small helper text under the input. |

The `signature` type renders a draw-to-sign canvas pad (mouse/touch) on forms, an inline
image on views and lists, and is embedded as an image in PDF exports. The value is stored
as a PNG data URL in a `text` column; the server rejects anything that is not a valid PNG
data URL under ~220 KB.
| `step`, `rows`, `default`, `width: "full"` | Fine-tuning. |

## 3. The database table (created for you)

**You do not write any SQL.** When the module is installed, eManager creates its MySQL table
automatically (via `dbDelta`) from your `module.json` fields, adding the shared **common
columns** every record has:

```
id              bigint unsigned, auto-increment, primary key
project_id      varchar(191)
company_id      varchar(191)
status          varchar(191)
direction       varchar(32)        -- workflow direction (P&P / PO / DNP)
linked_module   varchar(191)       -- set when spawned from another record
linked_id       bigint unsigned
created_by      varchar(20)        -- WordPress user id
created_by_name varchar(191)
created_at      datetime
updated_at      datetime
```

Each of your fields becomes one column, typed by field `type`:

| Field type | MySQL column |
|---|---|
| `text` · `select` · `email` · `url` | `varchar(191)` (indexable) |
| `textarea` · `richtext` | `text` |
| `json` | `longtext` |
| `signature` | `mediumtext` |
| `number` · `currency` | `decimal(20,4)` |
| `date` | `date` · `datetime` → `datetime` |
| `checkbox` | `tinyint(1)` |

Indexes are added on `status`, `created_at`, `created_by`, `(project_id, status)`, the
linked-record back-reference, and **every field flagged `list: true`** (that is indexable),
so lists, filters and reports stay fast at tens of thousands of rows. If you change a
module's fields later, **eManager → Settings → Rebuild tables** re-runs `dbDelta` to add the
new columns (it never drops data). A full MySQL reference is generated at
`schema/schema-reference.sql` for DBAs, but it is informational only.

## 4. Package & install

```
equipment-log.zip
└── equipment-log/
    └── module.json        (plus optional module.js / module.css / *.html overrides)
```

Upload under **WP-Admin → eManager → Modules → Install module from ZIP**. The module is
extracted to `wp-content/uploads/emanager-modules/<section>/<id>/`, **its database table is
created automatically**, and it appears in the sidebar immediately. Uploading a ZIP with the
same `id` and a higher `version` upgrades it (and `dbDelta` adds any new columns).

> **Dev note — registry cache.** The scanned module list is cached in a transient for
> performance, so if you hand-edit a `module.json` on disk the change won't show until the
> cache clears. Click **eManager → Settings → Rebuild tables**, or add
> `add_filter( 'em_disable_module_cache', '__return_true' )` while developing.

## 5. Workflows & record linking (optional)

A module can declare a **workflow** — a state machine whose transitions are gated by the
acting user's **party role** (GC, Owner, Rep, Consultant, Subcontractor). The state names
double as the module's status list. Add a `workflow` key to `module.json`:

```json
"workflow": {
	"states": {
		"NOC Pending": {
			"transitions": [
				{ "to": "NOC Returned", "label": "Return with direction",
				  "party": [ "owner", "rep" ], "directions": [ "P&P", "PO", "DNP" ] }
			]
		},
		"NOC Returned": {
			"transitions": [ { "to": "Closed", "label": "Close", "party": [ "gc" ] } ]
		},
		"Closed": { "transitions": [] }
	}
}
```

Transition keys:

| Key | Notes |
|---|---|
| `to` | Target status (must be another state name). |
| `label` | Button text shown on the record view. |
| `party` | Party roles allowed to perform it (`gc`, `owner`, `rep`, `consultant`, `subcontractor`). Empty = any. GC/administrators always pass. |
| `directions` | Optional. When present, the user must choose one (e.g. `P&P`, `PO`, `DNP`); it is stored in the record's `direction` column and noted in the activity log. |
| `cap` | Optional capability floor (default `em_create`). |

The first state is the initial status applied to new records. Every transition is enforced
server-side and written to the `em_activity` audit table, which renders as a timeline on the
record view.

### Record linking ("convert to…")

Declare `relations` to let a record spawn a linked record in another module (the patent's
"convert PCO to NOC", "issue directive" chaining). Mapped fields are carried forward and the
new record back-references the source via `linked_module` / `linked_id`:

```json
"relations": [
	{ "spawn": "nocs", "label": "Issue NOC",
	  "map": { "title": "title", "description": "description", "rom_estimate": "rom_estimate" } }
]
```

`map` is `targetField: sourceField`. A "Issue NOC" button appears in the record's Workflow
panel for users who can create.

## 6. Custom behavior with module.js (optional)

`module.js` is loaded the first time the module is opened. Use `EM.registerModule()` to
replace any of the three pages, or run the default and decorate it:

```js
( function ( EM ) {
	'use strict';

	// Decorate the default form…
	async function form( container, module, id ) {
		await EM.form.render( container, module, id );
		// …e.g. add a button, compute totals, etc.
	}

	// …or completely replace the list page.
	async function list( container, module ) {
		const { records } = await EM.api.list( module.id, { per_page: 100 } );
		container.innerHTML = '<pre>' + JSON.stringify( records, null, 2 ) + '</pre>';
	}

	EM.registerModule( 'equipment-log', { form, list } );
} )( window.EM );
```

### The JS toolbox available to modules

| API | Purpose |
|---|---|
| `EM.api.list/get/create/update/remove(moduleId, …)` | CRUD against the WP REST proxy |
| `EM.api.comments / addComment` | Record comments |
| `EM.api.weather(lat, lon)` / `EM.api.stats()` | Weather proxy / project stats |
| `EM.table.render / EM.form.render / EM.view.render(container, module, id)` | Default pages |
| `EM.tpl.esc / format / statusBadge / toast / load(url)` | Escaping, formatting, toasts, template fetch |
| `EM.pdf.fromRecord / fromList / fromRows(…)` | PDF export |
| `EM.app.boot` | `{ user, caps, project, registry }` for the signed-in user |

Real examples in this plugin:

- `modules/reports/reports/module.js` — fully custom page (Chart.js + exports)
- `modules/field/daily-reports/module.js` — decorates the default form (weather autofill)
- `modules/bim/bim-models/module.js` — decorates the view page (three.js IFC viewer)
- `modules/settings/project-info/module.js` — virtual module (no table queries)

## 7. Template overrides (optional)

Copy a file from `modules/_defaults/` into your module folder and edit it. Keep the
`data-em="…"` attributes — they are the contract between templates and the engine
(e.g. `list-head`, `list-body`, `form-fields`, `comments`). Anything around them is yours
to restyle.

## 8. Server-side hooks (for theme/plugin developers)

| Hook | Type | Purpose |
|---|---|---|
| `em_modules` | filter | Add/modify module definitions in PHP |
| `em_sections` | filter | Add/modify sections |
| `em_record_created / _updated / _deleted` | action | React to CRUD events (notifications, webhooks…) |
| `em_record_transitioned` | action | React to a workflow status change |
| `em_allow_registration` | filter | Toggle front-end registration |

## 9. Checklist before shipping

- [ ] `id`, `table` unique; `section` valid
- [ ] First field is a sensible record title; `list: true` on ≤ 6 columns
- [ ] Statuses (or workflow states) ordered with the *initial* one first
- [ ] No PHP files in the ZIP (the database table is created automatically)
- [ ] Tested as Editor, Contributor and Viewer (buttons hide automatically, but verify your custom JS respects `EM.app.boot.caps`)
