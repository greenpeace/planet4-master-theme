var $ = jQuery;

$(document).on('ready', function() {
  function unzoom() {
    $('.image-zoomer').fadeOut(500, function() {
      $('.image-zoomer-content').html('');
    });
  }

  function zoomImage(image) {
    var imageClone = $(image).clone();
    $('.image-zoomer-content').html('');
    $(imageClone).appendTo('.image-zoomer-content');
    $('.image-zoomer').fadeIn();
    $('.image-zoomer').off('click').on('click', unzoom);
  }

  $('.post-content img').each(function() {
    $(this).off('click').on('click', function() {
      zoomImage(this);
    });
  });
});