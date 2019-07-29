$(document).ready(function () {
  'use strict';

  // Parse submenu object passed to a variable from server-side.
  if ('undefined' === submenu || ! Array.isArray(submenu)) {
    submenu = [];
  }

  for (var i = 0; i < submenu.length; i++) {
    var menu = submenu[i];

    if ('undefined' === menu.id || 'undefined' === menu.type || 'undefined' === menu.link) {
      continue;
    }
    var type = menu.type;

    // Iterate over headings and create an anchor tag for this heading.
    if (menu.link) {

      var $headings = $('body ' + type);

      for (var j = 0; j < $headings.length; j++) {
        var $heading = $($headings[j]);
        if ($heading.text().replace(/\u2010|\u2011|\u2013/, '') === menu.text.replace('-', '')) {
          $heading.prepend('<a id="' + menu.id + '" data-hash-target="' + menu.hash + '"></a>');
        }
      }
    }

    addChildrenLinks(menu);
  }

  // Add click event for submenu links.
  $('.submenu-link').click(function (event) {
    event.preventDefault();
    var link = $.attr(this, 'href');
    var h = $(this).data('hash');
    var $target = $('*[data-hash-target="'+h+'"]');
    if ($target) {
      $('html, body').animate({
        scrollTop: $target.offset().top - 100
      }, 2000, function () {
        var position = $(window).scrollTop();
        window.location.hash = link;
        $(window).scrollTop(position);
      });
    }

    return false;
  });

  /**
   * Append html links for a submenu entry children.
   *
   * @param menu Submenu entry
   */
  function addChildrenLinks(menu) {
    if ('undefined' === menu.children || !Array.isArray(menu.children)) {
      return;
    }

    for (var k = 0; k < menu.children.length; k++) {
      var child = menu.children[k];
      var child_type = child.type;
      var $headings = $('body ' + child_type);

      addChildrenLinks(child);

      for (var l = 0; l < $headings.length; l++) {

        var $heading = $($headings[l]);
        if ($heading.text().replace(/\u2010|\u2011|\u2013/, '') === child.text.replace('-', '')) {
          $heading.prepend('<a id="' + child.id + '" data-hash-target="' + child.hash + '"></a>');
          break;
        }
      }
    }
  }
});
