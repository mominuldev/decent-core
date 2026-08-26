/**
 * Accordion — smooth open/close.
 *
 * Every panel renders open in the markup so content stays readable with
 * JavaScript off; this is the first thing that runs, and it closes whichever
 * panels the widget did not mark active. The CSS grid-rows transition that
 * animates a panel is gated behind the root's `is-ready` class, added only
 * once that initial state is applied — so the closing that happens here on
 * load never animates, only a later click does.
 *
 * Binds from `frontend/element_ready`, which is what makes it work in the
 * editor: a widget dropped on the canvas arrives as markup from an AJAX
 * render, long after any load event, and the hook fires again on every
 * control change. Triggers are wired with `onclick` rather than
 * `addEventListener`, so a second run replaces the handler instead of
 * stacking a second listener onto the same button.
 */
(function ($) {
  'use strict';

  function closePanel(panel) {
    if (panel) panel.classList.add('is-closed');
  }

  function openPanel(panel) {
    if (panel) panel.classList.remove('is-closed');
  }

  function panelFor(trigger) {
    return document.getElementById(trigger.getAttribute('aria-controls'));
  }

  function init(root) {
    var triggers = root.querySelectorAll('[data-accordion-trigger]');
    var multiple = root.hasAttribute('data-accordion-multiple');

    triggers.forEach(function (trigger) {
      var open = trigger.getAttribute('aria-expanded') === 'true';

      if (open) {
        openPanel(panelFor(trigger));
      } else {
        closePanel(panelFor(trigger));
      }

      trigger.onclick = function () {
        var isOpen = trigger.getAttribute('aria-expanded') === 'true';

        if (!multiple) {
          triggers.forEach(function (other) {
            if (other === trigger) return;
            other.setAttribute('aria-expanded', 'false');
            closePanel(panelFor(other));
          });
        }

        trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

        if (isOpen) {
          closePanel(panelFor(trigger));
        } else {
          openPanel(panelFor(trigger));
        }
      };
    });

    root.classList.add('is-ready');
  }

  $(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/pixelomatic-accordion.default',
      function ($scope) {
        var root = $scope[0].querySelector('[data-accordion]');

        if (root) init(root);
      }
    );
  });
})(jQuery);
