import Toggle from './Toggle';

/**
 * Renders one setting from its schema entry.
 *
 * The schema is the same array PHP validates against, so the control a user
 * sees and the rule the server enforces cannot disagree — a number field's
 * min and max are the same two values in both places.
 */
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

  return (
    <div className="dc:py-4">
      <label htmlFor={id} className="dc:block dc:text-sm dc:font-semibold dc:text-ink">
        {field.label}
      </label>

      {field.help && <p className="dc:mt-1 dc:max-w-prose dc:text-[13px] dc:leading-relaxed dc:text-muted">{field.help}</p>}

      <div className="dc:mt-3">
        {field.allowed ? (
          <select
            id={id}
            value={value}
            onChange={(event) => onChange(event.target.value)}
            className="dc:w-full dc:max-w-xs dc:rounded-sm dc:border dc:border-line-strong dc:bg-surface dc:px-3 dc:py-2 dc:text-sm dc:transition-colors focus:dc:border-brand focus:dc:outline-none"
          >
            {field.allowed.map((option) => (
              <option key={option} value={option}>
                {option}
              </option>
            ))}
          </select>
        ) : (
          <div className="dc:flex dc:items-center dc:gap-3">
            <input
              id={id}
              type={field.type === 'integer' ? 'number' : 'text'}
              value={value}
              min={field.min}
              max={field.max}
              onChange={(event) =>
                onChange(field.type === 'integer' ? Number(event.target.value) : event.target.value)
              }
              className="dc:w-28 dc:rounded-sm dc:border dc:border-line-strong dc:bg-surface dc:px-3 dc:py-2 dc:text-sm dc:transition-colors focus:dc:border-brand focus:dc:outline-none"
            />
            {field.type === 'integer' && (
              <span className="dc:font-mono dc:text-[11px] dc:tracking-wide dc:text-muted-alt">
                {field.min}–{field.max}
              </span>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
