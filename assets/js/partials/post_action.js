// Force the Cover card to follow scroll

$(document).ready(function() {
  'use strict';

  var $sidebar = $('.post-content').find('> #action-card');
  var offset = $sidebar.offset();
  var topPadding = 100;

  function scroll_action_card() {
    let absPosition = $('.post-details > p:last-child').offset().top - $sidebar.outerHeight() - topPadding;

    if ($(window).scrollTop() > offset.top && $(window).scrollTop() < absPosition) {
      $sidebar.stop().animate({
        marginTop: $(window).scrollTop() - offset.top + topPadding
      });
    }
    if ($(window).scrollTop() < offset.top) {
      $sidebar.stop().animate({
        marginTop: 0
      });
    }
  }

  if ($sidebar.length > 0 && $(window).width() > 992) {
    window.addEventListener('scroll', scroll_action_card);
  }
});
