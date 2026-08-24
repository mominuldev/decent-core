/**
 * Build pipeline.
 *
 *   src/widgets/<slug>/style.scss  ->  assets/widgets/<slug>/style.css
 *   src/widgets/<slug>/script.js   ->  assets/widgets/<slug>/script.js
 *   src/builder/style.scss         ->  assets/builder/style.css
 *   src/builder/script.js          ->  assets/builder/script.js
 *   src/admin/index.jsx            ->  assets/admin/app.js
 *   src/admin/app.css              ->  assets/admin/app.css
 *
 * Sources live in src/ and are committed but not shipped; assets/ is build
 * output and IS shipped, because a plugin must run from a zip without anyone
 * having to install node.
 *
 *   npm run build     one pass
 *   npm run dev       rebuild on save, unminified
 *   npm run package   build, verify, and zip the distributable plugin
 */

const fs = require('fs');
const path = require('path');
const { Transform } = require('stream');
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

/* ------------------------------------------------------------- builder */

/**
 * The header and footer builder's own pair. Not a widget: it styles the
 * landmark the builder renders into the theme's header and footer slots, and
 * it is enqueued by the builder rather than by a widget's asset declaration.
 */
function builderStyles() {
  return src('src/builder/*.scss', { sourcemaps: !PROD })
    .pipe(sass.sync({ silenceDeprecations: ['legacy-js-api'] }).on('error', sass.logError))
    .pipe(postcss([autoprefixer(), ...(PROD ? [cssnano({ preset: 'default' })] : [])]))
    .pipe(dest('assets/builder', { sourcemaps: !PROD ? '.' : false }));
}

function builderScripts() {
  return src('src/builder/*.js', { sourcemaps: !PROD })
    .pipe(PROD ? terser({ format: { comments: false } }) : rename((p) => p))
    .pipe(dest('assets/builder', { sourcemaps: !PROD ? '.' : false }));
}

/* --------------------------------------------------------------- admin */

/**
 * Bundles the React admin app. No @wordpress/* packages: React and ReactDOM
 * are bundled, and everything the app needs from WordPress arrives as data on
 * window.pixelomaticCore, printed by PHP.
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

const build = parallel(widgetStyles, widgetScripts, builderStyles, builderScripts, adminScripts, adminStyles);

function watchFiles() {
  watch('src/widgets/**/*.scss', widgetStyles);
  watch('src/widgets/**/*.js', widgetScripts);
  watch('src/builder/*.scss', builderStyles);
  watch('src/builder/*.js', builderScripts);
  watch(['src/admin/**/*.jsx', 'src/admin/**/*.js'], adminScripts);
  watch(['src/admin/**/*.css'], adminStyles);
}

/* ------------------------------------------------------------- package */

/*
 * `npm run package` writes the file that actually ships:
 *
 *   build/pixelomatic-core-<version>.zip
 *
 * with every path inside a single top-level `pixelomatic-core/` folder, because
 * WordPress installs a plugin under whatever the directory inside the archive
 * is called — and the plugin's own basename is what the licence check, the
 * update route and every `plugin_dir_url()` are keyed on.
 *
 * The exclusion list is `.distignore` and nothing else. Carrying a second copy
 * of it in this file is the usual arrangement and it is how a package ends up
 * shipping a source tree or missing a required directory: the two lists drift,
 * and nobody finds out until the zip is in someone else's hands.
 */

const SLUG = 'pixelomatic-core';
const BOOTSTRAP = SLUG + '.php';
const BUILD_DIR = 'build';

/**
 * `.distignore`, as gulp.src globs.
 *
 * Each entry excludes the path itself and everything below it, so a bare
 * directory name does what it looks like it does. Dotfiles are already outside
 * `**\/*`; they stay listed in `.distignore` because that file is also read by
 * humans and by `wp dist-archive`.
 *
 * @return {string[]} Glob list, inclusion first.
 */
function distGlobs() {
  const entries = fs
    .readFileSync('.distignore', 'utf8')
    .split('\n')
    .map((line) => line.trim())
    .filter((line) => line !== '' && !line.startsWith('#'));

  const globs = ['**/*'];

  entries.forEach((entry) => {
    const clean = entry.replace(/\/+$/, '');
    globs.push('!' + clean, '!' + clean + '/**');
  });

  return globs;
}

/**
 * The plugin version, read from the header WordPress itself parses.
 *
 * @return {string} Version string.
 */
function pluginVersion() {
  const header = fs.readFileSync(BOOTSTRAP, 'utf8').match(/^\s*\*\s*Version:\s*(.+)$/m);

  if (!header) {
    throw new Error(BOOTSTRAP + ' has no Version header.');
  }

  return header[1].trim();
}

/**
 * The checks that have to pass before anything is written.
 *
 * One version string spread across two places is one too many: the header is
 * what WordPress compares on update, PIXELOMATIC_CORE_VERSION is what every
 * enqueued asset is cache-busted with. Shipping them out of step releases a
 * plugin whose CSS never reaches a returning visitor.
 *
 * The asset check is the other half: `assets/` is committed build output, so a
 * widget added without a build produces a plugin that loads, registers the
 * widget, and renders it unstyled on someone else's site.
 */
function verifyVersion(done) {
  const bootstrap = fs.readFileSync(BOOTSTRAP, 'utf8');
  const version = pluginVersion();
  const constant = bootstrap.match(/PIXELOMATIC_CORE_VERSION',\s*'([^']+)'/);

  if (!constant) {
    done(new Error(BOOTSTRAP + ' does not define PIXELOMATIC_CORE_VERSION.'));
    return;
  }

  if (constant[1] !== version) {
    done(
      new Error(
        'Version mismatch: the plugin header says ' +
          version +
          ', PIXELOMATIC_CORE_VERSION says ' +
          constant[1] +
          '.'
      )
    );
    return;
  }

  const missing = [];

  const expect = (file) => {
    if (!fs.existsSync(file)) {
      missing.push(file);
    }
  };

  fs.readdirSync('src/widgets', { withFileTypes: true })
    .filter((entry) => entry.isDirectory())
    .forEach((entry) => {
      fs.readdirSync(path.join('src/widgets', entry.name)).forEach((file) => {
        if (file.endsWith('.scss')) {
          expect(path.join('assets/widgets', entry.name, file.replace(/\.scss$/, '.css')));
        } else if (file.endsWith('.js')) {
          expect(path.join('assets/widgets', entry.name, file));
        }
      });
    });

  ['assets/builder/style.css', 'assets/builder/script.js', 'assets/admin/app.js', 'assets/admin/app.css'].forEach(
    expect
  );

  if (missing.length) {
    done(new Error('Sources with no compiled asset:\n  ' + missing.join('\n  ')));
    return;
  }

  done();
}

/**
 * Refuse to package files that must never leave the machine.
 *
 * A stray `*.bkp` or `*.orig` beside a PHP file is not harmless: the webserver
 * has no handler for those extensions, so it serves them as plain text and the
 * source is readable at a guessable URL. Sourcemaps point at `src/` files the
 * package deliberately does not contain, so they are dead weight at best and a
 * 404 in devtools at worst — and `npm run dev` writes them next to the assets
 * that do ship.
 *
 * This walks the packaged set rather than filtering inside the zip pipeline: a
 * stream error raised mid-pipeline does not propagate downstream, so gulp would
 * hang on `dest` instead of reporting what was wrong.
 *
 * @return {Promise} Rejects, with the offending paths, if any are present.
 */
function verifyPackageSet() {
  return new Promise((resolve, reject) => {
    const stray = [];

    src(distGlobs(), { read: false })
      .on('data', (file) => {
        if (/(\.bkp|\.bak|\.orig|\.map|~)$/.test(file.relative)) {
          stray.push(file.relative);
        }
      })
      .on('error', reject)
      .on('end', () => {
        if (stray.length) {
          reject(new Error('Files that must not ship are in the package:\n  ' + stray.join('\n  ')));
          return;
        }

        resolve();
      });
  });
}

/**
 * Rewrite each file's path so the archive holds one top-level folder.
 *
 * @param {string} folder Directory name to nest everything under.
 * @return {Transform} Object-mode pass-through.
 */
function intoFolder(folder) {
  return new Transform({
    objectMode: true,
    transform(file, encoding, callback) {
      // Read `relative` before touching `path` — it is derived from both.
      const relative = file.relative;

      file.path = path.join(file.base, folder, relative);
      callback(null, file);
    },
  });
}

function cleanBuild(done) {
  fs.rmSync(BUILD_DIR, { recursive: true, force: true });
  done();
}

/*
 * gulp-zip is ESM from v6, and this file is CommonJS. Resolving it as its own
 * task keeps `bundle` a plain stream-returning function: gulp waits on a
 * returned stream, but not on a stream returned from inside a promise, and that
 * difference silently produces a truncated zip.
 */
let zip = null;

async function loadZip() {
  zip = (await import('gulp-zip')).default;
}

function bundle() {
  // `encoding: false` keeps images and fonts from being read as UTF-8 and
  // corrupted on the way into the archive.
  return src(distGlobs(), { base: '.', encoding: false })
    .pipe(intoFolder(SLUG))
    .pipe(zip(SLUG + '-' + pluginVersion() + '.zip'))
    .pipe(dest(BUILD_DIR));
}

function report(done) {
  const file = path.join(BUILD_DIR, SLUG + '-' + pluginVersion() + '.zip');
  const kb = Math.round(fs.statSync(file).size / 1024);

  process.stdout.write('\n  ' + file + '  (' + kb + ' KB)\n\n');
  done();
}

exports.widgetStyles = widgetStyles;
exports.widgetScripts = widgetScripts;
exports.builderStyles = builderStyles;
exports.builderScripts = builderScripts;
exports.adminScripts = adminScripts;
exports.adminStyles = adminStyles;
exports.build = build;
exports.verify = series(verifyVersion, verifyPackageSet);
// `bundle` zips whatever is on disk now, without rebuilding. It carries
// loadZip because a task that only works as part of a longer series is a task
// that fails the first time someone runs it on its own.
exports.bundle = series(loadZip, bundle, report);
exports.package = series(cleanBuild, build, verifyVersion, verifyPackageSet, loadZip, bundle, report);
exports.watch = series(build, watchFiles);
exports.default = build;
