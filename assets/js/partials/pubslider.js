/* global slickify */

$(document).ready(function() {
  'use strict';

  // Handle slick functionality for each Content Four Column blocks.
  $('.four-column-content').each( function() {
    var pubSlidesNum = $('.publications-slider .post-column', $(this)).length;

    if (pubSlidesNum > 3 && $(window).width() < 768) {
      slickify('.publications-slider');
    }
    if (pubSlidesNum < 4 && $(window).width() > 768) {
      $('.post-column').removeClass('col-lg-3').removeClass('col-md-4').addClass('col-md');
    }
  });
});
