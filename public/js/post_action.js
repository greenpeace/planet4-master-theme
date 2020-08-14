// Force the Cover card to follow scroll

jQuery(function ($) {
  'use strict';

  const $sidebar = $('.post-content').find('> #action-card');
  const offset = $sidebar.offset();
  const topPadding = 100;

  function scroll_action_card_normal() {
    if ($(window).width() > 992) {
      let absPosition = $('.post-details > :last-child').offset().top - $sidebar.outerHeight() - topPadding;

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
    } else {
      $sidebar.css('margin-top', 0);
    }
  }

  function scroll_action_card_scroll() {
    const $boxoutScroll = $('.post-content').find('> #action-card-scroll').not('.hidden');
    const postStart = $('.post-content').position().top;
    const postHeight = $('.post-content').outerHeight();
    if ($boxoutScroll.length > 0) {
      if ($(window).scrollTop() > postStart && $(window).scrollTop() < (postHeight - postStart)) {
        $boxoutScroll.fadeIn();
        // If mobile/tablet the user can close the boxout by clicking on "not now"
        if ($(window).width() < 992) {
          $('.not-now').on('click', () => {
            $boxoutScroll.fadeOut(() => $boxoutScroll.addClass('hidden'));
          });
        }
      } else if ($(window).width() > 992 || $(window).scrollTop() > (postHeight - postStart)) {
        $boxoutScroll.fadeOut();
      }
    }
  }

  if ($sidebar.length > 0) {
    window.addEventListener('scroll', scroll_action_card_normal);
    window.addEventListener('resize', scroll_action_card_normal);
  }

  // This is called in Google Optimise for the A/B test
  window.animateBoxoutScroll = () => {
    window.addEventListener('scroll', scroll_action_card_scroll);
    window.addEventListener('resize', scroll_action_card_scroll);
  };
});
