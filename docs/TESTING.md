# Testing & quality infrastructure

All development tooling lives at the **repository root**, outside the `emanager/`
plugin folder, so none of it ships in the distributed plugin ZIP.

## One-time setup

```bash
composer install   # installs PHPUnit + PHP_CodeSniffer + WordPress Coding Standards
```

## Commands

| Command              | What it does                                                        |
| -------------------- | ------------------------------------------------------------------- |
| `composer test`      | Runs the PHPUnit unit suite (`phpunit.xml.dist`).                   |
| `composer lint`      | Runs PHPCS against the plugin using the WordPress standard.          |
| `composer lint:fix`  | Auto-fixes the alignment/spacing issues PHPCS can fix (`phpcbf`).    |

## What the unit tests cover

These are **pure-logic** unit tests — no database, no live WordPress runtime.
The bootstrap (`tests/bootstrap.php`) defines the handful of WordPress functions
the code calls and steers them through `$GLOBALS['em_test_*']` knobs.

- **`WorkflowTest`** — the state machine: `has`/`get`, `transitions_from`,
  `find_transition`, `missing_requirements`, and party/capability gating in
  `available_transitions` (including the manager bypass).
- **`DbHelpersTest`** — the SQL-safety choke point: `EM_DB::safe_ident()`
  identifier whitelisting and `EM_DB::column_type()` field-type mapping.
- **`RolesTest`** — party-role resolution and `user_has_party()` gating rules.
- **`ModuleManifestTest`** — structural validation of **every** generated
  `module.json` (102 of them): required keys, safe table names, fields with a
  name + type, and workflow consistency (initial state exists, every transition
  targets a defined state, every party gate names a real party role). This is
  the regression net for `tools/generate-modules.js`.

## Integration / live tests

Database- and REST-bound behaviour is exercised against a **real WordPress
install** by the `tools/smoke*.php` scripts, run via WP-CLI:

```bash
php tools/wp-cli.phar eval-file tools/smoke17.php --path=C:/laragon/www/emtest
```

Each script prints `PASS`/`FAIL` lines and ends with `DONE`; a healthy run also
leaves an empty `debug.log`.

## Translations

The translation template ships at `emanager/languages/emanager.pot`. Regenerate
it after adding or changing translatable strings:

```bash
php tools/wp-cli.phar i18n make-pot emanager emanager/languages/emanager.pot \
  --domain=emanager --exclude=vendor,node_modules,public/vendor
```

## Continuous integration

`.github/workflows/ci.yml` runs on every push/PR:

1. **phpunit** — `php -l` lint + the unit suite on PHP 8.0/8.1/8.2/8.3.
2. **phpcs** — the WordPress Coding Standards check.
3. **javascript** — `node --check` on the generator and front-end bundles, plus
   a JSON-parse check across all `module.json` manifests.
