# eManager — repository

This repository contains the **eManager** WordPress plugin (a modular construction-management
dashboard) plus its development tooling. The shippable plugin lives entirely in
[`emanager/`](emanager/); everything else here is build/test tooling and is **not** included
in the distributed plugin ZIP.

> **Product & architecture docs:** see [`emanager/README.md`](emanager/README.md).
> **wp.org listing copy & changelog:** see [`emanager/readme.txt`](emanager/readme.txt).

## Repository layout

| Path | What it is |
|---|---|
| [`emanager/`](emanager/) | The plugin — the only folder that ships. |
| [`tools/generate-modules.js`](tools/generate-modules.js) | **Source of truth for modules.** Edit this and regenerate; never hand-edit the generated `module.json` / `schema-reference.sql`. |
| [`tools/wp-cli.phar`](tools/) | Bundled WP-CLI used for `.pot` generation and live smoke tests. |
| [`tools/smoke*.php`](tools/) | Live integration tests, run via `wp eval-file` against a real WordPress install. |
| [`tests/`](tests/) | PHPUnit unit suite (no DB / no WP boot). |
| [`docs/TESTING.md`](docs/TESTING.md) | How to run tests, lint, regenerate translations, and what CI does. |
| `composer.json`, `phpunit.xml.dist`, `phpcs.xml.dist` | Dev tooling config (PHPUnit + WordPress Coding Standards). |
| [`.github/workflows/ci.yml`](.github/workflows/ci.yml) | CI: lint + unit tests (PHP 8.0–8.3), PHPCS, JS/JSON checks. |

## Quick start (contributors)

```bash
composer install        # PHPUnit + PHP_CodeSniffer + WordPress Coding Standards
composer test           # run the unit suite
composer lint           # PHPCS (WordPress standard); composer lint:fix to auto-fix
node tools/generate-modules.js   # regenerate module.json + schema after editing the spec
```

See [`docs/TESTING.md`](docs/TESTING.md) for the full workflow, including the live smoke tests
and regenerating the translation template (`emanager/languages/emanager.pot`).

## Building the plugin ZIP

The ZIP is just the `emanager/` folder (dev tooling stays at the repo root and is excluded):

```powershell
Compress-Archive -Path emanager -DestinationPath emanager-<version>.zip -Force
```

When bumping the version, keep these four in sync: the `Version:` header and `EM_VERSION` in
[`emanager/emanager.php`](emanager/emanager.php), `Stable tag` in
[`emanager/readme.txt`](emanager/readme.txt), and the version span in
[`emanager/public/partials/footer.html`](emanager/public/partials/footer.html).

## WordPress.org listing

- **Contributors:** `blackrebel` · **Plugin URI / Author URI:** https://www.wprealwise.com
- **Screenshots:** [`assets/`](assets/) holds `screenshot-1..5.png` matching the
  `== Screenshots ==` list in `readme.txt`. Regenerate with `node tools/make-screenshots.js`
  (see [`assets/README.md`](assets/README.md)). On wordpress.org these go in the SVN
  `/assets/` directory, not the plugin ZIP.
- *Optional polish:* add directory `banner-*.png` and `icon-*.png` to `assets/`, and swap the
  representative screenshots for captures from a populated live install.

## License

GPL-2.0-or-later.
