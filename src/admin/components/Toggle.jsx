/**
 * A switch.
 *
 * A real checkbox underneath, visually hidden: it keeps keyboard behaviour,
 * form semantics and screen-reader announcements without reimplementing any
 * of them on a div.
 *
 * `Switch` is the control on its own, so the widget cards and the settings
 * rows can share one implementation instead of each drawing their own track
 * and knob and drifting apart.
 */

export function Switch({ id, checked, onChange, disabled, label }) {
  return (
    <span
      className={`dc:relative dc:inline-flex dc:h-6 dc:w-11 dc:shrink-0 dc:items-center dc:rounded-full dc:transition-colors dc:duration-200 dc:has-[:focus-visible]:ring-2 dc:has-[:focus-visible]:ring-brand dc:has-[:focus-visible]:ring-offset-2 ${
        disabled
          ? 'dc:cursor-not-allowed dc:bg-line-strong dc:opacity-60'
          : 'dc:cursor-pointer'
      } ${
        checked && !disabled
          ? 'dc:bg-brand'
          : 'dc:bg-line-strong'
      }`}
    >
      <input
        id={id}
        type="checkbox"
        className="dc:absolute dc:inset-0 dc:z-10 dc:h-full dc:w-full dc:cursor-[inherit] dc:opacity-0"
        checked={checked}
        disabled={disabled}
        onChange={(event) => onChange(event.target.checked)}
      />
      {label && <span className="dc:sr-only">{label}</span>}
      <span
        className={`dc:pointer-events-none dc:ml-0.5 dc:h-5 dc:w-5 dc:rounded-full dc:bg-surface dc:shadow-sm dc:transition-transform dc:duration-200 ${
          checked && !disabled ? 'dc:translate-x-5' : 'dc:translate-x-0'
        }`}
      />
    </span>
  );
}

/**
 * A switch with its label and help text, as used in the settings rows.
 */
export default function Toggle({ id, checked, onChange, label, help, disabled }) {
  return (
    <div className="dc:flex dc:items-start dc:gap-4 dc:px-5 dc:py-4 dc:transition-colors dc:hover:bg-surface-alt">
      <div className="dc:min-w-0 dc:flex-1">
        <label
          htmlFor={id}
          className={`dc:block dc:text-label dc:font-semibold dc:text-ink ${
            disabled ? '' : 'dc:cursor-pointer'
          }`}
        >
          {label}
        </label>
        {help && <p className="dc:mt-1 dc:max-w-prose dc:text-body dc:text-muted">{help}</p>}
      </div>

      <div className="dc:mt-0.5 dc:flex dc:items-center dc:gap-3">
        <span
          className={`dc:hidden dc:w-8 dc:text-right dc:font-mono dc:text-micro dc:tracking-widest dc:uppercase dc:transition-colors dc:sm:block ${
            checked ? 'dc:text-brand' : 'dc:text-muted-alt'
          }`}
          aria-hidden="true"
        >
          {checked ? 'On' : 'Off'}
        </span>
        <Switch
          id={id}
          checked={checked}
          onChange={onChange}
          disabled={disabled}
          label={label}
        />
      </div>
    </div>
  );
}
