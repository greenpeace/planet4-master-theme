/* global slickify */

$(document).ready(function() {
  'use strict';

  // Handle slick functionality for each Content Four Column blocks.
  $('.four-column-content').each( function() {
    var pubSlidesNum = $('.publications-slider .post-column', $(this)).length;

    if (pubSlidesNum > 3 && $(window).width() < 992) {
      slickify('.publications-slider');
    }
  });
});
