import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { api, config } from './api';
import Button from './components/Button';
import Field from './components/Field';
import Icon from './components/Icon';
import Panel from './components/Panel';
import Tools from './components/Tools';
import WidgetsPanel from './components/WidgetsPanel';

const TAB_ORDER = Object.keys(config.tabs);

/**
 * An icon per tab, keyed by the schema's tab slug.
 *
 * A tab added by the `pixelomatic_core/settings/schema` filter simply gets no icon
 * rather than a broken one, so third-party tabs stay possible.
 */
const TAB_ICONS = {
  general: 'sliders',
  widgets: 'grid',
  extensions: 'puzzle',
  edd: 'tag',
  performance: 'gauge',
  tools: 'wrench',
};

/**
 * Settings application.
 *
 * State lives here and is flushed in one request. The save bar only appears
 * when something has actually changed, so the screen never asks for an action
 * that would do nothing.
 */
export default function App() {
  const [settings, setSettings] = useState(config.settings);
  const [saved, setSaved] = useState(config.settings);
  const [tab, setTab] = useState(readTabFromUrl());
  const [status, setStatus] = useState(null);
  const [busy, setBusy] = useState(false);
  const panel = useRef(null);

  const dirty = useMemo(
    () => JSON.stringify(settings) !== JSON.stringify(saved),
    [settings, saved]
  );

  const set = useCallback((key, value) => {
    setSettings((current) => ({ ...current, [key]: value }));
  }, []);

  const save = useCallback(async () => {
    setBusy(true);
    setStatus(null);

    try {
      const result = await api.save(settings);
      setSaved(result.settings);
      setSettings(result.settings);
      setStatus({ kind: 'ok', message: 'Settings saved.' });
    } catch (error) {
      setStatus({ kind: 'error', message: error.message });
    } finally {
      setBusy(false);
    }
  }, [settings]);

  // The tab lives in the URL so a screenshot or a support link points at the
  // screen somebody actually meant.
  useEffect(() => {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url);
  }, [tab]);

  // Switching tabs replaced the whole panel and told a screen reader nothing:
  // focus stayed on the button that was clicked, so the only feedback was the
  // button's own label changing state. Moving focus into the panel makes the
  // new section announce itself, which is the same thing the sighted reader
  // gets from the content visibly swapping.
  //
  // Skipped on first paint — stealing focus on page load would drop anyone
  // tabbing in from the admin menu straight past the navigation.
  const mounted = useRef(false);

  useEffect(() => {
    if (!mounted.current) {
      mounted.current = true;
      return;
    }

    panel.current?.focus();
  }, [tab]);

  // A dismissible toast that does not linger.
  useEffect(() => {
    if (!status || status.kind !== 'ok') return undefined;
    const timer = window.setTimeout(() => setStatus(null), 4000);
    return () => window.clearTimeout(timer);
  }, [status]);

  // Leaving with unsaved changes is almost always a mistake.
  useEffect(() => {
    if (!dirty) return undefined;
    const warn = (event) => {
      event.preventDefault();
      event.returnValue = '';
    };
    window.addEventListener('beforeunload', warn);
    return () => window.removeEventListener('beforeunload', warn);
  }, [dirty]);

  // Ctrl/Cmd+S saves. The browser's "save page" dialog is never what someone
  // pressing it on a settings screen wanted.
  useEffect(() => {
    const onKey = (event) => {
      if (!(event.metaKey || event.ctrlKey) || event.key !== 's') return;
      event.preventDefault();
      if (dirty && !busy) save();
    };

    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [dirty, busy, save]);

  const fieldsFor = useCallback(
    (name) => Object.entries(config.schema).filter(([, field]) => field.tab === name),
    []
  );

  // A per-tab count, shown in the nav so the sections advertise their weight
  // before you click into them.
  const tabCount = useCallback(
    (name) => {
      if (name === 'widgets') return Object.keys(config.widgets).length;
      if (name === 'tools') return null;
      return fieldsFor(name).length || null;
    },
    [fieldsFor]
  );

  const widgetStats = useMemo(() => {
    const entries = Object.entries(config.widgets);
    const on = entries.filter(
      ([, widget]) => !(widget.missing || []).length && settings[widget.key]
    ).length;

    return { on, total: entries.length };
  }, [settings]);

  return (
    <div id="pixelomatic-core-app-inner" className="dc:pb-28">
      <header className="dc:relative dc:overflow-hidden dc:rounded-2xl dc:px-6 dc:py-6 dc:text-white dc:sm:px-8">
        {/* The brand field runs the whole width of the header rather than
            fading to near-black on the left. This is the one place on the
            screen where the product gets to look like itself, and half of it
            was being spent on a dark rectangle.

            A second pass lifts the top edge, so the surface reads as lit
            rather than printed. Both are decorative and aria-hidden. */}
        <span
          aria-hidden="true"
          className="dc:pointer-events-none dc:absolute dc:inset-0"
          style={{
            background:
              'linear-gradient(100deg, #241c57 0%, #4338ca 42%, #6d3be4 74%, #a335ea 100%)',
          }}
        />
        <span
          aria-hidden="true"
          className="dc:pointer-events-none dc:absolute dc:inset-x-0 dc:top-0 dc:h-24"
          style={{ background: 'linear-gradient(180deg, rgb(255 255 255 / 0.14), transparent)' }}
        />

        <div className="dc:relative dc:flex dc:flex-wrap dc:items-center dc:justify-between dc:gap-6">
          <div className="dc:min-w-0">
            <p className="dc:flex dc:items-center dc:gap-2 dc:font-mono dc:text-micro dc:tracking-[0.18em] dc:text-white/75 dc:uppercase">
              <Icon name="spark" size={13} />
              Pixelomatic Core
              {config.version && (
                <span className="dc:rounded-full dc:bg-white/10 dc:px-2 dc:py-0.5 dc:tracking-normal dc:normal-case">
                  v{config.version}
                </span>
              )}
            </p>

            <h1 className="dc:mt-2.5 dc:text-display dc:font-bold dc:tracking-tight dc:text-white">
              Settings
            </h1>

            <p className="dc:mt-2 dc:max-w-prose dc:text-body dc:text-white/80">
              Widgets, builder modules and catalogue behaviour. Everything here is generated
              from the plugin&rsquo;s schema, so what you see is what the server validates.
            </p>
          </div>

          <div className="dc:flex dc:items-center dc:gap-2.5">
            {/* A hairline of its own, so the tile reads as a panel on the brand
                field rather than a patch of slightly lighter paint. */}
            <div className="dc:rounded-xl dc:bg-white/10 dc:px-4 dc:py-2.5 dc:ring-1 dc:ring-white/20 dc:ring-inset dc:backdrop-blur">
              <p className="dc:font-mono dc:text-micro dc:tracking-[0.12em] dc:text-white/65 dc:uppercase">
                Widgets on
              </p>
              <p className="dc:mt-0.5 dc:text-lg dc:leading-none dc:font-bold dc:tabular-nums">
                {widgetStats.on}
                <span className="dc:text-label dc:font-medium dc:text-white/55">
                  /{widgetStats.total}
                </span>
              </p>
            </div>

            {/* The status tokens are tuned for white surfaces and are close to
                invisible here, so the header uses its own two. */}
            <span
              className={`dc:inline-flex dc:items-center dc:gap-2 dc:rounded-full dc:px-3 dc:py-1.5 dc:font-mono dc:text-micro dc:tracking-widest dc:uppercase dc:backdrop-blur ${
                dirty
                  ? 'dc:bg-on-brand-warn/20 dc:text-on-brand-warn'
                  : 'dc:bg-on-brand-ok/20 dc:text-on-brand-ok'
              }`}
            >
              <span
                className={`dc:h-1.5 dc:w-1.5 dc:rounded-full ${
                  dirty ? 'dc:animate-pulse dc:bg-on-brand-warn' : 'dc:bg-on-brand-ok'
                }`}
              />
              {dirty ? 'Unsaved' : 'Saved'}
            </span>
          </div>
        </div>
      </header>

      <div className="dc:mt-6 dc:grid dc:gap-6 dc:lg:grid-cols-[270px_minmax(0,1fr)]">
        {/* The nav gets a surface of its own. Sitting bare on the page
            background it read as loose text beside a card, which left the
            whole left column looking like an afterthought.

            It hugs its six rows rather than stretching to the viewport. The
            full-height version put roughly 300px of empty card under the last
            tab on every screen — the rail's own emptiness was the largest
            shape on the page — and it bought nothing, because sticky
            positioning does not need the height. */}
        <nav aria-label="Settings sections" className="dc:h-max dc:lg:sticky dc:lg:top-10">
          <div className="dc:flex dc:flex-col dc:rounded-xl dc:border dc:border-line dc:bg-surface dc:p-3 dc:shadow-card">
            <ul className="dc:flex dc:flex-wrap dc:gap-0.5 dc:lg:flex-col">
            {TAB_ORDER.map((name) => {
              const active = tab === name;
              const count = tabCount(name);

              return (
                <li key={name}>
                  <button
                    type="button"
                    onClick={() => setTab(name)}
                    aria-current={active ? 'page' : undefined}
                    // A lit fill for the current section rather than a flat
                    // block: this is the one thing on the screen that answers
                    // "where am I", and the brand-tinted shadow keeps it from
                    // looking pasted on. A neutral shadow under a saturated
                    // element reads as dirt against the colour above it.
                    className={`dc:flex dc:w-full dc:items-center dc:gap-2.5 dc:rounded-lg dc:px-3 dc:py-2.5 dc:text-left dc:text-label dc:transition-all dc:duration-150 ${
                      active
                        ? 'dc:bg-linear-to-r dc:from-brand-600 dc:to-brand-500 dc:font-semibold dc:text-white dc:shadow-brand'
                        : 'dc:font-medium dc:text-ink-soft dc:hover:bg-surface-sunk'
                    }`}
                  >
                    <Icon
                      name={TAB_ICONS[name]}
                      size={17}
                      className={active ? 'dc:text-white' : 'dc:text-muted'}
                    />
                    <span className="dc:flex-1 dc:truncate">{config.tabs[name]}</span>
                    {count !== null && (
                      // A pill rather than loose digits: at 11px a bare number
                      // beside a label reads as part of the label.
                      <span
                        className={`dc:min-w-[1.375rem] dc:rounded-full dc:px-1.5 dc:py-0.5 dc:text-center dc:font-mono dc:text-micro ${
                          active
                            ? 'dc:bg-white/20 dc:text-white/95'
                            : 'dc:bg-surface-sunk dc:text-muted'
                        }`}
                      >
                        {count}
                      </span>
                    )}
                  </button>
                </li>
              );
            })}
            </ul>

            {/* Follows the last tab. Now that the rail hugs its rows rather
                than running a viewport tall, this sits just under the last tab
                instead of a screen below it, which is the only place it was
                ever going to be read. */}
            <p className="dc:mt-3 dc:hidden dc:border-t dc:border-line dc:px-2 dc:pt-3 dc:text-help dc:text-muted dc:lg:block">
              Changes apply to every tab at once. Press{' '}
              <kbd className="dc:rounded dc:bg-surface-sunk dc:px-1.5 dc:py-0.5 dc:font-mono dc:text-micro dc:text-ink-soft">
                ⌘S
              </kbd>{' '}
              to save.
            </p>
          </div>
        </nav>

        {/* Keyed on the tab so switching sections replays the entrance
            animation instead of swapping content in place. */}
        {/* tabIndex -1 so the effect above can move focus here without adding
            another stop to the tab order. The outline is suppressed because
            this only ever receives focus programmatically; every control inside
            keeps its own. */}
        <main
          key={tab}
          ref={panel}
          tabIndex={-1}
          aria-label={config.tabs[tab]}
          className="dc-rise dc:min-w-0 dc:focus:outline-none"
        >
          {tab === 'widgets' ? (
            <WidgetsPanel settings={settings} set={set} />
          ) : tab === 'tools' ? (
            <Tools />
          ) : (
            <Panel icon={TAB_ICONS[tab]} title={config.tabs[tab]}>
              {fieldsFor(tab).length === 0 ? (
                <p className="dc:px-5 dc:py-10 dc:text-center dc:text-body dc:text-muted">
                  Nothing to configure here yet.
                </p>
              ) : (
                <div className="dc:divide-y dc:divide-line">
                  {fieldsFor(tab).map(([name, field]) => (
                    <Field
                      key={name}
                      name={name}
                      field={field}
                      value={settings[name]}
                      onChange={(value) => set(name, value)}
                    />
                  ))}
                </div>
              )}
            </Panel>
          )}
        </main>
      </div>

      {(dirty || status) && (
        <div className="dc:fixed dc:right-0 dc:bottom-0 dc:left-(--dc-menu-width) dc:z-40 dc:border-t dc:border-line dc:bg-surface/90 dc:px-6 dc:py-3 dc:shadow-bar dc:backdrop-blur-md">
          <div className="dc:flex dc:items-center dc:justify-between dc:gap-6">
            <p
              className={`dc:flex dc:items-center dc:gap-2 dc:text-body ${
                status?.kind === 'error'
                  ? 'dc:font-semibold dc:text-danger'
                  : status?.kind === 'ok'
                    ? 'dc:font-medium dc:text-ok'
                    : 'dc:text-muted'
              }`}
              role={status?.kind === 'error' ? 'alert' : 'status'}
            >
              {status ? (
                <Icon name={status.kind === 'error' ? 'alert' : 'check'} size={15} />
              ) : (
                // The bar can appear for two different reasons. A mark against
                // the unsaved message keeps it from being read as the same
                // settled confirmation the saved state shows.
                <span
                  aria-hidden="true"
                  className="dc:h-1.5 dc:w-1.5 dc:shrink-0 dc:rounded-full dc:bg-warn"
                />
              )}
              {status ? status.message : 'You have unsaved changes.'}
            </p>

            <div className="dc:flex dc:items-center dc:gap-2">
              {dirty && (
                <Button variant="ghost" icon="undo" onClick={() => setSettings(saved)}>
                  Discard
                </Button>
              )}

              <Button
                variant="primary"
                icon="save"
                onClick={save}
                disabled={!dirty}
                busy={busy}
                busyLabel="Saving…"
                className="dc:px-4"
              >
                Save changes
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

/**
 * Reads the tab from the URL, falling back to the first one.
 *
 * @returns {string} Tab key.
 */
function readTabFromUrl() {
  const requested = new URL(window.location.href).searchParams.get('tab');
  return TAB_ORDER.includes(requested) ? requested : TAB_ORDER[0];
}
