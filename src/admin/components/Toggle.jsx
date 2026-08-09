/**
 * A switch.
 *
 * A real checkbox underneath, visually hidden: it keeps keyboard behaviour,
 * form semantics and screen-reader announcements without reimplementing any
 * of them on a div.
 */
export default function Toggle({ id, checked, onChange, label, help, disabled }) {
  return (
    <div className="dc:flex dc:items-start dc:gap-4 dc:py-4">
      <label
        htmlFor={id}
        className={`dc:relative dc:mt-0.5 dc:inline-flex dc:h-5 dc:w-9 dc:shrink-0 dc:cursor-pointer dc:rounded-full dc:transition-colors ${
          disabled ? 'dc:cursor-not-allowed dc:opacity-50' : ''
        } ${checked ? 'dc:bg-brand-600' : 'dc:bg-line-strong'}`}
      >
        <input
          id={id}
          type="checkbox"
          className="dc:sr-only dc:peer"
          checked={checked}
          disabled={disabled}
          onChange={(event) => onChange(event.target.checked)}
        />
        <span
          className={`dc:pointer-events-none dc:absolute dc:top-0.5 dc:left-0.5 dc:h-4 dc:w-4 dc:rounded-full dc:bg-surface dc:shadow-sm dc:transition-transform ${
            checked ? 'dc:translate-x-4' : ''
          } dc:peer-focus-visible:ring-2 dc:peer-focus-visible:ring-brand dc:peer-focus-visible:ring-offset-2`}
        />
      </label>

      <div className="dc:min-w-0">
        <label htmlFor={id} className="dc:block dc:cursor-pointer dc:text-sm dc:font-semibold dc:text-ink">
          {label}
        </label>
        {help && <p className="dc:mt-1 dc:max-w-prose dc:text-[13px] dc:leading-relaxed dc:text-muted">{help}</p>}
      </div>
    </div>
  );
}
