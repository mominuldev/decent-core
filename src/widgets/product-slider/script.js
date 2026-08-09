/**
 * Product slider.
 *
 * The track already scrolls without this file: it is a scroll-snap container,
 * so touch, trackpad and keyboard all work before any script runs. All this
 * adds is the previous/next pair, which is why the buttons ship hidden and are
 * only revealed once the script is here to drive them.
 */
(function () {
  'use strict';

  var sliders = document.querySelectorAll('[data-slider]');
  if (!sliders.length) return;

  Array.prototype.forEach.call(sliders, function (slider) {
    var track = slider.querySelector('[data-slider] > ul, .decent-slider__track');
    var nav = slider.querySelector('[data-slider-nav]');
    var prev = slider.querySelector('[data-slider-prev]');
    var next = slider.querySelector('[data-slider-next]');

    if (!track || !nav || !prev || !next) return;

    // Nothing to page through: leave the buttons hidden rather than showing
    // two controls that cannot do anything.
    if (track.scrollWidth <= track.clientWidth + 1) return;

    nav.hidden = false;

    function step() {
      var first = track.firstElementChild;
      return first ? first.getBoundingClientRect().width + 20 : track.clientWidth * 0.8;
    }

    function scrollBy(amount) {
      var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      track.scrollBy({ left: amount, behavior: reduced ? 'auto' : 'smooth' });
    }

    prev.addEventListener('click', function () { scrollBy(-step()); });
    next.addEventListener('click', function () { scrollBy(step()); });

    function sync() {
      var atStart = track.scrollLeft <= 1;
      var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
      prev.disabled = atStart;
      next.disabled = atEnd;
    }

    track.addEventListener('scroll', sync);
    window.addEventListener('resize', sync);
    sync();
  });
})();
