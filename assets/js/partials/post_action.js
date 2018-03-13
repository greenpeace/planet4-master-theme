// Force the Cover card to follow scroll

$(document).ready(function() {
  'use strict';

  var $sidebar = $('.post-content').find('> #action-card');
  var $window = $(window);
  var offset = $sidebar.offset();
  var topPadding = 100;

  if ($sidebar.length > 0 && $window.width() > 992) {
    var absPosition = $('.post-details > p:last-child').offset().top - $sidebar.outerHeight() - topPadding;
    $('h1.page-header-title').addClass('limit-width');

    $window.scroll(function () {
      if ($window.scrollTop() > offset.top &&
      $window.scrollTop() < absPosition) {
        $sidebar.stop().animate({
          marginTop: $window.scrollTop() - offset.top + topPadding
        });
      }
      if ($window.scrollTop() < offset.top) {
        $sidebar.stop().animate({
          marginTop: 0
        });
      }
    });
  } else {
    $('h1.page-header-title').removeClass('limit-width');
  }
});
