/* exported slickify */

/*
  This file is always concatenated first.
  It should include functions needed in more than one place.
*/

$ = jQuery //eslint-disable-line no-global-assign

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
  })
}
