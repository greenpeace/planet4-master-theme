$(document).ready(function() {
  'use strict';

  // Handle slick functionality for each Content Four Column blocks.
  $('.four-column-content').each( function() {

    var pubSlidesNum = $('.publications-slider .post-column', $(this)).length;

    if (pubSlidesNum > 3 && $(window).width() < 992) {
      $('.publications-slider', $(this)).slick({
        infinite:       false,
        mobileFirst:    true,
        slidesToShow:   2.2,
        slidesToScroll: 1,
        arrows:         false,
        dots:           false,
        responsive: [
          {
            breakpoint: 992,
            settings: { slidesToShow: 4 }
          },
          {
            breakpoint: 768,
            settings: { slidesToShow: 3 }
          },
          {
            breakpoint: 576,
            settings: { slidesToShow: 2 }
          }
        ]
      });
    }
  });
});
