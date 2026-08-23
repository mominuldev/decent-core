# Pixelomatic Core

Elementor widgets, header and footer builder, and Easy Digital Downloads
extensions for the Pixelomatic marketplace.

## Requirements

PHP 7.4+ · WordPress 6.0+ · Elementor 3.18+ (tested against 4.x)

## Working on it

The plugin ships ready to run — `assets/` is committed build output, so a zip
install needs no tooling. You only need the toolchain to change something.

```bash
composer install     # PHPCS, PHPStan
npm install          # gulp, sass, tailwind, esbuild, react

npm run build        # one-off build
npm run dev          # watch

composer lint        # phpcs
composer analyse     # phpstan level 5
```

### Layout

```
src/widgets/<slug>/style.scss   ->  assets/widgets/<slug>/style.css
src/widgets/<slug>/script.js    ->  assets/widgets/<slug>/script.js
src/admin/index.jsx             ->  assets/admin/app.js
src/admin/app.css               ->  assets/admin/app.css
```

`src/` is committed but not shipped; `assets/` is shipped.

### The admin app

React 19 and Tailwind 4, bundled with esbuild. No `@wordpress/*` packages:
everything WordPress needs to say arrives as one JSON object on
`window.pixelomaticCore`, and the app talks back over `pixelomatic/v1/settings`, which is
capability-checked server-side.

Two constraints come from living inside somebody else's admin:

- **Preflight is not loaded.** Tailwind's reset would restyle all of wp-admin.
  A scoped reset under `#pixelomatic-core-app` stands in for it — including
  `border-style: solid`, without which every `border-*` utility renders
  nothing.
- **Every utility is prefixed `dc:`.** WordPress already defines `.button`,
  `.card`, `.hidden` and `.notice`; an unprefixed utility from a later
  stylesheet would silently change core UI outside our container.

### Adding a widget

1. `config/widgets.php` — one entry: class, title, category, group, styles,
   scripts, style_deps, script_deps, requires, default.
2. `includes/Elementor/Widgets/<Name>.php` extending the base widget.
3. `src/widgets/<slug>/style.scss` if it needs styles.

The registry, the settings toggle, the asset handles and the admin UI all read
that one entry. Nothing else needs changing.

Shared control sets live in `includes/Elementor/Base/Traits/` — compose them
rather than restating controls: `Has_Query_Controls` (the EDD product query),
`Has_Grid_Controls` (responsive columns and gap), `Has_Slider_Controls` (a whole
Swiper carousel), `Has_Section_Head`, `Has_Style_Controls`.

### Adding a slider widget

Sliders always use Swiper, and always the shared one. Elementor already ships
Swiper 8.4.5 and registers it, so it costs no bytes of ours — and a second
slider implementation would be a second thing to keep in step.

`Has_Slider_Controls` supplies the controls, the Style-tab panel, the markup and
the Swiper config; the widget supplies slides:

```php
use Has_Slider_Controls;   // alongside Has_Style_Controls

$this->register_slider_controls( array( 'slides_to_show' => '4' ) );
$this->register_slider_style_controls();

$slider = $this->render_slider_start();
foreach ( $items as $item ) { /* echo one .swiper-slide */ }
$this->render_slider_end( array( 'tag' => $slider['tag'] ) );
```

with `'styles' => array( 'carousel' )`, `'scripts' => array( 'carousel' )` and
`'style_deps' => array( 'swiper' )`, `'script_deps' => array( 'swiper' )` in the
map. The shared `pix-carousel` block does the rest — viewport, track, arrows,
drag rail, bullets/fraction/progress-bar pagination and the slide count.

Widget JavaScript must initialise from Elementor's `frontend/element_ready`
hook, never on load, or it will not run in the editor. See `CLAUDE.md`.

## CLI

```bash
wp pixelomatic-core tokens verify   # diffs config/tokens.php against the theme's base.css
wp pixelomatic-core kit seed        # re-seeds Elementor globals and breakpoints
```
