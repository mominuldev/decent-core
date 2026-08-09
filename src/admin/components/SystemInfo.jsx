import { config } from '../api';

/**
 * The read-out support asks for first.
 *
 * Copy-to-clipboard rather than "please screenshot your screen": the whole
 * point is that it can be pasted into a ticket in one action.
 */
export default function SystemInfo() {
  const rows = Object.entries(config.system);

  const copy = () => {
    const text = rows.map(([label, value]) => `${label}: ${value}`).join('\n');
    navigator.clipboard?.writeText(text);
  };

  return (
    <div className="dc:rounded-md dc:border dc:border-line dc:bg-surface">
      <div className="dc:flex dc:items-center dc:justify-between dc:border-b dc:border-line dc:px-5 dc:py-4">
        <div>
          <h2 className="dc:text-[15px] dc:font-semibold dc:text-ink">System</h2>
          <p className="dc:mt-1 dc:text-[13px] dc:text-muted">
            Paste this into a support ticket before describing the problem.
          </p>
        </div>

        <button
          type="button"
          onClick={copy}
          className="dc:rounded-sm dc:border dc:border-line-strong dc:px-3 dc:py-2 dc:text-[13px] dc:font-semibold dc:transition-colors hover:dc:bg-surface-alt"
        >
          Copy
        </button>
      </div>

      <dl className="dc:divide-y dc:divide-line">
        {rows.map(([label, value]) => (
          <div key={label} className="dc:flex dc:items-center dc:justify-between dc:gap-6 dc:px-5 dc:py-3">
            <dt className="dc:text-[13px] dc:text-muted">{label}</dt>
            <dd className="dc:font-mono dc:text-[12px] dc:text-ink">{String(value)}</dd>
          </div>
        ))}
      </dl>
    </div>
  );
}
