import { useState } from 'react';
import { api } from '../api';
import SystemInfo from './SystemInfo';

const ACTIONS = [
  {
    slug: 'flush-assets',
    label: 'Rebuild widget assets',
    help: 'Deletes the combined CSS and JS bundles. They rebuild on the next page view. Use this after editing a widget stylesheet, since bundle filenames only change with the plugin version outside debug mode.',
  },
  {
    slug: 'recompile-conditions',
    label: 'Recompile display conditions',
    help: 'Rebuilds the header and footer lookup table. It recompiles on every template save, so this is only needed if a template was changed outside the editor.',
  },
  {
    slug: 'clear-cache',
    label: 'Clear caches',
    help: "Clears Elementor's generated CSS and the object cache. Do this after changing the palette, since compiled files still carry the old values.",
  },
];

/**
 * Maintenance actions and the system read-out.
 */
export default function Tools() {
  const [busy, setBusy] = useState(null);
  const [result, setResult] = useState(null);

  const run = async (slug) => {
    setBusy(slug);
    setResult(null);

    try {
      const payload = await api.tool(slug);
      setResult({ kind: 'ok', message: payload.message || 'Done.' });
    } catch (error) {
      setResult({ kind: 'error', message: error.message });
    } finally {
      setBusy(null);
    }
  };

  return (
    <div className="dc:flex dc:flex-col dc:gap-6">
      <div className="dc:rounded-md dc:border dc:border-line dc:bg-surface">
        <div className="dc:border-b dc:border-line dc:px-5 dc:py-4">
          <h2 className="dc:text-[15px] dc:font-semibold dc:text-ink">Maintenance</h2>
          <p className="dc:mt-1 dc:text-[13px] dc:text-muted">
            None of these remove content. They only discard derived files that can be rebuilt.
          </p>
        </div>

        <ul className="dc:divide-y dc:divide-line">
          {ACTIONS.map((action) => (
            <li key={action.slug} className="dc:flex dc:items-start dc:justify-between dc:gap-6 dc:px-5 dc:py-4">
              <div className="dc:min-w-0">
                <p className="dc:text-sm dc:font-semibold dc:text-ink">{action.label}</p>
                <p className="dc:mt-1 dc:max-w-prose dc:text-[13px] dc:leading-relaxed dc:text-muted">{action.help}</p>
              </div>

              <button
                type="button"
                onClick={() => run(action.slug)}
                disabled={busy !== null}
                className="dc:shrink-0 dc:rounded-sm dc:border dc:border-line-strong dc:px-3 dc:py-2 dc:text-[13px] dc:font-semibold dc:transition-colors hover:dc:bg-surface-alt disabled:dc:opacity-50"
              >
                {busy === action.slug ? 'Working…' : 'Run'}
              </button>
            </li>
          ))}
        </ul>

        {result && (
          <p
            role={result.kind === 'error' ? 'alert' : 'status'}
            className={`dc:border-t dc:border-line dc:px-5 dc:py-3 dc:text-[13px] ${
              result.kind === 'error' ? 'dc:font-semibold dc:text-danger' : 'dc:text-ink'
            }`}
          >
            {result.message}
          </p>
        )}
      </div>

      <SystemInfo />
    </div>
  );
}
