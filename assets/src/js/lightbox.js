const GRID_GALLERY_CLASS = 'gallery-grid';
const SLIDER_GALLERY_CLASS = 'carousel-wrap';
const THREE_COLUMN_GALLERY_CLASS = 'three-column-images';

const setupPhotoSwipe = function(items, clickedIndex) {
  var pswpElement = $('.pswp')[0];

  var options = {
    shareEl: false,
    fullscreenEl: false,
    zoomEl: false,
    index: clickedIndex,
  };

  var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
  gallery.listen('gettingData', function (index, galleryItem) {
    if (galleryItem.w < 1 || galleryItem.h < 1) {
      var imageSizeHandler = new Image();
      imageSizeHandler.onload = function () {
          galleryItem.w = this.width;
          galleryItem.h = this.height;
          gallery.updateSize(true);
      };
      imageSizeHandler.src = galleryItem.src;
    }
  });
  gallery.init();
}

export const buildItemsFromImageBlocks = function() {
  const imageBlocks = $('.wp-block-image');

  const items = [];
  imageBlocks.each(function() {
    const image = $(this).find('img');
    const tempImage = {};
    tempImage.src = image.attr('src') ? image.attr('src') : image.attr('data-src');
    tempImage.w = 0;
    tempImage.h = 0;

    const caption = $(this).find('figcaption');
    if (caption.length) {
      tempImage.title = caption.text();
    }

    items.push(tempImage);
  });

  return items;
}

export const buildItemsFromGallery = function(galleryImages, galleryClass) {
  const items = [];
  galleryImages.each(function() {
    const tempImage = {};
    tempImage.src = $(this).attr('src') ? $(this).attr('src') : $(this).attr('data-src');
    tempImage.w = 0;
    tempImage.h = 0;

    let caption = null;

    switch (galleryClass) {
      case GRID_GALLERY_CLASS:
      case THREE_COLUMN_GALLERY_CLASS:
        caption = $(this).attr('alt');
      break;
      case SLIDER_GALLERY_CLASS:
        caption = $(this).parent().find('.carousel-caption p').text();
      break;
    }

    if (caption.length) {
      tempImage.title = caption;
    }

    items.push(tempImage);
  });

  return items;
}


export const setupLightBox = function($) {
  'use strict';

  $('.post-content .wp-block-image img, .page-template .wp-block-image img').each(
    function (clickedImageIndex) {
      const galleryItemsFromImageBlocks = buildItemsFromImageBlocks();

      $(this).off('click').on('click', () => {
        setupPhotoSwipe(galleryItemsFromImageBlocks, clickedImageIndex);
      });
    }
  );

  [GRID_GALLERY_CLASS, SLIDER_GALLERY_CLASS, THREE_COLUMN_GALLERY_CLASS].forEach( galleryClass => {
    $(`.${galleryClass} img`).each(
      function (clickedImageIndex) {
        const clickedImage = this;
        const galleryImages = $(clickedImage).closest(`.${galleryClass}`).find('img');
        const galleryItems = buildItemsFromGallery(galleryImages, galleryClass);

        $(this).off('click').on('click', () => {
          setupPhotoSwipe(galleryItems, clickedImageIndex);
        });
      }
    );
  });
};
