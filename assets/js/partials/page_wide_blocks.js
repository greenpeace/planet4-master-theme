// Force wide blocks outside the container

$(document).ready(function() {
  'use strict'

  var $wideblocks = $('.block-wide')
    , $container = $('div.page-template')

  function force_wide_blocks() {
    var vw = $container.width()
    $wideblocks.each(function() {
      var width = $(this).innerWidth()
        , margin = ((vw - width) / 2)

      $(this).css('margin-left', margin + 'px')
    })
  }

  if ($wideblocks.length > 0 && $container.length > 0) {
    force_wide_blocks()
    $(window).on('resize', force_wide_blocks)
  } else {
    $('.block-wide').attr('style','margin: 0px !important;padding-left: 0px !important;padding-right: 0px !important')
    $('iframe').attr('style','left: 0')
  }
})
