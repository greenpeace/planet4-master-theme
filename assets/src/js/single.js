export const setupImageZoomer = function($) {
  'use strict';

  function unzoom() {
    $('.image-zoomer').fadeOut(500, function() {
      $('.image-zoomer-content').html('');
    });
  }

  function zoomImage(image) {
    const imageClone = $(image).clone();
    $('.image-zoomer-content').html('');
    $(imageClone).appendTo('.image-zoomer-content');
    $('.image-zoomer').fadeIn();
    $('.image-zoomer').off('click').on('click', unzoom);
  }

  $('.post-content img').each(function() {
    const isInBlock = $(this).parents('.block').length;
    if (!isInBlock) {
      $(this).off('click').on('click', function() {
        zoomImage(this);
      });
    }
  });
};
