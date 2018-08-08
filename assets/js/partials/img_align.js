$(document).ready(function() {
  'use strict';

  $('div.wp-caption[class*="align"]').each( function() {
    const imgwidth = $(this).find('img').attr('width');
    $(this).css('cssText', 'width: ' + imgwidth +'px !important;');
  });
});
