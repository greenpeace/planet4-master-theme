$(document).ready(function() {
  'use strict';

  var pubSlides = $('.publications-slider .col-md-4').length;

  if (pubSlides > 3 && $(window).width() < 992) {
    $('.publications-slider').slick({
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
