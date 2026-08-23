import { useMemo, useState } from 'react';
import { config } from '../api';
import Button from './Button';
import Icon from './Icon';
import WidgetCard from './WidgetCard';

/**
 * The widgets screen.
 *
 * Twenty-nine cards in one flat grid is a list, not a screen you can act on.
 * Three things make it usable: a search that matches title, slug and keywords;
 * grouping by the four categories Elementor files them under, so the admin's
 * mental model matches the editor's panel; and one pair of bulk switches,
 * because "turn everything off except what I use" is the common first-run task
 * and doing it one card at a time is twenty-nine clicks.
 *
 * The headings are signposts, not controls. Per-heading bulk switches read as
 * four more decisions to make on the way down the page, and grouping is meant
 * to make the list scannable, not to invent a unit anyone manages as a set.
 */

/**
 * Labels come from PHP, which shares them with the editor's category
 * registration. A category the map does not cover still gets a heading rather
 * than vanishing, since a widget that renders nowhere cannot be switched off.
 *
 * @param {string} category Category slug.
 * @returns {string} Display label.
 */
function categoryLabel(category) {
  if (config.categories[category]) {
    return config.categories[category];
  }

  if (!category) {
    return 'Other';
  }

  return category
    .replace(/^pixelomatic-/, '')
    .replace(/[-_]+/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase());
}

export default function WidgetsPanel({ settings, set }) {
  const [query, setQuery] = useState('');
  const [filter, setFilter] = useState('all');

  const all = useMemo(() => Object.entries(config.widgets), []);

  const counts = useMemo(() => {
    let on = 0;
    let blocked = 0;

    all.forEach(([, widget]) => {
      if ((widget.missing || []).length) {
        blocked += 1;
      } else if (settings[widget.key]) {
        on += 1;
      }
    });

    return { on, blocked, total: all.length };
  }, [all, settings]);

  const visible = useMemo(() => {
    const needle = query.trim().toLowerCase();

    return all.filter(([slug, widget]) => {
      const blocked = (widget.missing || []).length > 0;
      const enabled = Boolean(settings[widget.key]) && !blocked;

      if (filter === 'enabled' && !enabled) return false;
      if (filter === 'disabled' && (enabled || blocked)) return false;
      if (filter === 'blocked' && !blocked) return false;

      if (!needle) return true;

      return [slug, widget.title, categoryLabel(widget.category), ...(widget.keywords || [])]
        .join(' ')
        .toLowerCase()
        .includes(needle);
    });
  }, [all, filter, query, settings]);

  // Grouped for display, but only the widgets that survived the filter, so a
  // group whose every widget was filtered out disappears rather than showing
  // an empty heading.
  const sections = useMemo(() => {
    // Seeded in the categories' own order, so the headings keep one stable
    // sequence instead of reshuffling as the filter changes what is left.
    const map = new Map(Object.keys(config.categories).map((key) => [key, []]));

    visible.forEach(([slug, widget]) => {
      const key = widget.category || '';
      if (!map.has(key)) {
        map.set(key, []);
      }
      map.get(key).push([slug, widget]);
    });

    return Array.from(map.entries()).filter(([, entries]) => entries.length > 0);
  }, [visible]);

  /**
   * Switches every widget in a list, skipping any with unmet dependencies —
   * enabling one would be a setting the server has to refuse.
   *
   * @param {Array}   entries Widget entries.
   * @param {boolean} value   Desired state.
   * @returns {void}
   */
  const setMany = (entries, value) => {
    entries.forEach(([, widget]) => {
      if (!(widget.missing || []).length) {
        set(widget.key, value);
      }
    });
  };

  const narrowed = visible.length !== all.length;

  const FILTERS = [
    { key: 'all', label: 'All', count: counts.total },
    { key: 'enabled', label: 'Enabled', count: counts.on },
    { key: 'disabled', label: 'Disabled', count: counts.total - counts.on - counts.blocked },
    { key: 'blocked', label: 'Unavailable', count: counts.blocked },
  ];

  return (
    <section className="dc:flex dc:flex-col dc:gap-5">
      <div className="dc:rounded-xl dc:border dc:border-line dc:bg-surface dc:p-4 dc:shadow-card">
        <div className="dc:flex dc:flex-wrap dc:items-center dc:gap-3">
          <div className="dc:relative dc:min-w-56 dc:flex-1">
            <Icon
              name="search"
              size={16}
              className="dc:pointer-events-none dc:absolute dc:top-1/2 dc:left-3 dc:-translate-y-1/2 dc:text-muted-alt"
            />
            <input
              type="search"
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              placeholder="Search widgets, keywords or slugs…"
              aria-label="Search widgets"
              className="dc:w-full dc:rounded-md dc:border dc:border-line-strong dc:bg-surface-alt dc:py-2 dc:pr-3 dc:pl-9 dc:text-label dc:transition-colors dc:hover:border-brand-400 dc:focus:border-brand dc:focus:bg-surface dc:focus:ring-2 dc:focus:ring-brand-tint"
            />
          </div>

          {/* These act on what is currently listed, not on all twenty-nine.
              Acting on the whole set while the screen shows six of them would
              be a bulk edit the reader cannot see, so the label counts the
              rows instead of saying "all" once a filter is on. */}
          <div className="dc:flex dc:items-center dc:gap-2">
            <Button onClick={() => setMany(visible, true)}>
              {narrowed ? `Enable ${visible.length}` : 'Enable all'}
            </Button>
            <Button variant="quiet" onClick={() => setMany(visible, false)}>
              {narrowed ? `Disable ${visible.length}` : 'Disable all'}
            </Button>
          </div>
        </div>

        {/* Grouped and named, so the four chips are announced as one filter
            control rather than four unrelated toggle buttons in a row. */}
        <div
          role="group"
          aria-label="Filter widgets by state"
          className="dc:mt-3 dc:flex dc:flex-wrap dc:gap-1.5"
        >
          {FILTERS.map((entry) => (
            <button
              key={entry.key}
              type="button"
              onClick={() => setFilter(entry.key)}
              aria-pressed={filter === entry.key}
              className={`dc:inline-flex dc:items-center dc:gap-1.5 dc:rounded-full dc:px-3 dc:py-1 dc:text-help dc:font-semibold dc:transition-colors dc:focus-visible:outline-2 dc:focus-visible:outline-offset-2 dc:focus-visible:outline-brand ${
                filter === entry.key
                  ? 'dc:bg-ink dc:text-white'
                  : 'dc:bg-surface-sunk dc:text-muted dc:hover:bg-line dc:hover:text-ink'
              }`}
            >
              {entry.label}
              <span
                className={`dc:font-mono dc:text-micro ${
                  filter === entry.key ? 'dc:text-white/80' : 'dc:text-muted'
                }`}
              >
                {entry.count}
              </span>
            </button>
          ))}
        </div>
      </div>

      {/* Searching and filtering rewrote the grid in silence — the only signal
          was cards vanishing, which is no signal at all if you cannot see them.
          The surviving count is the one fact worth announcing. */}
      <p role="status" aria-live="polite" className="dc:sr-only">
        {visible.length === all.length
          ? `Showing all ${all.length} widgets.`
          : `Showing ${visible.length} of ${all.length} widgets.`}
      </p>

      <p className="dc:max-w-prose dc:text-body dc:text-muted">
        Switching a widget off removes it from the Elementor panel and stops registering its
        assets. Pages already using it will render nothing in its place.
      </p>

      {sections.length === 0 ? (
        <div className="dc:rounded-xl dc:border dc:border-dashed dc:border-line-strong dc:bg-surface-alt dc:px-6 dc:py-12 dc:text-center">
          <p className="dc:text-label dc:font-semibold dc:text-ink">No widgets match that.</p>
          <p className="dc:mt-1 dc:text-body dc:text-muted">
            Try a different term, or clear the filter.
          </p>
          <Button
            variant="dark"
            className="dc:mt-4"
            onClick={() => {
              setQuery('');
              setFilter('all');
            }}
          >
            Reset filters
          </Button>
        </div>
      ) : (
        sections.map(([category, entries]) => (
          <div key={category || 'other'}>
            <div className="dc:mb-3 dc:flex dc:items-center dc:gap-3">
              <h3 className="dc:font-mono dc:text-micro dc:tracking-[0.16em] dc:text-muted dc:uppercase">
                {categoryLabel(category)}
              </h3>
              <span className="dc:font-mono dc:text-micro dc:text-muted">
                {entries.length}
              </span>
              <span className="dc:h-px dc:flex-1 dc:bg-line" />
            </div>

            <div className="dc:grid dc:gap-3 dc:sm:grid-cols-2 dc:xl:grid-cols-3">
              {entries.map(([slug, widget]) => (
                <WidgetCard
                  key={slug}
                  slug={slug}
                  widget={widget}
                  enabled={Boolean(settings[widget.key])}
                  onChange={(value) => set(widget.key, value)}
                />
              ))}
            </div>
          </div>
        ))
      )}
    </section>
  );
}
