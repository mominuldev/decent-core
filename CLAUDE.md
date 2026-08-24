# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**An Elementor extension plugin** (`pixelomatic-core`) for an Easy Digital Downloads
marketplace. It ships the widgets, dynamic tags and theme-builder support that
the companion classic theme (`pixelomatic`, a sibling directory) does not own.

**The theme is the design system; this plugin is the editor surface.** The
theme owns the components — `.product-card`, `.btn`, `.section`, `.container`,
`.feature-card`, `.trust__metrics` and the rest — plus every design token. This
plugin renders *through* them. A widget that emitted its own card markup would
fork the design system on day one, and the theme's `bin/check.sh` fails the
build if `product-card` markup appears anywhere outside its own
`template-parts/product/`.

**The visual contract is the Figma file** `Pixelomatic`
(`https://www.figma.com/design/H4iveOsRivPhycwkeskHcb/Pixelomatic`). Widgets are
screenshot-diffed against it. Where Figma and an older implementation disagree,
Figma wins.

## Commands

```bash
composer check     # phpcs + phpstan (level 5) — run before saying anything is done
composer lint      # phpcs only          composer lint:fix  # phpcbf
composer analyse   # phpstan only
npm run build      # gulp: widget scss/js -> assets/, admin app via esbuild
npm run dev        # the same, watching
npm run package    # build, verify, zip -> build/pixelomatic-core-<version>.zip
wp i18n make-pot . languages/pixelomatic-core.pot --domain=pixelomatic-core \
  --exclude=node_modules,vendor,tests,src,assets
```

**`assets/` is build output and IS committed** — a plugin must run from a zip
without anyone installing node. Never hand-edit `assets/widgets/*/style.css` or
`script.js`; edit `src/widgets/<slug>/` and rebuild.

`npm run package` is the release command. It cleans `build/`, runs a production
build, runs the release gates and writes `build/pixelomatic-core-<version>.zip`
with everything inside a single top-level `pixelomatic-core/` folder — the
plugin basename is what the licence check, the update route and every
`plugin_dir_url()` are keyed on.

**The exclusion list is `.distignore` and nothing else.** `gulpfile.js` reads it
and turns each line into a pair of negated globs, so there is no second copy to
drift out of step with the first. Excluding something from the package means
adding a line there — and note that `src/` holds asset sources only because the
PHP lives in `includes/`; a package rule that excludes a directory holding PHP
ships a plugin that fatals on `plugins_loaded`.

The gates that run before anything is written:

- The plugin header `Version:` and `PIXELOMATIC_CORE_VERSION` must agree — one
  is what WordPress compares on update, the other is what every enqueued asset
  is cache-busted with.
- Every `src/widgets/<slug>/*.scss` and `*.js`, both builder pairs and both
  admin bundles must have a compiled counterpart in `assets/`. A widget added
  without a build otherwise registers fine and renders unstyled on someone
  else's site.
- No `*.bkp`, `*.bak`, `*.orig`, `*~` or `*.map` in the packaged set. The
  webserver has no handler for those extensions, so it serves them as plain
  text — one left beside a PHP file publishes the source at a guessable URL,
  and `npm run dev` writes sourcemaps next to assets that do ship.

`npm run zip` is an alias for the same thing. `gulp verify` runs the gates on
their own, without building or zipping; `gulp bundle` zips whatever is on disk
now, without rebuilding or running the gates.

PHPStan runs against hand-written stubs in `tests/stubs/` rather than a real
Elementor. When you use an Elementor or theme API the stubs do not declare, add
it to the stub — that is the point of them, and "PHPStan cannot see it" is not a
reason to skip a real check.

## Architecture

`config/widgets.php` is **the plugin's database of itself**. Four things read
that one array: `Widget_Registry` (what to register, plus a settings toggle per
entry), `Admin_Page` (the toggles and their unmet dependencies), `Manager` (a
style and script handle per entry) and `Bundler` (collapsing a page's widget set
into one file). Adding a widget is a class and one entry there. Nothing else
changes.

Per-entry keys: `class`, `title`, `category`, `group`, `icon`, `keywords`,
`styles`, `scripts`, `style_deps`, `script_deps`, `requires`, `default`.
`requires` is checked before registration — a product widget on a site without
EDD never reaches the panel.

Shared behaviour lives in traits under `includes/Elementor/Base/Traits/`, and a
widget composes them rather than restating controls:

| Trait | Gives you |
| --- | --- |
| `Has_Style_Controls` | `start_style_section()`, `register_box_style()`, `register_text_style()`, `register_button_style()`, `register_link_style()`, `register_gap_style()` |
| `Has_Query_Controls` | the EDD product query: category, tag, orderby, count, offset, exclude-current |
| `Has_Grid_Controls` | responsive columns + gap, targeting `.pix-grid` |
| `Has_Slider_Controls` | the whole carousel: controls, style controls, markup, Swiper config |
| `Has_Section_Head` | eyebrow / title / intro / trailing link |
| `Has_Product_Card_Style` | style controls for the theme's product card |

## Adding a widget

1. `config/widgets.php` — one entry.
2. `includes/Elementor/Widgets/<Name>.php` extending `Base\Widget_Base`.
3. `src/widgets/<slug>/style.scss` if it needs styles of its own.

## Adding a slider widget

**Always Swiper. Never a hand-rolled track.** Elementor ships Swiper 8.4.5 and
registers it under the `swiper` script and style handles whether we use it or
not, so the library is already on the page — a second slider implementation
costs maintenance and buys nothing.

Everything comes from `Has_Slider_Controls`; the widget supplies slides:

```php
use Has_Slider_Controls;   // needs Has_Style_Controls alongside it

// Content tab
$this->start_controls_section( 'slider', array( 'label' => __( 'Slider', 'pixelomatic-core' ) ) );
$this->register_slider_controls( array( 'slides_to_show' => '4' ) );  // your design's defaults
$this->end_controls_section();

// Style tab
$this->start_style_section( 'style_nav', __( 'Slider controls', 'pixelomatic-core' ) );
$this->register_slider_style_controls();
$this->end_controls_section();

// render()
$this->render_slider_start();
foreach ( $items as $item ) { /* echo one div.swiper-slide */ }
$this->render_slider_end( array(
    'prev_label' => __( 'Previous products', 'pixelomatic-core' ),
    'next_label' => __( 'Next products', 'pixelomatic-core' ),
) );
```

and in the map:

```php
'styles'      => array( 'carousel' ),   // plus your own handle if you have one
'scripts'     => array( 'carousel' ),
'style_deps'  => array( 'swiper' ),
'script_deps' => array( 'swiper' ),
```

That is the whole widget-side cost. The shared `pix-carousel` block
(`src/widgets/carousel/`) supplies the viewport, track, arrows, drag rail,
three pagination types, the "Showing 1–3 of 6" line and the Swiper boot.

- `slider_settings()` writes the config in **Swiper's own spelling**, so the
  script hands it straight to `new Swiper()`. Exposing another Swiper option is
  a control and nothing else — never add a translation layer.
- Elementor's breakpoints are **max-width**, Swiper's are **min-width**. They
  are not the same numbers. The trait maps between them off
  `Plugin::instance()->breakpoints->get_active_breakpoints()`, so the kit stays
  the single source of truth. Do not hard-code breakpoints.
- Swiper's `.swiper-wrapper` is `height: 100%`, which resolves circularly in an
  auto-height container and makes every slide hundreds of pixels tall. The
  shared stylesheet sets `height: auto` on the track. Leave it there.

## Header and footer builder

A `pixelomatic_template` post is a header, a footer or a block, built in Elementor
and assigned by condition. `includes/Builder/`, in the order a request meets it:

| Class | Owns |
| --- | --- |
| `Post_Type` | the CPT, its capabilities (`edit_theme_options`) and its type meta |
| `Canvas` | a template is edited, previewed and rendered on a bare canvas |
| `Display_Settings` | sticky / overlay / bottom, in Elementor's document settings |
| `Conditions\Manager` | the rules, compiled into one autoloaded option on save |
| `Resolver` | which template this request gets — array lookups, never a query |
| `Assets` | that template's CSS and JS, enqueued before `</head>` |
| `Renderer` | the landmark and the content, into the theme's header/footer slots |
| `Shortcode` | `[pixelomatic_template id="12"]`, the only way a block is placed |
| `Pro_Bridge` | stands down wherever Elementor Pro's Theme Builder owns the location |
| `Admin\Conditions_Box`, `Admin\Templates_List` | the type, the rules, the list |

**A template always renders on a canvas.** `Canvas` pins `_wp_page_template`
to `elementor_canvas` on save, reduces the layout picker to that one entry, and
filters `template_include` regardless of both — theme canvas, then Elementor's,
then the plugin's `templates/canvas.php`. Editing a header inside the ordinary
page canvas means building it while looking at the theme's other one.

**The landmark belongs to `Renderer`, never to the template.** `<header>`,
`<footer>`, the id and every behaviour class are printed by the renderer from
the template's document settings, so replacing the theme's static part cannot
silently drop a landmark. Behaviour is expressed as modifiers on the theme's
own block (`site-header--sticky`, `--overlay`, `--shadow`), and the script adds
only `--stuck` and `--hidden`. The stylesheet doubles the class
(`.site-header.site-header--builder`) because the theme's stylesheet loads
after the plugin's.

**Sticking is CSS.** `src/builder/script.js` exists for the two states CSS
cannot observe, and the renderer prints `data-pixelomatic-header` — and `Assets`
enqueues the script — only when a header has one of them switched on.

`Assets` also feeds `pixelomatic_core/assets/request_slugs`, so the widgets inside a
header join the page's bundle. Without that the menu and the logo are a request
of their own on every page of the site.

## JavaScript

- ES5 in a bare IIFE with `'use strict'`: `var` only, function expressions, no
  `let`/`const`, arrows, template literals or modules. Matches the theme.
- **Every widget script initialises from `frontend/element_ready`, never on
  load:**

  ```js
  jQuery(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/pixelomatic-<slug>.default', function ($scope) { … });
  });
  ```

  A widget dropped on the Elementor canvas arrives as markup from an AJAX
  render, long after any load event, and Elementor adds no script tag for it. A
  one-shot IIFE is dead in the editor. The hook also fires again on every
  control change, so **handlers must be idempotent** — tear down or replace,
  never stack listeners.
- Widget scripts are registered with `jquery` + `elementor-frontend` deps
  automatically (`Manager::register_assets()`); anything else goes in
  `script_deps`. The `elementor/frontend/init` event is a jQuery event — a
  native `addEventListener` never hears it.
- Editor preview assets are enqueued on `elementor/preview/enqueue_styles` and
  `elementor/preview/enqueue_scripts`. Not `elementor/editor/after_enqueue_*`,
  which targets the panel chrome, not the preview iframe.
- **CSS styles classes; JS queries `data-*` attributes.**
- **Progressive enhancement is a hard contract**, as in the theme. Every filter
  is a real link, every sort a real form, every slider a scroll-snap row before
  Swiper arrives. Failure paths follow the link rather than showing an error.
  Controls that only work with JS ship `hidden` and are revealed by the script
  that can drive them.

## CSS

- **Widget-owned classes use the `pix-` prefix and BEM**:
  `pix-block__element--modifier`. No `is-` state classes — a state is a
  modifier.
- **Theme-owned classes keep their names.** They are the theme's API. Check
  ownership by grepping `themes/pixelomatic/assets/css/style.css` before
  renaming anything.
- Widget stylesheets are scoped to `.elementor-widget-pixelomatic-<slug>`. Shared
  ones (`carousel`) are not — the `pix-` prefix is what keeps them from
  colliding.
- Always `var(--token)`; the theme's `assets/css/base.css` `:root` is where they
  are defined. Never a raw hex.
- Desktop-first `max-width` breakpoints only: **1180 / 1024 / 900 / 768 / 560**,
  synced into Elementor's kit by `Compat\Breakpoints` so "tablet" means the same
  width in the panel and the stylesheet.
- Text the design draws in `ink/400` renders in `--muted` — `ink/400` is 2.56:1
  on white. base.css documents this; follow it rather than the mockup.

## PHP

- `defined( 'ABSPATH' ) || exit;` is the first statement in every file.
- Validate for shape and range, sanitise for type, escape at output.
- `permission_callback` is never omitted on a REST route. A public read is fine
  **only** when every argument is a closed enum or a bounded scalar.
- **A REST endpoint that renders a widget reads its settings from the document,
  never from the request** (`Rest\Product_Grid_Controller`). The client names a
  post and an element id; it can never ask for a different post type, a bigger
  page size or a meta key, because none of those are arguments.
- Widgets never embed SVG. Icons come from the theme's map through
  `Widget_Base::icon()`, or from an Elementor picker through
  `render_picked_icon()`, which passes everything through `wp_kses`.

## Gotchas that have bitten

- `Bundler` concatenates widget assets and re-registers the originals as
  aliases. It must carry their dependencies onto the bundle — otherwise Swiper
  loads after the script that needs it, but only on pages with 2+ widgets.
- Renaming a CSS class changes Elementor's saved selectors. After any such
  change, tell the user to run **Elementor → Tools → Regenerate CSS** or the
  plugin's flush-assets tool.
- A widget slug is its identity in every page already built with it. Retitle
  freely; never rename a slug.

## Git

Commit only when asked.
