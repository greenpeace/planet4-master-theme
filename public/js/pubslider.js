jQuery(function ($) {
  'use strict';

  // convert an element to slider using slick js
  function slickify(element) {
    $(element).slick({
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

  // Handle slick functionality for each Content Four Column blocks.
  $('.four-column-content').each( function() {
    const pubSlidesNum = $('.publications-slider .post-column', $(this)).length;

    if (pubSlidesNum > 3 && $(window).width() < 768) {
      slickify('.publications-slider');
    }
    if (pubSlidesNum < 4 && $(window).width() > 768) {
      $('.post-column', $(this)).removeClass('col-lg-3').removeClass('col-md-4').addClass('col-md');
    }
  });
});
