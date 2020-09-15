const GRID_GALLERY_CLASS = 'gallery-grid';
const SLIDER_GALLERY_CLASS = 'carousel-wrap';
const THREE_COLUMN_GALLERY_CLASS = 'three-column-images';

const renderIndicators = (gallery) => {
  const indicatorSpans = document.querySelectorAll('.p4-photoswipe-indicators-wrapper span');
  const indicatorsWrapper = document.querySelector('.p4-photoswipe-indicators-wrapper');

  const topGap = gallery.currItem.bounds ? gallery.currItem.bounds.center.y : 0;
  let currentItemBottomBorder = (gallery.currItem.h * gallery.currItem.fitRatio) + topGap;
  indicatorsWrapper.style.top = currentItemBottomBorder + 'px';

  if (indicatorSpans.length !== gallery.options.getNumItemsFn()) {
    indicatorsWrapper.innerHTML = '';
    for (let i = 0; i < gallery.options.getNumItemsFn(); i++) {
      let indicatorClickArea = document.createElement('span');
      indicatorClickArea.classList.add('p4-photoswipe-indicator-click-area')
      let indicatorBar = document.createElement('span');
      indicatorBar.classList.add('p4-photoswipe-indicator-bar')
      indicatorClickArea.appendChild(indicatorBar);
      indicatorClickArea.addEventListener('click', e => {
        e.preventDefault();
        gallery.goTo(i);
      });
      indicatorsWrapper.appendChild(indicatorClickArea);
    }
  }

  indicatorSpans.forEach(item => item.classList.remove(['active']));

  const currentIndicator = document.querySelector(`.p4-photoswipe-indicators-wrapper > span:nth-child(${gallery.getCurrentIndex() + 1})`);
  currentIndicator.classList.add(['active']);
}

const setupPhotoSwipe = function(items, clickedIndex) {
  var pswpElement = document.querySelector('.pswp');

  var options = {
    shareEl: false,
    fullscreenEl: false,
    zoomEl: false,
    index: clickedIndex,
    closeOnScroll: false,
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

  gallery.listen('afterChange', () => {
    renderIndicators(gallery);
  });

  gallery.listen('resize', () => {
    renderIndicators(gallery);
  });

  window.addEventListener('resize', () => {
    renderIndicators(gallery);
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
  galleryImages.forEach(function(image) {
    const tempImage = {};
    tempImage.src = image.src ? image.src : image.dataset.src;
    tempImage.w = 0;
    tempImage.h = 0;

    let caption = null;

    switch (galleryClass) {
      case GRID_GALLERY_CLASS:
      case THREE_COLUMN_GALLERY_CLASS:
        caption = image.getAttribute('alt');
      break;
      case SLIDER_GALLERY_CLASS:
        caption = image.parentNode().querySelector('.carousel-caption p').innerHTML;
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

  document.querySelectorAll('.post-content .wp-block-image img, .page-template .wp-block-image img').forEach(
    (clickedImage, clickedImageIndex) => {
      const galleryItemsFromImageBlocks = buildItemsFromImageBlocks();

      clickedImage.addEventListener('click', () => {
        setupPhotoSwipe(galleryItemsFromImageBlocks, clickedImageIndex);
      });
    }
  );

  [GRID_GALLERY_CLASS, SLIDER_GALLERY_CLASS, THREE_COLUMN_GALLERY_CLASS].forEach( galleryClass => {
    document.querySelectorAll(`.${galleryClass} img`).forEach(
      (clickedImage, clickedImageIndex) => {
        const galleryImages = clickedImage.closest(`.${galleryClass}`).querySelectorAll('img');
        const galleryItems = buildItemsFromGallery(galleryImages, galleryClass);

        clickedImage.addEventListener('click', () => {
          setupPhotoSwipe(galleryItems, clickedImageIndex);
        });
      }
    );
  });
};

window.setupLightBox = setupLightBox;
