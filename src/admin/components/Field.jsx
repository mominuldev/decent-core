import Toggle from './Toggle';

/**
 * Renders one setting from its schema entry.
 *
 * The schema is the same array PHP validates against, so the control a user
 * sees and the rule the server enforces cannot disagree — a number field's
 * min and max are the same two values in both places.
 */

/**
 * Human labels for enumerated values.
 *
 * The schema's `allowed` list is machine keys, because that is what gets
 * stored and validated. Showing "price-asc" in a dropdown asks the reader to
 * decode it; anything not listed here falls back to a de-slugged version of
 * the key, so a new option is never invisible, only unpolished.
 */
const OPTION_LABELS = {
  popular: 'Most popular',
  new: 'Newest first',
  'price-asc': 'Price: low to high',
  'price-desc': 'Price: high to low',
  rating: 'Highest rated',
  title: 'Title (A–Z)',
};

function optionLabel(value) {
  if (OPTION_LABELS[value]) {
    return OPTION_LABELS[value];
  }

  const words = String(value).replace(/[-_]+/g, ' ');
  return words.charAt(0).toUpperCase() + words.slice(1);
}

export default function Field({ name, field, value, onChange }) {
  const id = `dc-${name}`;

  if (field.type === 'boolean') {
    return (
      <Toggle
        id={id}
        checked={Boolean(value)}
        onChange={onChange}
        label={field.label}
        help={field.help}
      />
    );
  }

  const isNumber = field.type === 'integer';

  /**
   * Keeps a stepped value inside the schema's range.
   *
   * @param {number} next Proposed value.
   * @returns {number} Clamped value.
   */
  const clamp = (next) => {
    const min = field.min ?? Number.NEGATIVE_INFINITY;
    const max = field.max ?? Number.POSITIVE_INFINITY;
    return Math.min(max, Math.max(min, next));
  };

  const atMin = isNumber && field.min !== null && Number(value) <= field.min;
  const atMax = isNumber && field.max !== null && Number(value) >= field.max;

  return (
    <div className="dc:flex dc:flex-col dc:gap-4 dc:px-5 dc:py-4 dc:transition-colors dc:hover:bg-surface-alt dc:sm:flex-row dc:sm:items-start dc:sm:justify-between">
      <div className="dc:min-w-0 dc:flex-1">
        <label htmlFor={id} className="dc:block dc:text-label dc:font-semibold dc:text-ink">
          {field.label}
        </label>

        {field.help && (
          <p className="dc:mt-1 dc:max-w-prose dc:text-body dc:text-muted">{field.help}</p>
        )}
      </div>

      <div className="dc:shrink-0">
        {field.allowed ? (
          <div className="dc:relative">
            <select
              id={id}
              value={value}
              onChange={(event) => onChange(event.target.value)}
              className="dc:w-full dc:min-w-52 dc:appearance-none dc:rounded-md dc:border dc:border-line-strong dc:bg-surface dc:py-2 dc:pr-9 dc:pl-3 dc:text-label dc:font-medium dc:transition-colors dc:hover:border-brand-400 dc:focus:border-brand dc:focus:ring-2 dc:focus:ring-brand-tint"
            >
              {field.allowed.map((option) => (
                <option key={option} value={option}>
                  {optionLabel(option)}
                </option>
              ))}
            </select>

            <svg
              className="dc:pointer-events-none dc:absolute dc:top-1/2 dc:right-3 dc:-translate-y-1/2 dc:text-muted"
              width="14"
              height="14"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              aria-hidden="true"
            >
              <path d="M6 9l6 6 6-6" />
            </svg>
          </div>
        ) : isNumber ? (
          <div className="dc:flex dc:flex-col dc:items-start dc:gap-1.5 dc:sm:items-end">
            <div className="dc:inline-flex dc:items-stretch dc:overflow-hidden dc:rounded-md dc:border dc:border-line-strong dc:bg-surface dc:transition-colors dc:focus-within:border-brand dc:focus-within:ring-2 dc:focus-within:ring-brand-tint">
              <button
                type="button"
                aria-label={`Decrease ${field.label}`}
                disabled={atMin}
                onClick={() => onChange(clamp(Number(value) - 1))}
                // Tinted rather than transparent: against a white value the two
                // steppers were invisible until hovered, so the control read as
                // a plain number box with two characters printed beside it.
                className="dc:flex dc:w-9 dc:items-center dc:justify-center dc:bg-surface-alt dc:text-lg dc:leading-none dc:font-medium dc:text-muted dc:transition-colors dc:hover:bg-surface-sunk dc:hover:text-ink dc:disabled:cursor-not-allowed dc:disabled:opacity-40"
              >
                &minus;
              </button>

              <input
                id={id}
                type="number"
                value={value}
                min={field.min ?? undefined}
                max={field.max ?? undefined}
                // An empty box is a state you pass through on the way to typing
                // a different number. Coercing it to 0 on the keystroke rewrote
                // the field under the cursor — and where the schema's floor is
                // above zero, the value it wrote was one the server rejects.
                // The empty string is allowed to stand until blur settles it.
                onChange={(event) => {
                  const raw = event.target.value;
                  onChange(raw === '' ? '' : Number(raw));
                }}
                onBlur={(event) => {
                  const raw = event.target.value;
                  onChange(clamp(raw === '' ? (field.min ?? 0) : Number(raw)));
                }}
                className="dc:w-16 dc:border-x dc:border-line dc:bg-transparent dc:py-2 dc:text-center dc:text-label dc:font-semibold dc:tabular-nums"
              />

              <button
                type="button"
                aria-label={`Increase ${field.label}`}
                disabled={atMax}
                onClick={() => onChange(clamp(Number(value) + 1))}
                // Tinted rather than transparent: against a white value the two
                // steppers were invisible until hovered, so the control read as
                // a plain number box with two characters printed beside it.
                className="dc:flex dc:w-9 dc:items-center dc:justify-center dc:bg-surface-alt dc:text-lg dc:leading-none dc:font-medium dc:text-muted dc:transition-colors dc:hover:bg-surface-sunk dc:hover:text-ink dc:disabled:cursor-not-allowed dc:disabled:opacity-40"
              >
                +
              </button>
            </div>

            {field.min !== null && field.max !== null && (
              <span className="dc:font-mono dc:text-micro dc:tracking-wide dc:text-muted-alt">
                {field.min}&ndash;{field.max}
              </span>
            )}
          </div>
        ) : (
          <input
            id={id}
            type="text"
            value={value}
            onChange={(event) => onChange(event.target.value)}
            className="dc:w-full dc:min-w-52 dc:rounded-md dc:border dc:border-line-strong dc:bg-surface dc:px-3 dc:py-2 dc:text-label dc:transition-colors dc:hover:border-brand-400 dc:focus:border-brand dc:focus:ring-2 dc:focus:ring-brand-tint"
          />
        )}
      </div>
    </div>
  );
}
