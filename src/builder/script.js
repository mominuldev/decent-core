/**
 * Sticky and overlay behaviour for builder headers.
 *
 * CSS does the sticking: a sticky header is `position: sticky` and an overlaid
 * one is `position: absolute`, both without a line of script. What CSS cannot
 * observe is the two states the design depends on — whether the page has
 * scrolled past the header, and which way it is going — so that is all this
 * adds: `--stuck` and `--hidden`.
 *
 * The renderer only prints data-decent-header on a header that has one of
 * those behaviours switched on, so on every other page this file finds nothing
 * and returns.
 */
(function () {
	'use strict';

	/* Matches the max-width the stylesheet stops sticking at. */
	var MOBILE = 768;
	var STUCK = 'site-header--stuck';
	var HIDDEN = 'site-header--hidden';

	function init() {
		var header = document.querySelector('[data-decent-header]');

		if (!header || 'true' === header.getAttribute('data-decent-bound')) {
			return;
		}

		var config;

		try {
			config = JSON.parse(header.getAttribute('data-decent-header'));
		} catch (error) {
			return;
		}

		if (!config || (!config.sticky && !config.overlay)) {
			return;
		}

		header.setAttribute('data-decent-bound', 'true');

		var threshold = 0;
		var lastY = window.pageYOffset;
		var ticking = false;

		/**
		 * An overlaid header covers the top of the page, so it becomes stuck
		 * once its own height has scrolled by. A header in the flow is stuck
		 * as soon as it reaches its offset.
		 */
		function measure() {
			threshold = config.overlay ? header.offsetHeight : config.offset || 0;
		}

		function disabled() {
			return !config.mobile && window.innerWidth <= MOBILE;
		}

		function update() {
			ticking = false;

			var y = window.pageYOffset;

			if (disabled()) {
				header.classList.remove(STUCK);
				header.classList.remove(HIDDEN);
				lastY = y;
				return;
			}

			var stuck = y > threshold;

			header.classList.toggle(STUCK, stuck);

			if (!stuck) {
				// Never leave the header hidden at the top of the page, which
				// is where a scroll-to-top lands the visitor.
				header.classList.remove(HIDDEN);
			} else if (config.hide) {
				// The four-pixel deadband keeps momentum scrolling on iOS from
				// flapping the header on every frame.
				if (y > lastY + 4) {
					header.classList.add(HIDDEN);
				} else if (y < lastY - 4) {
					header.classList.remove(HIDDEN);
				}
			}

			lastY = y;
		}

		function onScroll() {
			if (ticking) {
				return;
			}

			ticking = true;
			window.requestAnimationFrame(update);
		}

		measure();
		update();

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener(
			'resize',
			function () {
				measure();
				onScroll();
			},
			{ passive: true }
		);
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
