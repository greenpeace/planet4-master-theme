/* global LazyLoad */
export const setupLazyLoad = function() {
  // These images will be loaded as soon
  // as they enter the viewport
  window.lazyLoad = new LazyLoad({
    elements_selector: '.lazyload',
  });

  // These will be loaded as soon as the DOM is ready,
  // even if they are outside the viewport, required
  // for carousels/sliders/etc.
  jQuery(function($) {
    $('img.preload').each(function() {
      window.lazyLoad.load(this);
    });
  });
};
