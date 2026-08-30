/**
 * Table of Contents — the list, and the marker that follows the reader.
 *
 * Two jobs, and which of them runs depends on how the widget was set up:
 *
 *   1. Where the list is built from headings, this is what builds it. The rail
 *      ships `hidden`; the script finds the headings, gives each one an id if
 *      it has none, writes the list and reveals the rail. A page with no
 *      matching heading is therefore a page with no rail rather than a rail
 *      with nothing in it.
 *   2. Either way it moves `aria-current` down the list as the page scrolls,
 *      through an IntersectionObserver with the same margins the theme's own
 *      article rail uses. Where a written list is used the links already work
 *      without this; the marker is the enhancement, not the navigation.
 *
 * Binds from `frontend/element_ready`, which is what makes it work in the
 * editor: a widget dropped on the canvas arrives as markup from an AJAX
 * render, long after any load event. That hook fires again on every control
 * change, so both halves are written to be run twice — the list is rebuilt
 * from empty and the previous observer is disconnected before a new one is
 * made, rather than a second one being stacked on the first.
 */
(function ($) {
  'use strict';

  var LEVELS = { h2: true, h3: true, h4: true, h5: true, h6: true };

  /* The element the headings are looked for in. An editor's selector first,
     then the page's main content region, then the whole document — a rail
     that searched the document by default would list the header's headings
     too. An unparseable selector is a typo in the panel, not a reason to give
     up, so it falls through to the same default. */
  function scopeFor(selector) {
    if (selector) {
      try {
        var picked = document.querySelector(selector);
        if (picked) return picked;
      } catch (error) {
        // Fall through.
      }
    }

    return document.querySelector('main, .site-main, [role="main"]') || document.body;
  }

  function levelsFor(root) {
    var asked = (root.getAttribute('data-toc-levels') || 'h2').split(',');
    var levels = [];

    asked.forEach(function (level) {
      level = level.trim().toLowerCase();

      if (LEVELS[level] && levels.indexOf(level) === -1) levels.push(level);
    });

    return levels.length ? levels : ['h2'];
  }

  /* A heading the editor gave an id keeps it — that id may already be linked
     from somewhere else. Anything else gets one made from its own words, and
     the document itself is what the uniqueness is checked against, so a second
     "Overview" further down cannot take the first one's anchor. */
  function idFor(heading) {
    var existing = heading.getAttribute('id');

    if (existing) return existing;

    var base = (heading.textContent || '')
      .toLowerCase()
      .replace(/[^\wÀ-￿]+/g, '-')
      .replace(/^-+|-+$/g, '');

    if (!base) base = 'section';

    var id = base;
    var suffix = 2;

    while (document.getElementById(id)) {
      id = base + '-' + suffix;
      suffix += 1;
    }

    heading.setAttribute('id', id);

    return id;
  }

  function entry(level, id, text, number) {
    var item = document.createElement('li');
    item.className = 'pix-toc__item pix-toc__item--' + level;

    var link = document.createElement('a');
    link.className = 'pix-toc__link';
    link.setAttribute('href', '#' + encodeURIComponent(id));

    if (number) {
      var count = document.createElement('span');
      count.className = 'pix-toc__number';
      count.setAttribute('aria-hidden', 'true');
      count.textContent = number;
      link.appendChild(count);
    }

    var label = document.createElement('span');
    label.className = 'pix-toc__text';
    label.textContent = text;
    link.appendChild(label);

    item.appendChild(link);

    return item;
  }

  function build(root) {
    var list = root.querySelector('[data-toc-list]');

    if (!list) return 0;

    var levels = levelsFor(root);
    var headings = scopeFor(root.getAttribute('data-toc-scope')).querySelectorAll(levels.join(','));
    var numbered = root.hasAttribute('data-toc-numbers');
    var top = levels[0];
    var count = 0;

    list.innerHTML = '';

    Array.prototype.forEach.call(headings, function (heading) {
      // The rail's own label is not an entry in the rail, and neither is
      // anything in the header or the footer — those are on every page.
      if (root.contains(heading)) return;
      if (heading.closest && heading.closest('header, footer')) return;

      var text = (heading.textContent || '').replace(/\s+/g, ' ').trim();

      if (!text) return;

      // Only the outermost level is numbered. A nested heading is indented
      // instead, which is what the design does with the ones it shows.
      var number = '';

      if (numbered && heading.tagName.toLowerCase() === top) {
        count += 1;
        number = String(count);
      }

      list.appendChild(entry(heading.tagName.toLowerCase(), idFor(heading), text, number));
    });

    return list.children.length;
  }

  function spy(root, offset) {
    var links = root.querySelectorAll('.pix-toc__link');

    if (root.pixTocObserver) {
      root.pixTocObserver.disconnect();
      root.pixTocObserver = null;
    }

    if (!links.length) return;

    var byId = {};
    var targets = [];

    Array.prototype.forEach.call(links, function (link) {
      var href = link.getAttribute('href') || '';

      if (href.charAt(0) !== '#') return;

      var id = decodeURIComponent(href.slice(1));
      var heading = document.getElementById(id);

      if (!heading) return;

      // The one thing this widget writes onto another widget's element, and
      // only when an editor has asked for it: without it a jump lands the
      // heading under whatever is stuck to the top of the viewport.
      if (offset) heading.style.scrollMarginTop = offset + 'px';

      byId[id] = link;
      targets.push(heading);
    });

    if (!targets.length || !window.IntersectionObserver) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (change) {
        if (!change.isIntersecting) return;

        Array.prototype.forEach.call(links, function (link) {
          link.removeAttribute('aria-current');
        });

        var current = byId[change.target.id];

        if (current) current.setAttribute('aria-current', 'true');
      });
    }, { rootMargin: '-12% 0px -75% 0px' });

    targets.forEach(function (heading) {
      observer.observe(heading);
    });

    root.pixTocObserver = observer;
  }

  function init(root) {
    var offset = parseInt(root.getAttribute('data-toc-offset'), 10);

    if (isNaN(offset) || offset < 0) offset = 0;

    // Only the headings source has a list to build. A written one is already
    // in the markup, and rebuilding it would throw away links that work.
    if (root.hasAttribute('data-toc-levels')) {
      var built = build(root);
      var note = root.querySelector('[data-toc-empty]');

      if (built) root.hidden = false;
      if (note) note.hidden = Boolean(built);
    }

    spy(root, offset);
  }

  $(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/pixelomatic-table-of-contents.default',
      function ($scope) {
        var root = $scope[0].querySelector('[data-pix-toc]');

        if (root) init(root);
      }
    );
  });
})(jQuery);
