jQuery(function ($) {
  'use strict';

  // Check header heights to align them all vertically to the max one
  function align_column_headers() {
    // on small screens columns are not next to each other
    if ($(window).width() <= 768) {
      $('.columns-block .column-wrap h3').css('min-height', 'auto');
      return;
    }

    $.each($('.columns-block'), function () {
      let columnHeadings = $(this).find('.column-wrap h3');

      let highestHeadingHeight = 0;
      columnHeadings.each((index, heading) => {
        highestHeadingHeight = Math.max(highestHeadingHeight, $(heading).height());
      });

      columnHeadings.each((index, heading) => {
        $(heading).css('min-height', highestHeadingHeight);
      });
    });
  }

  window.addEventListener('resize', align_column_headers);
  window.addEventListener('load', align_column_headers);
});
