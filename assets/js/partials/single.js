var $ = jQuery;

$(document).on('ready', function() {
  function unzoom() {
    $('.image-zoomer').fadeOut(500, function() {
      $('.image-zoomer').html('');
    });
  }

  function zoomImage(image) {
    var imageClone = $(image).clone();
    $('.image-zoomer').html('');
    $(imageClone).appendTo('.image-zoomer');
    $('.image-zoomer').fadeIn();
    $('.image-zoomer').off('click').on('click', unzoom);
  }

  $('.post-content img').each(function() {
    $(this).off('click').on('click', function() {
      zoomImage(this);
    });
  });
});