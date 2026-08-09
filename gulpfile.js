/**
 * Build pipeline.
 *
 *   src/widgets/<slug>/style.scss  ->  assets/widgets/<slug>/style.css
 *   src/widgets/<slug>/script.js   ->  assets/widgets/<slug>/script.js
 *   src/admin/index.jsx            ->  assets/admin/app.js
 *   src/admin/app.css              ->  assets/admin/app.css
 *
 * Sources live in src/ and are committed but not shipped; assets/ is build
 * output and IS shipped, because a plugin must run from a zip without anyone
 * having to install node.
 */

const { src, dest, series, parallel, watch } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const postcss = require('gulp-postcss');
const terser = require('gulp-terser');
const rename = require('gulp-rename');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const esbuild = require('esbuild');

const PROD = process.env.NODE_ENV !== 'development';

/* ------------------------------------------------------------- widgets */

/**
 * Compiles each widget's SCSS beside its PHP, keeping the one-directory-per-
 * widget layout the widget map and the asset bundler both rely on.
 */
function widgetStyles() {
  return src('src/widgets/**/*.scss', { sourcemaps: !PROD })
    .pipe(sass.sync({ silenceDeprecations: ['legacy-js-api'] }).on('error', sass.logError))
    .pipe(postcss([autoprefixer(), ...(PROD ? [cssnano({ preset: 'default' })] : [])]))
    .pipe(dest('assets/widgets', { sourcemaps: !PROD ? '.' : false }));
}

/**
 * Widget scripts. Copied and minified rather than bundled: each one is a small
 * IIFE with no imports, matching the theme's own front-end convention.
 */
function widgetScripts() {
  return src('src/widgets/**/*.js', { sourcemaps: !PROD })
    .pipe(PROD ? terser({ format: { comments: false } }) : rename((p) => p))
    .pipe(dest('assets/widgets', { sourcemaps: !PROD ? '.' : false }));
}

/* --------------------------------------------------------------- admin */

/**
 * Bundles the React admin app. No @wordpress/* packages: React and ReactDOM
 * are bundled, and everything the app needs from WordPress arrives as data on
 * window.decentCore, printed by PHP.
 */
function adminScripts() {
  return esbuild.build({
    entryPoints: ['src/admin/index.jsx'],
    outfile: 'assets/admin/app.js',
    bundle: true,
    format: 'iife',
    target: ['es2019'],
    jsx: 'automatic',
    minify: PROD,
    sourcemap: !PROD,
    logLevel: 'info',
    define: { 'process.env.NODE_ENV': JSON.stringify(PROD ? 'production' : 'development') },
  });
}

/**
 * Tailwind for the admin app.
 */
function adminStyles() {
  return src('src/admin/app.css')
    .pipe(postcss([
      require('@tailwindcss/postcss'),
      autoprefixer(),
      ...(PROD ? [cssnano({ preset: 'default' })] : []),
    ]))
    .pipe(dest('assets/admin'));
}

/* --------------------------------------------------------------- tasks */

const build = parallel(widgetStyles, widgetScripts, adminScripts, adminStyles);

function watchFiles() {
  watch('src/widgets/**/*.scss', widgetStyles);
  watch('src/widgets/**/*.js', widgetScripts);
  watch(['src/admin/**/*.jsx', 'src/admin/**/*.js'], adminScripts);
  watch(['src/admin/**/*.css'], adminStyles);
}

exports.widgetStyles = widgetStyles;
exports.widgetScripts = widgetScripts;
exports.adminScripts = adminScripts;
exports.adminStyles = adminStyles;
exports.build = build;
exports.watch = series(build, watchFiles);
exports.default = build;
