import {initPhotoSwipeLightbox} from './photoSwipeLightbox.js';

/**
 * Sets up an image and its optional caption for PhotoSwipe lightbox functionality.
 *
 * @param {string}      [imageSelector='img']  - The CSS selector used to find the image within the image block.
 * @param {string|null} [captionSelector=null] - The CSS selector used to find the caption element, or `null` if none.
 * @return {Function} - A function that takes an image block element and initializes the lightbox setup for it.
 */
const setupImageAndCaption = (imageSelector = 'img', captionSelector = null) => {
  return imageBlock => {
    const image = imageBlock.querySelector(imageSelector);
    if (!image) {return;}

    // Derive full/original image URL by removing size suffix
    let fullSrc = image.src || image.dataset.src || '';
    fullSrc = fullSrc.replace(/-\d+x\d+(?=\.\w+$)/, '');

    let width = null;
    let height = null;

    // Extract dimensions from srcset — pick the *largest* one
    if (image.srcset) {
      const sizeMatches = image.srcset.match(/-(\d+)x(\d+)\.\w+/g);
      if (sizeMatches && sizeMatches.length) {
        const sizes = sizeMatches.map(s => {
          const [, w, h] = s.match(/-(\d+)x(\d+)/);
          return {w: parseInt(w, 10), h: parseInt(h, 10)};
        });
        const largest = sizes.reduce((a, b) => (a.w > b.w ? a : b));
        width = largest.w;
        height = largest.h;
      }
    }

    // Fallback: try to read from <img> attributes
    if (!width && image.width) {width = image.width;}
    if (!height && image.height) {height = image.height;}

    // Get caption if exists
    const captionEl = captionSelector ? imageBlock.querySelector(captionSelector) : null;
    const caption = captionEl ? captionEl.innerHTML.trim() : '';

    // Build PhotoSwipe item
    const item = {
      srcset: image.srcset || image.dataset.srcset || '',
      src: fullSrc,
      width,
      height,
      alt: image.alt || '',
      caption,
    };

    // Attach click event to open lightbox
    image.addEventListener('click', async e => {
      e.preventDefault();

      const lightbox = await initPhotoSwipeLightbox({items: [item]});
      lightbox.loadAndOpen(0);
    });
  };
};

/**
 * Initializes PhotoSwipe lightbox functionality for various types of image elements across the page.
 *
 * It applies lightbox behavior to:
 * - Gutenberg image blocks without existing links.
 * - WordPress captioned images (with `.wp-caption` or `.wp-caption-text`).
 * - Images inside paragraphs that are not captions or excluded via `.force-no-lightbox`.
 * - Media & Text block images (`.wp-block-media-text`).
 *
 * @function setupLightboxForImages
 * @return {void}
 */
export const setupLightboxForImages = function () {
  const imageBlocks = [...document.querySelectorAll('.wp-block-image:not(.force-no-lightbox)')];
  const imageBlocksWithoutLinks = imageBlocks.filter(imageBlock => {
    const image = imageBlock.querySelector('img');
    return image && image.parentElement.tagName !== 'A';
  });

  imageBlocksWithoutLinks.forEach(setupImageAndCaption('img', 'figcaption'));

  const imagesWithCaptions = document.querySelectorAll('.post-content .wp-caption, .page-content .wp-caption');
  imagesWithCaptions.forEach(setupImageAndCaption('img', '.wp-caption-text'));

  const imagesInParagraphs = document.querySelectorAll(
    '.post-content p:not(.wp-caption):not(.force-no-lightbox), .page-content p:not(.wp-caption):not(.force-no-lightbox)'
  );
  imagesInParagraphs.forEach(setupImageAndCaption('img'));

  const mediaAndTextImages = document.querySelectorAll('.wp-block-media-text:not(.force-no-lightbox)');
  mediaAndTextImages.forEach(setupImageAndCaption());
};
