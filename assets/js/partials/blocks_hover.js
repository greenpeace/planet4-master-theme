// Underline headlines on thumbnail hover.

$(document).ready(function() {
  'use strict'

  $('.article-list-item-image').hover(
    function() {
      $('.article-list-item-headline', $(this).parent()).addClass('article-hover')
    }, function() {
      $('.article-list-item-headline', $(this).parent()).removeClass('article-hover')
    })

  $('.four-column-content-symbol').hover(
    function() {
      $('h4', $(this).parent()).addClass('four-column-hover')
    }, function() {
      $('h4', $(this).parent()).removeClass('four-column-hover')
    })
})
