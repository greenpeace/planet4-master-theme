$(document).ready(function() {
  'use strict';

  function createCookie(name, value) {
    document.cookie = encodeURI(name) + '=' + encodeURI(value) + ';domain=.' + document.domain + ';path=/;'
  }

  function readCookie(name) {
    var nameEQ = name + '='
      , ca = document.cookie.split(';')
    
    for(var i=0;i < ca.length;i++) {
      var c = ca[i]
      while (c.charAt(0)==' ') {
        c = c.substring(1,c.length)
      }
      if (c.indexOf(nameEQ) == 0) {
        return c.substring(nameEQ.length,c.length)
      }
    }
    return null
  }

  $(function() {
    var cookie = readCookie('greenpeace')
    if (cookie == null) {
      $('.cookie-block').show()
      const height = $('.cookie-block').height()
      $('footer').css('margin-bottom', height + 'px')
    }
  })

  $('#hidecookie').click(function () {
    $('.cookie-block').slideUp('slow')
    $('footer').css('margin-bottom', '0')
    createCookie('greenpeace', 'policy-accepted')
  })
})
