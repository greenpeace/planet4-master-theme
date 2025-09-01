import PhotoSwipe from '../../../../../node_modules/photoswipe/dist/photoswipe.js';
import PhotoSwipeUI_Default from '../../../../../node_modules/photoswipe/dist/photoswipe-ui-default.js';

const {useEffect, useRef, useState} = wp.element;

// `items` should be an array of object with this shape:
// [{ src, w, h, title }, ...]
// See: https://photoswipe.com/documentation/getting-started.html
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

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    const preloadItems = items.map(item => {
      if (!item.w || !item.h) {
        const img = new Image();
        img.src = item.originalSrc.url;
        item.w = item.originalSrc.width;
        item.h = item.originalSrc.height;
        item.src = item.originalSrc.url;
      }
      return item;
    });

    const photoSwipe = new PhotoSwipe(photoSwipeElement, PhotoSwipeUI_Default, preloadItems, photoSwipeOptions);

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

