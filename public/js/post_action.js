// Force the Cover card to follow scroll

jQuery(function ($) {
  'use strict';

  const $post = $('.post-content');
  const $boxout = $post.find('> #action-card').not('.action-card-bottom, .action-card-scroll');
  const offset = $boxout.offset();
  const topPadding = 100;

  function action_card_normal() {
    if ($(window).width() >= 992) {
      let absPosition = $('.post-details > :last-child').offset().top - $boxout.outerHeight() - topPadding;

      if ($(window).scrollTop() > offset.top && $(window).scrollTop() < absPosition) {
        $boxout.stop().animate({
          marginTop: $(window).scrollTop() - offset.top + topPadding
        });
      }
      if ($(window).scrollTop() < offset.top) {
        $boxout.stop().animate({
          marginTop: 0
        });
      }
    } else {
      $boxout.css('margin-top', 0);
    }
  }

  function action_card_scroll() {
    const $boxoutScrollNotHidden = $('.post-content').find('.action-card-scroll').not('.hidden');
    const postStart = $('.post-content').position().top;
    const postHeight = $('.post-content').outerHeight();
    const shareButtonsLeft = $('.share-buttons').offset().left;
    if ($boxoutScrollNotHidden.length > 0) {
      if ($(window).scrollTop() > postStart && $(window).scrollTop() < (postHeight - postStart)) {
        // We want to align the boxout with the share buttons
        if ($(window).width() >= 992 && shareButtonsLeft && $boxoutScrollNotHidden.offset().left !== shareButtonsLeft) {
          $boxoutScrollNotHidden.css('left', shareButtonsLeft + 'px');
        }
        $boxoutScrollNotHidden.fadeIn();
        // If mobile/tablet the user can close the boxout by clicking on the cross
        if ($(window).width() < 992) {
          $('.not-now').on('click', () => {
            $boxoutScrollNotHidden.fadeOut(() => $boxoutScrollNotHidden.addClass('hidden'));
          });
        }
      } else if ($(window).width() >= 992 && $boxoutScrollNotHidden.css('display') !== 'none') {
        $boxoutScrollNotHidden.fadeOut();
      }
    }
  }

  if ($post.length > 0 && $boxout.length > 0) {
    window.addEventListener('scroll', action_card_normal);
    window.addEventListener('resize', action_card_normal);
  }

  window.animateBoxoutScroll = () => {
    window.removeEventListener('scroll', action_card_normal);
    window.removeEventListener('resize', action_card_normal);
    window.addEventListener('scroll', action_card_scroll);
    window.addEventListener('resize', action_card_scroll);
  };
});
