/**
 * Navigation Menu — the burger, the panel and the submenus.
 *
 * The drawer exists only where this script runs, which is why the collapsed
 * state is a class this adds (`pix-nav--collapsed`) rather than a media query
 * the stylesheet applies on its own: a burger CSS alone can draw is a button
 * that opens nothing. Without this file the widget stays a plain list, stacked
 * below its collapse width by the rules at the foot of style.scss.
 *
 * Binds from `frontend/element_ready`, which is what makes it work in the
 * editor: a widget dropped on the canvas arrives as markup from an AJAX
 * render, long after any load event, and the hook fires again on every control
 * change. Everything here is therefore idempotent — element handlers are
 * assigned with `onclick` so a second run replaces rather than stacks, the
 * submenu toggle is only built where one is not already, and the three
 * listeners that have to live on the document or the window are registered
 * once for the page and find their target from the DOM.
 */
(function ($) {
  'use strict';

  var OPEN = 'pix-nav--open';
  var COLLAPSED = 'pix-nav--collapsed';
  var ITEM_OPEN = 'pix-nav__item--open';
  var LOCK = 'pix-nav-locked';

  // The panel currently open, if any. Only one can be: opening a second
  // closes the first, so Escape and the outside click never have to guess.
  var openRoot = null;
  var documentBound = false;
  var spyQueued = false;

  function roots() {
    return document.querySelectorAll('[data-pix-nav]');
  }

  function closePanel(root, focusTrigger) {
    var trigger = root.querySelector('[data-pix-nav-open]');

    root.classList.remove(OPEN);

    if (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
      if (focusTrigger) trigger.focus();
    }

    if (openRoot === root) {
      openRoot = null;
      document.body.classList.remove(LOCK);
    }
  }

  function openPanel(root) {
    var trigger = root.querySelector('[data-pix-nav-open]');
    var close = root.querySelector('[data-pix-nav-close]:not(.pix-nav__scrim)');

    if (openRoot && openRoot !== root) closePanel(openRoot, false);

    root.classList.add(OPEN);
    document.body.classList.add(LOCK);
    openRoot = root;

    if (trigger) trigger.setAttribute('aria-expanded', 'true');

    // The close button is the first thing in the panel, so moving focus there
    // puts the keyboard inside the drawer without a trap to maintain.
    if (close) close.focus();
  }

  function closeItems(root, except) {
    var items = root.querySelectorAll('.' + ITEM_OPEN);

    Array.prototype.forEach.call(items, function (item) {
      if (item === except) return;
      item.classList.remove(ITEM_OPEN);
      var toggle = item.querySelector('.pix-nav__sub-toggle');
      if (toggle) toggle.setAttribute('aria-expanded', 'false');
    });
  }

  /**
   * Gives every parent item a real button to open its submenu with.
   *
   * The walker prints a chevron beside the link, but a decorative SVG is not
   * something a keyboard or a screen reader can operate — and inside the
   * drawer, opening on hover would mean the first tap opens the submenu and
   * the second navigates away from it. The glyph is cloned rather than moved
   * so the stylesheet can show whichever of the two the current mode wants.
   */
  function buildToggles(root) {
    var parents = root.querySelectorAll('.pix-nav__menu .has-sub');

    Array.prototype.forEach.call(parents, function (item) {
      var toggle = item.querySelector(':scope > .pix-nav__sub-toggle');
      var sub = item.querySelector(':scope > .main-nav__sub');
      var chevron = item.querySelector(':scope > svg');
      var link = item.querySelector(':scope > a');

      if (!sub) return;

      if (!toggle) {
        toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'pix-nav__sub-toggle';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute(
          'aria-label',
          (link ? link.textContent : '') + ' — submenu'
        );

        if (chevron) toggle.appendChild(chevron.cloneNode(true));

        // Before the submenu, not after it: inside the drawer the item wraps
        // so the submenu can take a line of its own, and a toggle appended
        // last would land underneath the list it opens.
        item.insertBefore(toggle, sub);
      }

      toggle.onclick = function (event) {
        var isOpen = item.classList.contains(ITEM_OPEN);

        event.preventDefault();
        event.stopPropagation();

        closeItems(root, isOpen ? null : item);
        item.classList.toggle(ITEM_OPEN, !isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      };
    });
  }

  /**
   * Applies the widget's collapse width.
   *
   * Elementor's breakpoints and the theme's are max-width, and so is this —
   * `data-breakpoint` is one of the five the kit carries, or `always` for a
   * header that wants the burger at every size.
   */
  function watchBreakpoint(root) {
    var value = root.getAttribute('data-breakpoint') || '900';

    if (value === 'always') {
      root.classList.add(COLLAPSED);
      return;
    }

    var query = window.matchMedia('(max-width: ' + parseInt(value, 10) + 'px)');

    function apply() {
      root.classList.toggle(COLLAPSED, query.matches);

      // Leaving the drawer behind at desktop would leave a panel open that
      // nothing on screen can close.
      if (!query.matches) {
        closePanel(root, false);
        closeItems(root, null);
      }
    }

    query.onchange = apply;
    apply();
  }

  /**
   * Lets the panel animate, one frame after it has been parked.
   *
   * `pix-nav--collapsed` turns the panel from `display: contents` into a
   * positioned box that is translated off the side — a transform change the
   * browser animates, so the drawer slid off screen on every page load. Two
   * frames is what it takes for the parked position to be the one being
   * transitioned from.
   */
  function allowAnimation(root) {
    window.requestAnimationFrame(function () {
      window.requestAnimationFrame(function () {
        root.classList.add('pix-nav--animated');
      });
    });
  }

  function target(link) {
    var hash = link.hash;

    if (!hash || hash === '#') return null;
    if (link.pathname !== window.location.pathname) return null;
    if (link.host !== window.location.host) return null;

    try {
      return document.querySelector(hash);
    } catch (error) {
      return null;
    }
  }

  /**
   * Marks the section being read, for every local-scroll menu on the page.
   *
   * `location` rather than `page`: the page is not the thing that changed.
   */
  function spy() {
    spyQueued = false;

    Array.prototype.forEach.call(roots(), function (root) {
      if (root.getAttribute('data-local-scroll') !== 'yes') return;

      var links = root.querySelectorAll('.pix-nav__menu a[href*="#"]');
      var current = null;

      Array.prototype.forEach.call(links, function (link) {
        var section = target(link);

        link.removeAttribute('aria-current');

        if (section && section.getBoundingClientRect().top <= 120) {
          current = link;
        }
      });

      if (current) current.setAttribute('aria-current', 'location');
    });
  }

  function bindLinks(root) {
    var links = root.querySelectorAll('.pix-nav__menu a');
    var scrolls = root.getAttribute('data-local-scroll') === 'yes';

    Array.prototype.forEach.call(links, function (link) {
      link.onclick = function (event) {
        var section = scrolls ? target(link) : null;

        // A link inside the drawer has done its job the moment it is
        // followed; leaving the panel over the page it moved to is the bug
        // every off-canvas menu ships with once.
        if (root.classList.contains(OPEN)) closePanel(root, false);

        if (!section) return;

        event.preventDefault();
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });

        if (window.history && window.history.replaceState) {
          window.history.replaceState(null, '', link.hash);
        }
      };
    });
  }

  function bindDocument() {
    if (documentBound) return;

    documentBound = true;

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape' && event.key !== 'Esc') return;

      if (openRoot) {
        closePanel(openRoot, true);
        return;
      }

      Array.prototype.forEach.call(roots(), function (root) {
        closeItems(root, null);
      });
    });

    // A dropdown held open by a click closes when the next click lands
    // anywhere else — including on another widget entirely.
    document.addEventListener('click', function (event) {
      Array.prototype.forEach.call(roots(), function (root) {
        if (!root.contains(event.target)) closeItems(root, null);
      });
    });

    window.addEventListener(
      'scroll',
      function () {
        if (spyQueued) return;
        spyQueued = true;
        window.requestAnimationFrame(spy);
      },
      { passive: true }
    );
  }

  function init(root) {
    var trigger = root.querySelector('[data-pix-nav-open]');
    var closers = root.querySelectorAll('[data-pix-nav-close]');

    buildToggles(root);
    bindLinks(root);
    watchBreakpoint(root);
    allowAnimation(root);
    bindDocument();

    if (trigger) {
      trigger.onclick = function () {
        if (root.classList.contains(OPEN)) {
          closePanel(root, true);
        } else {
          openPanel(root);
        }
      };
    }

    Array.prototype.forEach.call(closers, function (closer) {
      closer.onclick = function () {
        closePanel(root, true);
      };
    });

    spy();
  }

  $(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/pixelomatic-navigation-menu.default',
      function ($scope) {
        var root = $scope[0].querySelector('[data-pix-nav]');

        if (root) init(root);
      }
    );
  });
})(jQuery);
