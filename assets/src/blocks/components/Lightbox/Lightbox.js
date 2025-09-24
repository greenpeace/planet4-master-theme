import PhotoSwipe from '../../../../../node_modules/photoswipe/dist/photoswipe.js';
import PhotoSwipeUI_Default from '../../../../../node_modules/photoswipe/dist/photoswipe-ui-default.js';

const {useEffect, useRef, useState, useMemo} = wp.element;

// `items` should be an array of object with this shape:
// [{ src, w, h, title }, ...]
// See: https://photoswipe.com/documentation/getting-started.html
export const Lightbox = ({index = 0, isOpen, items, onClose = () => {}}) => {
  const photoSwipeElement = useRef(null);
  const [currentIndex, setCurrentIndex] = useState(index);

  const photoSwipeOptions = useMemo(() => ({
    index,
    closeOnScroll: false,
    history: false,
    fullscreenEl: false,
    zoomEl: false,
    shareEl: false,
    counterEl: false,
  }), [index]);

  useEffect(() => {
    if (!isOpen || !photoSwipeElement.current) {return;}

    const preloadItems = items.map(item => ({
      src: item.originalSrc?.url || item.src,
      w: item.originalSrc?.width || item.w,
      h: item.originalSrc?.height || item.h,
      title: item.title,
    }));

    const photoSwipe = new PhotoSwipe(
      photoSwipeElement.current,
      PhotoSwipeUI_Default,
      preloadItems,
      photoSwipeOptions
    );

    const getData = (i, galleryItem) => {
      if (!galleryItem.w || !galleryItem.h) {
        const img = new Image();
        img.onload = function () {
          galleryItem.w = this.width;
          galleryItem.h = this.height;
          photoSwipe.updateSize(true);
        };
        img.src = galleryItem.src;
      }
    };

    photoSwipe.listen('gettingData', getData);
    photoSwipe.listen('destroy', onClose);
    photoSwipe.listen('close', onClose);
    photoSwipe.listen('afterChange', () => setCurrentIndex(photoSwipe.getCurrentIndex()));

    photoSwipe.init();

    return () => {
      if (photoSwipe && photoSwipe._listeners) {
        photoSwipe.destroy();
      }
    };
  }, [isOpen, items, photoSwipeOptions, onClose]);

  return wp.element.createPortal(
    <div className="pswp" tabIndex="-1" role="dialog" aria-hidden="true" ref={photoSwipeElement}>
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

