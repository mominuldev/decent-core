import Icon from './Icon';
import { Switch } from './Toggle';

/**
 * One widget's enable/disable card.
 *
 * Generated from config/widgets.php, so the list here cannot drift from what
 * Elementor actually registers.
 *
 * The whole card is the hit area, not just the switch: with twenty of these on
 * screen, a 44px target beats a 20px one every time. The switch keeps its own
 * input for keyboard and screen-reader users, and stops the card's click from
 * firing a second toggle that would undo it.
 */
export default function WidgetCard({ slug, widget, enabled, onChange }) {
  const blocked = (widget.missing || []).length > 0;
  const on = enabled && !blocked;

  return (
    <div
      // Deliberately not role="button": the card is a pointer convenience, and
      // the switch inside it is the real control — already focusable, already
      // announced. A second button role here would announce a control that
      // duplicates it, or worse, one that keyboard users cannot reach.
      onClick={() => !blocked && onChange(!enabled)}
      className={`dc:flex dc:flex-col dc:rounded-xl dc:border dc:bg-surface dc:p-4 dc:transition-all dc:duration-200 ${
        blocked
          ? 'dc:border-line dc:bg-surface-alt'
          : `dc:cursor-pointer dc:hover:-translate-y-0.5 dc:hover:shadow-lift ${
              on ? 'dc:border-brand-tint-line dc:shadow-card' : 'dc:border-line'
            }`
      }`}
    >
      <div className="dc:flex dc:items-start dc:justify-between dc:gap-3">
        <div className="dc:flex dc:min-w-0 dc:gap-2.5">
          {/* Marks the enabled ones at a glance down the grid without twenty
              saturated blocks fighting the text. It replaced a full-height bar
              on the card's left edge, which the card's own corner radius
              clipped into a wedge at both ends.

              It rules the title and the slug together rather than sitting
              inside the heading, so the two stay left-aligned with each other.
              Always rendered, transparent when off: dropping the element would
              shift every title in the grid each time a switch was flipped. */}
          <span
            aria-hidden="true"
            className={`dc:w-[3px] dc:shrink-0 dc:self-stretch dc:rounded-full dc:transition-colors ${
              on ? 'dc:bg-brand' : 'dc:bg-transparent'
            }`}
          />

          <div className="dc:min-w-0">
            <h3 className="dc:truncate dc:text-title dc:font-semibold dc:text-ink">
              {widget.title}
            </h3>
            <p className="dc:mt-0.5 dc:truncate dc:font-mono dc:text-micro dc:tracking-wide dc:text-muted-alt">
              {slug}
            </p>
          </div>
        </div>

        <div onClick={(event) => event.stopPropagation()}>
          <Switch
            id={`dc-widget-${slug}`}
            checked={on}
            disabled={blocked}
            onChange={onChange}
            label={widget.title}
          />
        </div>
      </div>

      {blocked ? (
        <p className="dc:mt-3 dc:flex dc:items-start dc:gap-2 dc:rounded-md dc:bg-warn-tint dc:px-2.5 dc:py-2 dc:text-help dc:text-ink-soft">
          <Icon name="alert" size={14} className="dc:mt-0.5 dc:shrink-0 dc:text-warn" />
          <span>
            Needs {widget.missing.join(', ')}. The widget stays out of the panel until then.
          </span>
        </p>
      ) : widget.keywords && widget.keywords.length ? (
        <ul className="dc:mt-3 dc:flex dc:flex-wrap dc:gap-1.5">
          {widget.keywords.map((keyword) => (
            <li
              key={keyword}
              // Outlined rather than filled: a card carrying four of these had
              // more of its area given to grey blocks than to the widget's own
              // name.
              className="dc:rounded-full dc:border dc:border-line dc:bg-surface-alt dc:px-2 dc:py-0.5 dc:text-micro dc:font-medium dc:text-muted"
            >
              {keyword}
            </li>
          ))}
        </ul>
      ) : (
        <p className="dc:mt-3 dc:text-help dc:text-muted-alt">No keywords</p>
      )}
    </div>
  );
}
