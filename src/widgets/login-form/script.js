/**
 * Login form — the password reveal toggle.
 *
 * The only thing on the card that JavaScript is needed for. Everything else is
 * a real form posting to a real handler, so this file arriving late, failing to
 * arrive, or being switched off costs the visitor a convenience and nothing
 * else.
 *
 * That is why the button is printed with `hidden` and revealed here rather than
 * printed visible and wired up here: a toggle that cannot toggle is worse than
 * no toggle, so the only thing that shows it is the code that can drive it.
 *
 * It binds from `frontend/element_ready`, which is what makes it work in the
 * editor: a widget dropped on the canvas arrives as markup from an AJAX render,
 * long after any load event, and the same hook fires again on every control
 * change. Handlers are assigned to `onclick` rather than added as listeners, so
 * a second run replaces the first instead of stacking a second toggle onto the
 * same button.
 */
(function ($) {
  'use strict';

  function init(card) {
    var toggle = card.querySelector('[data-reveal]');
    var input = card.querySelector('[data-password]');

    if (!toggle || !input) return;

    var show = toggle.getAttribute('data-show') || toggle.textContent;
    var hide = toggle.getAttribute('data-hide') || show;

    function paint(revealed) {
      input.type = revealed ? 'text' : 'password';
      toggle.textContent = revealed ? hide : show;
      toggle.setAttribute('aria-pressed', revealed ? 'true' : 'false');
    }

    // Re-running the hook must not leave the field revealed from a previous
    // pass, so the resting state is asserted rather than assumed.
    paint(false);
    toggle.hidden = false;

    toggle.onclick = function () {
      var revealed = input.type === 'password';

      paint(revealed);

      // The caret goes to the end rather than back to the start, which is
      // where a type swap would otherwise leave it mid-word.
      if (typeof input.setSelectionRange === 'function') {
        try {
          input.focus();
          input.setSelectionRange(input.value.length, input.value.length);
        } catch (e) {
          input.focus();
        }
      }
    };
  }

  $(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/decent-login-form.default',
      function ($scope) {
        var card = $scope[0].querySelector('.pix-login');

        if (card) init(card);
      }
    );
  });
})(jQuery);
