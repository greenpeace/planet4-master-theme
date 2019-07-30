// Scroll to comments

jQuery(function($) {
  'use strict';

  $('#comments-link').click(function() {
    $('html, body').animate({
      scrollTop: $('#comments').offset().top - 100
    }, 2000);
  });
});
