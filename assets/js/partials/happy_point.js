// Wait until user scrolls to load happy point iframe

$(document).ready(function() {
  'use strict';

  var url = $('#happy-point').data('src');

  if (url) {
    $(window).scroll(function () {
      if ($(this).scrollTop() >= 500) {
        $('#happy-point').append($('<iframe></iframe>')
          .attr({src: url, cellSpacing: '0', allowtransparency: 'true', frameborder: '0', scrolling: 'no', width: '100%'}));
        $(window).off('scroll');
      }
    });
  }
});
