import {useEffect, useRef} from '@wordpress/element';

/**
 * Creates and initializes a PhotoSwipe lightbox instance.
 *
 * Dynamically imports the PhotoSwipe library and sets up a custom caption element.
 *
 * @async
 * @function createPhotoSwipeLightbox
 * @param {Object}         params            - The configuration object.
 * @param {Array|Function} params.dataSource - The array or function providing the images for PhotoSwipe.
 * @param {Function}       [params.onClose]  - Optional callback executed when the lightbox closes.
 * @return {Promise<Object>} The initialized PhotoSwipe lightbox instance.
 */
async function createPhotoSwipeLightbox({dataSource, onClose}) {
  // eslint-disable-next-line import/no-unresolved
  const {default: PhotoSwipeLightbox} = await import('photoswipe/lightbox');
  // eslint-disable-next-line import/no-unresolved
  await import('photoswipe/style.css');

  const lightbox = new PhotoSwipeLightbox({
    showHideAnimationType: 'none',
    pswpModule: () => import('photoswipe'),
    dataSource,
  });

  // Register a custom caption UI element
  lightbox.on('uiRegister', () => {
    lightbox.pswp.ui.registerElement({
      name: 'custom-caption',
      order: 9,
      isButton: false,
      appendTo: 'root',
      html: '',
      /**
       * Initializes and updates the caption element on slide change.
       *
       * @param {HTMLElement} el - The caption element to modify.
       */
      onInit: el => {
        lightbox.pswp.on('change', () => {
          const data = lightbox.pswp.currSlide?.data;
          const caption = data?.caption || '';

          if (caption.trim() === '') {
            // Hide completely when no caption
            el.style.display = 'none';
            el.innerHTML = '';
          } else {
            // Show and update text
            el.style.display = 'block';
            el.innerHTML = caption;
          }
        });
      },
    });
  });

  // Bind close event handler if provided
  if (onClose) {
    lightbox.on('close', onClose);
  }

  lightbox.init();
  return lightbox;
}

/**
 * React hook for initializing and controlling a PhotoSwipe lightbox.
 *
 * Sets up the lightbox when the component mounts and cleans it up when unmounted.
 *
 * @function usePhotoSwipeLightbox
 * @param {Object}   params           - Hook parameters.
 * @param {Array}    params.options   - Array of image objects used as the lightbox data source.
 * @param {Function} [params.onClose] - Optional callback triggered when the lightbox closes.
 * @return {Function} A function that opens the lightbox at a given image index.
 */
export function usePhotoSwipeLightbox({options, onClose}) {
  const lightboxRef = useRef(null);

  useEffect(() => {
    let lightbox;

    (async () => {
      lightbox = await createPhotoSwipeLightbox({
        dataSource: options,
        onClose,
      });
      lightboxRef.current = lightbox;
    })();

    return () => {
      if (lightbox) {
        lightbox.destroy();
        lightbox = null;
      }
    };
  }, [options]);

  /**
   * Opens the lightbox at the specified slide index.
   *
   * @param {number} index - Index of the image to open.
   */
  const open = index => {
    if (lightboxRef.current) {
      lightboxRef.current.loadAndOpen(index);
    }
  };

  return open;
}

/**
 * Initializes a PhotoSwipe lightbox outside React components.
 *
 * Useful for imperative use cases or when React hooks cannot be used.
 *
 * @async
 * @function initPhotoSwipeLightbox
 * @param {Object}   params           - The configuration object.
 * @param {Array}    params.items     - Array of image objects for the lightbox data source.
 * @param {Function} [params.onClose] - Optional callback executed when the lightbox closes.
 * @return {Promise<Object>} The initialized PhotoSwipe lightbox instance.
 */
export async function initPhotoSwipeLightbox({items, onClose}) {
  return createPhotoSwipeLightbox({dataSource: items, onClose});
}
