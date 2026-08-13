import Icon from './Icon';

/**
 * The screen's buttons, in one place.
 *
 * These were seven hand-written class strings across four files, and they had
 * drifted the way duplicated strings do — the widgets panel's two bulk buttons
 * disagreed about their hover border, and the maintenance and copy buttons were
 * identical except that one of them had picked up a different text size. Naming
 * the variants makes "the secondary button" a thing that exists rather than a
 * string somebody re-typed.
 *
 * The focus ring is defined here too. wp-admin's own focus styles do not reach
 * inside the app, and nothing in the previous strings replaced them, so every
 * button on this screen was focusable with no visible indication of it.
 */

const BASE =
  'dc:inline-flex dc:items-center dc:justify-center dc:gap-2 dc:rounded-md dc:px-3 dc:py-2 ' +
  'dc:text-body dc:font-semibold dc:transition-all dc:duration-150 ' +
  'dc:focus-visible:outline-2 dc:focus-visible:outline-offset-2 dc:focus-visible:outline-brand ' +
  'dc:disabled:cursor-not-allowed dc:disabled:opacity-50 dc:disabled:shadow-none';

const VARIANTS = {
  /** The one committing action on screen. Never more than one at a time. */
  primary: 'dc:bg-brand dc:text-white dc:shadow-brand dc:hover:bg-brand-600 dc:hover:shadow-lift',

  /** Outlined, warming to brand on hover. The default for a real action. */
  secondary:
    'dc:border dc:border-line-strong dc:text-ink-soft ' +
    'dc:hover:border-brand dc:hover:bg-brand-tint dc:hover:text-brand-700',

  /** Outlined, staying neutral. For the destructive half of a pair, where
      warming to brand would advertise the wrong one of the two. */
  quiet:
    'dc:border dc:border-line-strong dc:text-ink-soft ' +
    'dc:hover:bg-surface-sunk dc:hover:text-ink',

  /** No chrome until hovered. For actions that should not compete. */
  ghost: 'dc:text-muted dc:hover:bg-surface-sunk dc:hover:text-ink',

  /** Solid ink. Matches the filter chips' selected state, so the empty state's
      reset button reads as the same family as the chips it clears. */
  dark: 'dc:bg-ink dc:text-white dc:hover:bg-ink-soft',

  /** A settled confirmation, not an action — the copy button after copying. */
  ok: 'dc:border dc:border-ok dc:bg-ok-tint dc:text-ok',
};

export default function Button({
  variant = 'secondary',
  icon,
  busy = false,
  busyLabel,
  children,
  className = '',
  disabled,
  ...rest
}) {
  const spinnerTone = variant === 'primary' ? 'dc:border-white' : 'dc:border-brand';

  return (
    <button
      type="button"
      disabled={disabled || busy}
      // Announced rather than merely spun: the spinner is aria-hidden, so
      // without this a screen reader hears nothing between click and result.
      aria-busy={busy || undefined}
      className={`${BASE} ${VARIANTS[variant] ?? VARIANTS.secondary} ${className}`.trim()}
      {...rest}
    >
      {busy ? (
        <span
          aria-hidden="true"
          className={`dc:h-3.5 dc:w-3.5 dc:animate-spin dc:rounded-full dc:border-2 dc:border-t-transparent ${spinnerTone}`}
        />
      ) : (
        icon && <Icon name={icon} size={14} />
      )}
      {busy && busyLabel ? busyLabel : children}
    </button>
  );
}
