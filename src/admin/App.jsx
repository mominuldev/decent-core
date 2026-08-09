import { useCallback, useEffect, useMemo, useState } from 'react';
import { api, config } from './api';
import Field from './components/Field';
import WidgetCard from './components/WidgetCard';
import Tools from './components/Tools';

const TAB_ORDER = Object.keys(config.tabs);

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

  const fieldsFor = (name) =>
    Object.entries(config.schema).filter(([, field]) => field.tab === name);

  return (
    <div id="decent-core-app-inner" className="dc:pb-24">
      <header className="dc:flex dc:flex-wrap dc:items-end dc:justify-between dc:gap-6 dc:pb-6">
        <div>
          <p className="dc:font-mono dc:text-[11px] dc:tracking-[0.16em] dc:text-brand-600 dc:uppercase">
            Decent Core
          </p>
          <h1 className="dc:mt-2 dc:text-[28px] dc:leading-tight dc:font-bold dc:tracking-tight dc:text-ink">
            Settings
          </h1>
          <p className="dc:mt-2 dc:max-w-prose dc:text-sm dc:text-muted">
            Widgets, builder modules and catalogue behaviour. Everything here is generated
            from the plugin&rsquo;s schema, so what you see is what the server validates.
          </p>
        </div>

        <div className="dc:flex dc:items-center dc:gap-3">
          <span
            className={`dc:inline-flex dc:items-center dc:gap-2 dc:rounded-full dc:px-3 dc:py-1.5 dc:font-mono dc:text-[10px] dc:tracking-widest dc:uppercase ${
              dirty ? 'dc:bg-warn-tint dc:text-ink' : 'dc:bg-ok-tint dc:text-ink'
            }`}
          >
            <span
              className={`dc:h-1.5 dc:w-1.5 dc:rounded-full ${dirty ? 'dc:bg-warn' : 'dc:bg-ok'}`}
            />
            {dirty ? 'Unsaved' : 'Saved'}
          </span>
        </div>
      </header>

      <div className="dc:grid dc:gap-6 dc:lg:grid-cols-[200px_minmax(0,1fr)]">
        <nav aria-label="Settings sections" className="dc:h-max dc:lg:sticky dc:lg:top-10">
          <ul className="dc:flex dc:flex-wrap dc:gap-1 dc:lg:flex-col">
            {TAB_ORDER.map((name) => (
              <li key={name}>
                <button
                  type="button"
                  onClick={() => setTab(name)}
                  aria-current={tab === name ? 'true' : undefined}
                  className={`dc:w-full dc:rounded-sm dc:px-3 dc:py-2 dc:text-left dc:text-sm dc:font-medium dc:transition-colors ${
                    tab === name
                      ? 'dc:bg-brand-tint dc:text-brand-700'
                      : 'dc:text-muted hover:dc:bg-surface-alt hover:dc:text-ink'
                  }`}
                >
                  {config.tabs[name]}
                </button>
              </li>
            ))}
          </ul>
        </nav>

        <main>
          {tab === 'widgets' ? (
            <WidgetsPanel settings={settings} set={set} />
          ) : tab === 'tools' ? (
            <Tools />
          ) : (
            <section className="dc:rounded-md dc:border dc:border-line dc:bg-surface dc:px-5 dc:py-1">
              {fieldsFor(tab).length === 0 ? (
                <p className="dc:py-8 dc:text-sm dc:text-muted">Nothing to configure here yet.</p>
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
            </section>
          )}
        </main>
      </div>

      {(dirty || status) && (
        <div className="dc:fixed dc:right-0 dc:bottom-0 dc:left-[160px] dc:z-40 dc:border-t dc:border-line dc:bg-surface/95 dc:px-6 dc:py-3 dc:backdrop-blur">
          <div className="dc:flex dc:items-center dc:justify-between dc:gap-6">
            <p
              className={`dc:text-[13px] ${
                status?.kind === 'error' ? 'dc:font-semibold dc:text-danger' : 'dc:text-muted'
              }`}
              role={status?.kind === 'error' ? 'alert' : 'status'}
            >
              {status ? status.message : 'You have unsaved changes.'}
            </p>

            <div className="dc:flex dc:items-center dc:gap-2">
              {dirty && (
                <button
                  type="button"
                  onClick={() => setSettings(saved)}
                  className="dc:rounded-sm dc:px-3 dc:py-2 dc:text-[13px] dc:font-semibold dc:text-muted dc:transition-colors hover:dc:text-ink"
                >
                  Discard
                </button>
              )}

              <button
                type="button"
                onClick={save}
                disabled={busy || !dirty}
                className="dc:rounded-sm dc:bg-brand-600 dc:px-4 dc:py-2 dc:text-[13px] dc:font-semibold dc:text-white dc:transition-colors hover:dc:bg-brand-700 disabled:dc:opacity-50"
              >
                {busy ? 'Saving…' : 'Save changes'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

/**
 * The widgets grid.
 */
function WidgetsPanel({ settings, set }) {
  const widgets = Object.entries(config.widgets);

  return (
    <section>
      <p className="dc:mb-4 dc:max-w-prose dc:text-sm dc:text-muted">
        Switching a widget off removes it from the Elementor panel and stops registering
        its assets. Pages already using it will render nothing in its place.
      </p>

      <div className="dc:grid dc:gap-4 dc:sm:grid-cols-2 dc:xl:grid-cols-3">
        {widgets.map(([slug, widget]) => (
          <WidgetCard
            key={slug}
            slug={slug}
            widget={widget}
            enabled={Boolean(settings[widget.key])}
            onChange={(value) => set(widget.key, value)}
          />
        ))}
      </div>
    </section>
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
