// Force wide blocks outside the container
$(document).ready(function() {
  'use strict';

  const $wideblocks = $('.block-wide');
  const $container = $('div.page-template, div.container').eq(0);

  function force_wide_blocks() {
    const vw = $container.width();
    $wideblocks.each(function() {
      const width = $(this).innerWidth();
      const margin = ((vw - width) / 2);

      if ($('html').attr('dir') === 'rtl') {
        $(this).css('margin-left', 'auto');
        $(this).css('margin-right', margin + 'px');
      } else {
        $(this).css('margin-left', margin + 'px');
      }
    });
  }

  if ($wideblocks.length > 0 && $container.length > 0) {
    force_wide_blocks();
    $(window).on('resize', force_wide_blocks);
  } else {
    $('.block-wide').attr('style','margin: 0px !important;padding-left: 0px !important;padding-right: 0px !important');
    $('iframe').attr('style','left: 0');
  }
});
