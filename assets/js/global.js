$(document).ready(function() {
  'use strict';

  $('.page-template img, .post-content img').each( function() {
    $(this).attr('title', $(this).attr('alt') );
  });
});
