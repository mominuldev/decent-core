import Icon from './Icon';

/**
 * The card every section on this screen sits in.
 *
 * This markup existed three times — the settings section, the maintenance list
 * and the system read-out — and the copies had already drifted: two different
 * heading sizes, two different header paddings, and only one of them wrapped
 * its header on a narrow viewport. One component is what keeps the tabs looking
 * like one screen rather than three screens that happen to share a palette.
 *
 * `action` is the optional control on the header's right, which today is the
 * system read-out's copy button. The body is left unpadded because every
 * current caller draws its own rows, and a padded shell would mean each of them
 * cancelling it back out.
 */
export default function Panel({ icon, title, description, action, headingId, children }) {
  return (
    <section className="dc:overflow-hidden dc:rounded-xl dc:border dc:border-line dc:bg-surface dc:shadow-card">
      {/* Wraps rather than squeezing: the read-out's description runs to a full
          line, and next to a button on a narrow screen the unwrapped version
          crushed the heading to two characters a line. */}
      <div className="dc:flex dc:flex-wrap dc:items-center dc:justify-between dc:gap-x-4 dc:gap-y-3 dc:border-b dc:border-line dc:bg-surface-alt dc:px-5 dc:py-3.5">
        {/* The icon sits in a tinted chip rather than loose against the words.
            Beside a bare glyph the title had no fixed left edge to hold, so
            each panel's heading started a few pixels from wherever its icon
            happened to end. The chip gives every panel the same one. */}
        <div className="dc:flex dc:min-w-0 dc:items-center dc:gap-3">
          {icon && (
            <span className="dc:flex dc:h-[30px] dc:w-[30px] dc:shrink-0 dc:items-center dc:justify-center dc:rounded-lg dc:bg-brand-tint dc:text-brand">
              <Icon name={icon} size={16} />
            </span>
          )}

          <div className="dc:min-w-0">
            <h2 id={headingId} className="dc:text-title dc:font-semibold dc:text-ink">
              {title}
            </h2>

            {description && (
              <p className="dc:mt-0.5 dc:max-w-prose dc:text-body dc:text-muted">{description}</p>
            )}
          </div>
        </div>

        {action && <div className="dc:shrink-0">{action}</div>}
      </div>

      {children}
    </section>
  );
}
