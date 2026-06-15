# WordPress.org listing assets

These files are for the **plugin directory listing only** — they are **not** part
of the plugin ZIP. On wordpress.org they belong in the SVN repository's top-level
`/assets/` directory (a sibling of `/trunk`), not in the plugin code.

| File | Listing caption (`readme.txt` → `== Screenshots ==`) |
|---|---|
| `screenshot-1.png` | Dashboard home with section overview |
| `screenshot-2.png` | Module list with sorting, filtering and exports |
| `screenshot-3.png` | Record view with comments and PDF export |
| `screenshot-4.png` | Daily Report form with weather autofill and signature pad |
| `screenshot-5.png` | Reports module with project statistics |

The screenshot order here must match the order of the `== Screenshots ==` list in
`emanager/readme.txt`.

## Regenerating

The screenshots are rendered from HTML reproductions of the real UI (the plugin's
own Bootstrap 5 / Bootstrap Icons / Chart.js and `emanager.css`, with sample
project data). To rebuild:

```bash
node tools/make-screenshots.js          # writes tools/screenshots-build/*.html
# then rasterise each with headless Chrome at 2x:
#   chrome --headless=new --force-device-scale-factor=2 --window-size=1280,860 \
#     --screenshot=assets/screenshot-N.png file:///.../screenshot-N.html
```

These are representative renders for the initial listing — replace them with
captures from a populated live install whenever you have one.

You may also add `banner-772x250.png` / `banner-1544x500.png` and
`icon-128x128.png` / `icon-256x256.png` here for the directory header and icon.
