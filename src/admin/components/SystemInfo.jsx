import { useEffect, useState } from 'react';
import { config } from '../api';
import Button from './Button';
import Panel from './Panel';

/**
 * The read-out support asks for first.
 *
 * Copy-to-clipboard rather than "please screenshot your screen": the whole
 * point is that it can be pasted into a ticket in one action.
 */

/**
 * Classifies a value so the eye can find the bad row without reading all ten.
 *
 * Only the handful of words the read-out actually produces are matched; a
 * version string is left neutral, which is what it should be.
 *
 * @param {string} value Value as printed.
 * @returns {'ok'|'warn'|null} Tone, or null for a plain value.
 */
function tone(value) {
  const text = String(value).toLowerCase();

  if (text === 'not active' || text === 'none' || text === 'no') {
    return 'warn';
  }

  if (text === 'yes' || text === 'persistent') {
    return 'ok';
  }

  return null;
}

export default function SystemInfo() {
  const rows = Object.entries(config.system);
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    if (!copied) return undefined;
    const timer = window.setTimeout(() => setCopied(false), 2000);
    return () => window.clearTimeout(timer);
  }, [copied]);

  const copy = async () => {
    const text = rows.map(([label, value]) => `${label}: ${value}`).join('\n');

    try {
      await navigator.clipboard.writeText(text);
      setCopied(true);
    } catch {
      // Clipboard access can be refused outright (insecure origin, denied
      // permission). Saying nothing would look like the button is broken.
      window.prompt('Copy the report:', text);
    }
  };

  return (
    <Panel
      icon="gauge"
      title="System"
      description="Paste this into a support ticket before describing the problem."
      action={
        <>
          <Button
            variant={copied ? 'ok' : 'secondary'}
            icon={copied ? 'check' : 'copy'}
            onClick={copy}
          >
            {copied ? 'Copied' : 'Copy'}
          </Button>

          {/* The button's own label carries the result visually, but a label
              rewriting itself under a reader's focus is not reliably announced.
              This says it once, out of band. */}
          <span role="status" className="dc:sr-only">
            {copied ? 'System report copied to the clipboard.' : ''}
          </span>
        </>
      }
    >
      <dl className="dc:grid dc:grid-cols-1 dc:gap-px dc:bg-line dc:sm:grid-cols-2">
        {rows.map(([label, value]) => {
          const kind = tone(value);

          return (
            <div
              key={label}
              className="dc:flex dc:items-center dc:justify-between dc:gap-4 dc:bg-surface dc:px-5 dc:py-3"
            >
              <dt className="dc:text-body dc:text-muted">{label}</dt>
              <dd
                className={`dc:shrink-0 dc:font-mono dc:text-help ${
                  kind === 'warn'
                    ? 'dc:rounded-full dc:bg-warn-tint dc:px-2 dc:py-0.5 dc:text-ink-soft'
                    : kind === 'ok'
                      ? 'dc:rounded-full dc:bg-ok-tint dc:px-2 dc:py-0.5 dc:text-ink-soft'
                      : 'dc:text-ink'
                }`}
              >
                {String(value)}
              </dd>
            </div>
          );
        })}
      </dl>
    </Panel>
  );
}
