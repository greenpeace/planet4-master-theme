$(document).ready(function () {
  'use strict';

  $(function () {
    var cookie = readCookie('greenpeace');
    if (cookie == null) {
      $('.cookie-block').show();
      const height = $('.cookie-block').height();
      $('footer').css('margin-bottom', height + 'px');
    }
  });

  $('#hidecookie').click(function () {
    $('.cookie-block').slideUp('slow');
    $('footer').css('margin-bottom', '0');
    createCookie('greenpeace', '2', 365);
  });
});
