/**
 * Inline SVG icons.
 *
 * Inline rather than an icon font, for the same reason the theme uses inline
 * SVG: no extra request, no FOUT, and `currentColor` means an icon inherits
 * whatever state its container is in without a second class.
 *
 * Every path is drawn on a 24-unit grid with a 1.75 stroke so the set stays
 * optically consistent at the 16-18px it is actually used at.
 */

const PATHS = {
  sliders: 'M4 6h10M18 6h2M4 12h2M10 12h10M4 18h8M16 18h4M14 4v4M8 10v4M12 16v4',
  grid: 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
  puzzle:
    'M10 4h4v2.5a1.5 1.5 0 1 0 3 0V4h3v4h-2.5a1.5 1.5 0 1 0 0 3H20v4h-3v2.5a1.5 1.5 0 1 1-3 0V15h-4v5H6v-3.5a1.5 1.5 0 1 1-2 0V13h2.5a1.5 1.5 0 1 0 0-3H4V6h6z',
  tag: 'M3 12.5V4h8.5L20 12.5 12.5 20zM7.5 7.5h.01',
  gauge: 'M12 20a8 8 0 1 1 8-8M12 12l5-3',
  wrench:
    'M14.5 4a5.5 5.5 0 0 0-5 7.7L4 17.2V20h2.8l5.5-5.5A5.5 5.5 0 1 0 14.5 4z',
  search: 'M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14zM20 20l-4-4',
  check: 'M4 12.5 9 17.5 20 6.5',
  close: 'M6 6l12 12M18 6L6 18',
  alert: 'M12 8v5M12 16.5v.01M12 3 2 20h20z',
  copy: 'M9 9h11v11H9zM5 15H4V4h11v1',
  refresh: 'M20 12a8 8 0 1 1-2.5-5.8M20 4v4h-4',
  spark: 'M12 3l1.9 5.6L19.5 10l-5.6 1.9L12 17.5l-1.9-5.6L4.5 10l5.6-1.4z',
  undo: 'M4 9h9a5 5 0 1 1 0 10H8M4 9l4-4M4 9l4 4',
  save: 'M5 4h11l3 3v13H5zM8 4v5h7V4M8 14h8v6H8z',
  chevron: 'M9 5l7 7-7 7',
};

export default function Icon({ name, className = '', size = 18 }) {
  const path = PATHS[name];

  if (!path) {
    return null;
  }

  return (
    <svg
      className={className}
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.75"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
      focusable="false"
    >
      <path d={path} />
    </svg>
  );
}
