const {useEffect, useRef, useState} = wp.element;

/**
 * Lightbox component for displaying images using PhotoSwipe.
 * Dynamically imports PhotoSwipe and PhotoSwipeUI_Default only when needed.
 *
 * @param {Object}        props           - Component props.
 * @param {number}        [props.index=0] - The initial index of the image to open.
 * @param {boolean}       props.isOpen    - Whether the lightbox is currently open.
 * @param {Array<Object>} props.items     - Array of image objects to display.
 * @param {Function}      [props.onClose] - Callback invoked when the lightbox closes.
 *
 * @return {JSX.Element|null} The PhotoSwipe lightbox portal.
 *
 * @see https://photoswipe.com/documentation/getting-started.html
 */
export const Lightbox = ({index, isOpen, items, onClose = () => {}}) => {
  let photoSwipeElement = useRef(null);

  const photoSwipeOptions = {
    index: index || 0,
    closeOnScroll: false,
    history: false,
    fullscreenEl: false,
    zoomEl: false,
    shareEl: false,
    counterEl: false,
  };

  const [currentIndex, setCurrentIndex] = useState(photoSwipeOptions.index);

  /**
   * Initializes and launches a PhotoSwipe instance.
   *
   * @param {typeof import('photoswipe/dist/photoswipe.js').default}            PhotoSwipe           - The PhotoSwipe class.
   * @param {typeof import('photoswipe/dist/photoswipe-ui-default.js').default} PhotoSwipeUI_Default - The default PhotoSwipe UI.
   * @return {void}
   */
  const launchPhotoSwipe = (PhotoSwipe, PhotoSwipeUI_Default) => {
    const photoSwipe = new PhotoSwipe(photoSwipeElement, PhotoSwipeUI_Default, items, photoSwipeOptions);

    // eslint-disable-next-line no-shadow
    photoSwipe.listen('gettingData', (index, galleryItem) => {
      if (galleryItem.w < 1 || galleryItem.h < 1) {
        const imageSizeHandler = new Image();
        imageSizeHandler.onload = function() {
          galleryItem.w = this.width;
          galleryItem.h = this.height;
          photoSwipe.updateSize(true);
        };
        imageSizeHandler.src = galleryItem.src;
      }
    });

    photoSwipe.listen('destroy', () => {
      onClose();
    });

    photoSwipe.listen('close', () => {
      onClose();
    });

    photoSwipe.listen('afterChange', () => {
      setCurrentIndex(photoSwipe.getCurrentIndex());
    });

    photoSwipe.init();
  };

  useEffect(() => {
    if (!isOpen) {
      return;
    }
    (async () => {
      const {default: PhotoSwipe} = await import('photoswipe/dist/photoswipe.js');
      const {default: PhotoSwipeUI_Default} = await import('photoswipe/dist/photoswipe-ui-default.js');
      launchPhotoSwipe(PhotoSwipe, PhotoSwipeUI_Default);
    })();
  }, [items, isOpen, index]);

  return wp.element.createPortal(
    <div
      className="pswp"
      tabIndex="-1"
      role="dialog"
      aria-hidden="true"
      ref={node => {
        photoSwipeElement = node;
      }}
    >
      <div className="pswp__bg" />
      <div className="pswp__scroll-wrap">
        <div className="pswp__container">
          <div className="pswp__item" />
          <div className="pswp__item" />
          <div className="pswp__item" />
        </div>

        <div className="pswp__ui pswp__ui--hidden">
          <div className="pswp__top-bar">
            <div className="pswp__counter" />
            <button className="pswp__button pswp__button--close" title="Close (Esc)" />
            <button className="pswp__button pswp__button--share" title="Share" />
            <button className="pswp__button pswp__button--fs" title="Toggle fullscreen" />
            <button className="pswp__button pswp__button--zoom" title="Zoom in/out" />
            <div className="pswp__preloader">
              <div className="pswp__preloader__icn">
                <div className="pswp__preloader__cut">
                  <div className="pswp__preloader__donut" />
                </div>
              </div>
            </div>
          </div>
          <div className="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
            <div className="pswp__share-tooltip" />
          </div>
          <button className="pswp__button pswp__button--arrow--left" title="Previous (arrow left)" />
          <button className="pswp__button pswp__button--arrow--right" title="Next (arrow right)" />

          <div className="pswp__caption">
            <div className="pswp__caption__center" />
          </div>
          <div className="p4-caption-and-indicators">
            <div className="p4-photoswipe-indicators-wrapper">
              {
                items.length > 1 && items.map((item, idx) =>
                  <span className={`p4-photoswipe-indicator-click-area ${idx === currentIndex ? 'active' : ''}`} key={idx}>
                    <span className="p4-photoswipe-indicator-bar" />
                  </span>
                )
              }
            </div>
          </div>
        </div>
      </div>
    </div>,
    document.body
  );
};
