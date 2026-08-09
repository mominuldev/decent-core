# Decent Core

Elementor widgets, header and footer builder, and Easy Digital Downloads
extensions for the Decent Themes marketplace.

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
`window.decentCore`, and the app talks back over `decent/v1/settings`, which is
capability-checked server-side.

Two constraints come from living inside somebody else's admin:

- **Preflight is not loaded.** Tailwind's reset would restyle all of wp-admin.
  A scoped reset under `#decent-core-app` stands in for it — including
  `border-style: solid`, without which every `border-*` utility renders
  nothing.
- **Every utility is prefixed `dc:`.** WordPress already defines `.button`,
  `.card`, `.hidden` and `.notice`; an unprefixed utility from a later
  stylesheet would silently change core UI outside our container.

### Adding a widget

1. `config/widgets.php` — one entry: class, title, category, group, styles,
   scripts, requires, default.
2. `includes/Elementor/Widgets/<Name>.php` extending the base widget.
3. `src/widgets/<slug>/style.scss` if it needs styles.

The registry, the settings toggle, the asset handles and the admin UI all read
that one entry. Nothing else needs changing.

## CLI

```bash
wp decent-core tokens verify   # diffs config/tokens.php against the theme's base.css
wp decent-core kit seed        # re-seeds Elementor globals and breakpoints
```
