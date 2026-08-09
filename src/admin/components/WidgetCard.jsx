/**
 * One widget's enable/disable card.
 *
 * Generated from config/widgets.php, so the list here cannot drift from what
 * Elementor actually registers.
 */
export default function WidgetCard({ slug, widget, enabled, onChange }) {
  const blocked = (widget.missing || []).length > 0;

  return (
    <div
      className={`dc:group dc:relative dc:flex dc:flex-col dc:rounded-md dc:border dc:bg-surface dc:p-5 dc:transition-shadow ${
        blocked ? 'dc:border-line dc:opacity-70' : 'dc:border-line hover:dc:shadow-lift'
      }`}
    >
      <div className="dc:flex dc:items-start dc:justify-between dc:gap-4">
        <div className="dc:min-w-0">
          <p className="dc:font-mono dc:text-[10px] dc:tracking-widest dc:text-muted-alt dc:uppercase">
            {widget.group || 'widget'}
          </p>
          <h3 className="dc:mt-1.5 dc:text-[15px] dc:font-semibold dc:text-ink">{widget.title}</h3>
        </div>

        <label
          className={`dc:relative dc:inline-flex dc:h-5 dc:w-9 dc:shrink-0 dc:rounded-full dc:transition-colors ${
            blocked ? 'dc:cursor-not-allowed dc:bg-line' : 'dc:cursor-pointer'
          } ${enabled && !blocked ? 'dc:bg-brand-600' : 'dc:bg-line-strong'}`}
        >
          <input
            type="checkbox"
            className="dc:sr-only dc:peer"
            checked={enabled && !blocked}
            disabled={blocked}
            onChange={(event) => onChange(event.target.checked)}
          />
          <span className="dc:sr-only">{widget.title}</span>
          <span
            className={`dc:pointer-events-none dc:absolute dc:top-0.5 dc:left-0.5 dc:h-4 dc:w-4 dc:rounded-full dc:bg-surface dc:shadow-sm dc:transition-transform ${
              enabled && !blocked ? 'dc:translate-x-4' : ''
            } dc:peer-focus-visible:ring-2 dc:peer-focus-visible:ring-brand dc:peer-focus-visible:ring-offset-2`}
          />
        </label>
      </div>

      {blocked ? (
        <p className="dc:mt-3 dc:rounded-sm dc:bg-warn-tint dc:px-3 dc:py-2 dc:text-[12px] dc:leading-relaxed dc:text-ink">
          Needs {widget.missing.join(', ')}. The widget stays out of the panel until then.
        </p>
      ) : (
        <p className="dc:mt-3 dc:text-[12px] dc:leading-relaxed dc:text-muted">
          {widget.keywords && widget.keywords.length
            ? widget.keywords.join(' · ')
            : 'No keywords'}
        </p>
      )}

      <p className="dc:mt-auto dc:pt-4 dc:font-mono dc:text-[10px] dc:tracking-wide dc:text-muted-alt">
        {slug}
      </p>
    </div>
  );
}
