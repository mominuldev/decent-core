/**
 * Carousel — the shared Swiper boot.
 *
 * One script for every slider widget. It knows nothing about any of them: it
 * finds `[data-pix-carousel]`, reads the Swiper config PHP put on it, and
 * hands it over. Has_Slider_Controls::slider_settings() writes that JSON in
 * Swiper's own spelling, so there is no translation layer here — a Swiper
 * option is exposed by adding a control and nothing else.
 *
 * What is left is the two things Swiper has no opinion about: the reduced
 * motion override, and the "Showing 1–3 of 6" line, whose two sentence forms
 * also come from PHP so they can be translated.
 *
 * It binds `frontend/element_ready/global` rather than one widget name,
 * because the point is that the next slider widget needs no script of its own.
 * That hook fires for every element, including the section a widget sits in,
 * so the config string doubles as a freshness check: an element already built
 * from the same options is left alone, and a changed one — which is what an
 * edit in the panel looks like — is torn down and rebuilt.
 *
 * If Swiper is missing the markup stays the scroll-snap row it was rendered
 * as: still swipeable, still readable, just without the controls, which is why
 * they ship hidden.
 */
(function ($) {
  'use strict';

  function init(carousel) {
    if (typeof window.Swiper !== 'function') return;

    var viewport = carousel.querySelector('.pix-carousel__viewport');
    if (!viewport) return;

    var signature = carousel.getAttribute('data-options') || '';

    // Already built from exactly these options: nothing to do.
    if (viewport.swiper && viewport.getAttribute('data-built') === signature) return;

    // Swiper does not replace itself — a second instance on the same element
    // fights the first over the transform.
    if (viewport.swiper) viewport.swiper.destroy(true, true);

    var config;

    try {
      config = JSON.parse(signature || '{}');
    } catch (error) {
      return;
    }

    // Autoplay is a motion preference before it is a setting: a visitor who
    // asked for less of it does not get a row that moves on its own.
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      delete config.autoplay;
    }

    var controls = carousel.querySelector('[data-controls]');
    var countEl = carousel.querySelector('[data-count]');
    var slides = viewport.querySelectorAll('.swiper-slide').length;

    // The class swaps the scroll-snap fallback for the Swiper-driven box. Set
    // before init so Swiper measures the geometry it will actually lay out in.
    carousel.classList.add('pix-carousel--ready');

    if (controls) controls.hidden = false;

    var swiper = new window.Swiper(viewport, config);

    viewport.setAttribute('data-built', signature);

    /* ----------------------------------------------------------------- count */

    if (!countEl) return;

    var long = countEl.getAttribute('data-template') || '';
    var short = countEl.getAttribute('data-template-short') || '';

    function fill(template, first, last, total) {
      return template
        .replace('%1$s', first)
        .replace('%2$s', last)
        .replace('%3$s', total);
    }

    function updateCount() {
      // realIndex rather than activeIndex: with loop on, Swiper pads the track
      // with clones and activeIndex counts them.
      var first = swiper.realIndex + 1;
      var perView = swiper.params.slidesPerView;

      // 'auto' has no number to count with, and a fractional view is one whole
      // slide plus a deliberate sliver of the next.
      perView = (typeof perView === 'number') ? Math.max(1, Math.floor(perView)) : 1;

      var last = Math.min(slides, first + perView - 1);

      // A single slide in view has no range to describe.
      countEl.textContent = fill(
        perView > 1 ? long : short,
        String(first),
        String(last),
        String(slides)
      );
    }

    swiper.on('slideChange', updateCount);
    swiper.on('breakpoint', updateCount);
    swiper.on('resize', updateCount);
    updateCount();
  }

  $(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) {
      var carousels = $scope[0].querySelectorAll('[data-pix-carousel]');
      Array.prototype.forEach.call(carousels, init);
    });
  });
})(jQuery);
