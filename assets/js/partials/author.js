$(document).ready(function() {
  'use strict';

  // Underline headline on thumbnail hover.
  $('.search-result-item-image').hover(function() {
    $('.search-result-item-headline', $(this).parent()).addClass('search-hover');
  }, function() {
    $('.search-result-item-headline', $(this).parent()).removeClass('search-hover');
  });
});
