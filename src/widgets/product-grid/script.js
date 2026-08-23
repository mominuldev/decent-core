/**
 * Product grid — category chips and sort, without a page load.
 *
 * The section already works before this file arrives: every chip is a link to
 * that category's archive and the sort control is a real GET form. All this
 * does is intercept both and swap in cards the server rendered, from the same
 * grid_response() that painted the page.
 *
 * The rules it follows, which are the theme's:
 *   - Every interaction it intercepts has a working non-JS path underneath.
 *   - Every failure falls back to following that path, not to an error state.
 *   - The markup it injects comes from the server, rendered by the same
 *     template part the page used. It never templates a card itself.
 *
 * The one place it departs from the catalogue script is history: this is one
 * section of a page, not the page. Pushing the category archive's URL for a
 * chip two thirds of the way down a landing page would make Back leave a
 * section the visitor never navigated to, so nothing is pushed and the chips
 * reset on reload.
 *
 * It binds from `frontend/element_ready` rather than on load, which is what
 * makes it work in the editor: a widget dropped on the canvas arrives as
 * markup from an AJAX render, long after any load event, and the same hook
 * fires again on every control change.
 */
(function ($) {
  'use strict';

  function init(section) {
    if (!window.fetch) return;

    var endpoint = section.getAttribute('data-endpoint');
    var post = section.getAttribute('data-post');
    var widget = section.getAttribute('data-widget');
    var results = section.querySelector('[data-results]');

    if (!endpoint || !post || !widget || !results) return;

    var chips = section.querySelectorAll('[data-chips] .pix-filter__chip');
    var sortForm = section.querySelector('[data-sort-form]');
    var sortSelect = section.querySelector('[data-sort]');
    var moreLink = section.querySelector('[data-more]');
    var moreLabel = section.querySelector('[data-more-label]');
    var sortValue = section.querySelector('[data-sort-value]');

    if (!chips.length && !sortSelect) return;

    var controller = null;
    var state = {
      category: 0,
      orderby: sortSelect ? sortSelect.value : ''
    };

    /* ---------------------------------------------------------------- fetch */

    function query() {
      var parts = [
        'post=' + encodeURIComponent(post),
        'widget=' + encodeURIComponent(widget),
        'category=' + encodeURIComponent(state.category)
      ];

      if (state.orderby) parts.push('orderby=' + encodeURIComponent(state.orderby));

      return parts.join('&');
    }

    function setBusy(busy) {
      // A class would be a design decision; aria-busy is the state itself, and
      // the stylesheet reads it. One attribute, both jobs.
      results.setAttribute('aria-busy', busy ? 'true' : 'false');
    }

    function apply(data) {
      // The response is the complete grid, byte-identical to what the server
      // renders — which is the point, and what keeps the two paths from
      // drifting. Its children are swapped rather than the container itself,
      // because replacing a live region tends to stop it announcing at all.
      results.innerHTML = data.html || '';

      if (moreLink && moreLabel) {
        if (data.more) {
          moreLabel.textContent = data.more.label;
          moreLink.href = data.more.url;
          moreLink.hidden = false;
        } else {
          moreLink.hidden = true;
        }
      }
    }

    function load(fallback) {
      if (controller) controller.abort();
      controller = ('AbortController' in window) ? new AbortController() : null;

      setBusy(true);

      window.fetch(endpoint + (endpoint.indexOf('?') === -1 ? '?' : '&') + query(), {
        credentials: 'same-origin',
        signal: controller ? controller.signal : undefined
      })
        .then(function (response) {
          if (!response.ok) throw new Error('HTTP ' + response.status);
          return response.json();
        })
        .then(function (data) {
          setBusy(false);
          apply(data);
        })
        .catch(function (error) {
          // An aborted request is the next chip taking over, not a failure —
          // leave the busy state to the request that replaced this one.
          if (error && error.name === 'AbortError') return;

          setBusy(false);
          // Anything else: the link underneath still works. Use it.
          if (fallback) fallback();
        });
    }

    /* ---------------------------------------------------------------- chips */

    Array.prototype.forEach.call(chips, function (chip) {
      chip.addEventListener('click', function (event) {
        // Leave modified clicks alone: a visitor opening a category in a new
        // tab means the archive, not a filter.
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;

        event.preventDefault();

        state.category = parseInt(chip.getAttribute('data-category'), 10) || 0;

        Array.prototype.forEach.call(chips, function (other) {
          other.classList.toggle('pix-filter__chip--active', other === chip);

          if (other === chip) {
            other.setAttribute('aria-current', 'true');
          } else {
            other.removeAttribute('aria-current');
          }
        });

        load(function () { window.location = chip.href; });
      });
    });

    /* ----------------------------------------------------------------- sort */

    if (sortSelect) {
      // The submit button lives inside <noscript>, so it is not in the DOM
      // here and there is nothing to hide. Changing the select is the whole
      // interaction.
      sortSelect.addEventListener('change', function () {
        state.orderby = sortSelect.value;

        // The select is transparent; the span beside it is what a visitor
        // actually reads, so it has to move first, before the request.
        if (sortValue) {
          var chosen = sortSelect.options[sortSelect.selectedIndex];
          if (chosen) sortValue.textContent = chosen.text.trim();
        }

        load(function () {
          if (sortForm) sortForm.submit();
        });
      });
    }
  }

  $(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/decent-product-grid.default',
      function ($scope) {
        var section = $scope[0].querySelector('[data-product-grid]');
        if (section) init(section);
      }
    );
  });
})(jQuery);
