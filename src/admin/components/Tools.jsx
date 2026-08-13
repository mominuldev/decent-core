import { useState } from 'react';
import { api } from '../api';
import Button from './Button';
import Icon from './Icon';
import Panel from './Panel';
import SystemInfo from './SystemInfo';

const ACTIONS = [
  {
    slug: 'flush-assets',
    icon: 'refresh',
    label: 'Rebuild widget assets',
    help: 'Deletes the combined CSS and JS bundles. They rebuild on the next page view. Use this after editing a widget stylesheet, since bundle filenames only change with the plugin version outside debug mode.',
  },
  {
    slug: 'recompile-conditions',
    icon: 'sliders',
    label: 'Recompile display conditions',
    help: 'Rebuilds the header and footer lookup table. It recompiles on every template save, so this is only needed if a template was changed outside the editor.',
  },
  {
    slug: 'clear-cache',
    icon: 'spark',
    label: 'Clear caches',
    help: "Clears Elementor's generated CSS and the object cache. Do this after changing the palette, since compiled files still carry the old values.",
  },
];

/**
 * Maintenance actions and the system read-out.
 *
 * The result is shown against the action that produced it rather than in one
 * shared strip at the bottom: with three buttons a few pixels apart, a message
 * that does not say which one it belongs to is a guess.
 */
export default function Tools() {
  const [busy, setBusy] = useState(null);
  const [results, setResults] = useState({});

  const run = async (slug) => {
    setBusy(slug);
    setResults((current) => ({ ...current, [slug]: null }));

    try {
      const payload = await api.tool(slug);
      setResults((current) => ({
        ...current,
        [slug]: { kind: 'ok', message: payload.message || 'Done.' },
      }));
    } catch (error) {
      setResults((current) => ({
        ...current,
        [slug]: { kind: 'error', message: error.message },
      }));
    } finally {
      setBusy(null);
    }
  };

  return (
    <div className="dc:flex dc:flex-col dc:gap-5">
      <Panel
        icon="wrench"
        title="Maintenance"
        description="None of these remove content. They only discard derived files that can be rebuilt."
      >
        <ul className="dc:divide-y dc:divide-line">
          {ACTIONS.map((action) => {
            const result = results[action.slug];
            const running = busy === action.slug;

            return (
              <li key={action.slug} className="dc:px-5 dc:py-4 dc:transition-colors dc:hover:bg-surface-alt">
                <div className="dc:flex dc:items-start dc:justify-between dc:gap-6">
                  <div className="dc:flex dc:min-w-0 dc:gap-3">
                    <span className="dc:mt-0.5 dc:flex dc:h-8 dc:w-8 dc:shrink-0 dc:items-center dc:justify-center dc:rounded-lg dc:bg-brand-tint dc:text-brand">
                      <Icon name={action.icon} size={16} />
                    </span>

                    <div className="dc:min-w-0">
                      <p className="dc:text-label dc:font-semibold dc:text-ink">{action.label}</p>
                      <p className="dc:mt-1 dc:max-w-prose dc:text-body dc:text-muted">
                        {action.help}
                      </p>
                    </div>
                  </div>

                  <Button
                    onClick={() => run(action.slug)}
                    disabled={busy !== null}
                    busy={running}
                    busyLabel="Working…"
                    className="dc:shrink-0"
                  >
                    Run
                  </Button>
                </div>

                {result && (
                  <p
                    role={result.kind === 'error' ? 'alert' : 'status'}
                    className={`dc:mt-3 dc:ml-11 dc:flex dc:items-start dc:gap-2 dc:rounded-md dc:px-3 dc:py-2 dc:text-body ${
                      result.kind === 'error'
                        ? 'dc:bg-danger-tint dc:font-semibold dc:text-danger'
                        : 'dc:bg-ok-tint dc:text-ink-soft'
                    }`}
                  >
                    <Icon
                      name={result.kind === 'error' ? 'alert' : 'check'}
                      size={14}
                      className={`dc:mt-0.5 dc:shrink-0 ${
                        result.kind === 'error' ? 'dc:text-danger' : 'dc:text-ok'
                      }`}
                    />
                    <span>{result.message}</span>
                  </p>
                )}
              </li>
            );
          })}
        </ul>
      </Panel>

      <SystemInfo />
    </div>
  );
}
