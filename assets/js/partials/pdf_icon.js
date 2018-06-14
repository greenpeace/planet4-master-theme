// Add pdf icon to pdf links

$(document).ready(function() {
  'use strict';

  $('a[href$=".pdf"]').each(function() {
    let link = $(this);

    if (!(link.parent('h1, h2, h3, h4, h5, h6').length || link.has('img').length)) {
      link.addClass('pdf-link');
    }
  });
});
