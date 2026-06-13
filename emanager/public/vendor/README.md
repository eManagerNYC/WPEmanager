# Bundled third-party libraries

These libraries are bundled locally (not loaded from a CDN) so the plugin makes
no external requests for scripts or styles, per the WordPress.org Plugin
Directory guidelines. Each is unmodified from its official distribution.

| Library | Version | License | Source |
|---|---|---|---|
| Bootstrap (CSS + JS bundle) | 5.3.3 | MIT | https://github.com/twbs/bootstrap/releases/tag/v5.3.3 |
| Bootstrap Icons (CSS + fonts) | 1.11.3 | MIT | https://github.com/twbs/icons/releases/tag/v1.11.3 |
| jsPDF | 2.5.2 | MIT | https://github.com/parallax/jsPDF/releases/tag/v2.5.2 |
| jsPDF-AutoTable | 3.8.4 | MIT | https://github.com/simonbengtsson/jsPDF-AutoTable/releases/tag/v3.8.4 |
| Chart.js | 4.4.4 | MIT | https://github.com/chartjs/Chart.js/releases/tag/v4.4.4 |

Minified files (`*.min.js`, `*.min.css`) are the vendors' official production
builds; the unminified source for each is available at the URLs above. Chart.js
is shipped here as the non-minified `chart.umd.js`.

The optional in-browser IFC 3D viewer (three.js / web-ifc) is **not** bundled
and is **disabled by default**; it loads from a CDN only if a site owner opts in
with `add_filter( 'em_enable_ifc_viewer', '__return_true' )`. Bundle those
libraries locally for a fully self-hosted build.
