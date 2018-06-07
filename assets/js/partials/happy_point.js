/* global readCookie */

// Load happy point iframe only when visible
$(document).ready(function() {
  'use strict';

  function load_happy_point() {
    if ($('#happy-point > iframe').length > 0) {
      window.removeEventListener('load', load_happy_point);
      window.removeEventListener('resize', load_happy_point);
      window.removeEventListener('scroll', load_happy_point);
      return;
    }

    const happy_pos = $('#happy-point')[0].getBoundingClientRect();

    if (happy_pos.top < window.innerHeight) {
      $('#happy-point').append($('<iframe></iframe>')
        .attr({
          src: decodeURIComponent(url),
          cellSpacing: '0',
          allowtransparency: 'true',
          frameborder: '0',
          scrolling: 'no',
          width: '100%'
        }));
    }
  }

  const url = $('#happy-point').data('src');

  if (url) {
    window.addEventListener('load', load_happy_point);
    window.addEventListener('resize', load_happy_point);
    window.addEventListener('scroll', load_happy_point);
  }
});
