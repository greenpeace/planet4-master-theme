$(document).ready(function() {
  'use strict'

  $('.can-do-steps .col').hover(function() {
    const step = $(this).data('id')
    $('#step-' + step).toggleClass('active')
  })
})
