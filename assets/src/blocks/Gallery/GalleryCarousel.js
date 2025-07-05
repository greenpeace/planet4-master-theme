import {IMAGE_SIZES} from './imageSizes';
import {getCaptionWithCredits} from './getCaptionWithCredits.js';

const {__} = wp.i18n;
const {useState, useEffect, useRef} = wp.element;

const isRTL = document.querySelector('html').dir === 'rtl';

// This will trigger the browser to synchronously calculate the style and layout
// You can find a list of examples here: https://gist.github.com/paulirish/5d52fb081b3570c81e3a
const reflow = element => element.offsetHeight;

export const GalleryCarousel = ({images, onImageClick, isEditing}) => {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [sliding, setSliding] = useState(false);
  const lastSlide = images.length - 1;
  const timerRef = useRef(null);
  const slidesRef = useRef([]);
  const containerRef = useRef(null);

  const getOrder = newSlide => {
    let order = newSlide < currentSlide ? 'prev' : 'next';
    if (newSlide === lastSlide && currentSlide === 0 && order !== 'prev') {
      order = 'prev';
    } else if (newSlide === 0 && currentSlide === lastSlide && order !== 'next') {
      order = 'next';
    }
    return order;
  };

  const goToSlide = newSlide => {
    const nextElement = slidesRef.current[newSlide];
    const activeElement = slidesRef.current[currentSlide];
    if (newSlide !== currentSlide && nextElement && activeElement && !sliding) {
      setSliding(true);
      const order = getOrder(newSlide);
      const direction = order === 'next' ? 'start' : 'end';
      const orderClassname = `carousel-item-${order}`;
      const directionClassname = `carousel-item-${direction}`;

      nextElement.classList.add(orderClassname);

      reflow(nextElement);

      activeElement.classList.add(directionClassname);
      nextElement.classList.add(directionClassname);

      setTimeout(() => {
        nextElement.classList.remove(directionClassname, orderClassname);
        activeElement.classList.remove(orderClassname, directionClassname);
        setSliding(false);
        setCurrentSlide(newSlide);
      }, 600);
    }
  };

  const goToNextSlide = () => goToSlide(currentSlide === lastSlide ? 0 : currentSlide + 1);
  const goToPrevSlide = () => goToSlide(currentSlide === 0 ? lastSlide : currentSlide - 1);

  // Set up the autoplay for the slides
  useEffect(() => {
    if (images.length > 1) {
      if (timerRef.current) {
        clearTimeout(timerRef.current);
      }
      timerRef.current = setTimeout(goToNextSlide, 10000);
      return () => clearTimeout(timerRef.current);
    }
  }, [currentSlide, images]);

  // Set up swiping on mobile
  useEffect(() => {
    if (isEditing || !containerRef.current) {
      return;
    }

    const carouselElement = containerRef.current;
    const carouselHammer = new Hammer(carouselElement, {recognizers: []}); // eslint-disable-line no-undef
    const hammer = new Hammer.Manager(carouselHammer.element); // eslint-disable-line no-undef
    const swipe = new Hammer.Swipe(); // eslint-disable-line no-undef
    // Only allow horizontal swiping (not vertical swiping)
    swipe.set({direction: Hammer.DIRECTION_HORIZONTAL}); // eslint-disable-line no-undef
    hammer.add(swipe);

    hammer.on('swipeleft', isRTL ? goToPrevSlide : goToNextSlide);
    hammer.on('swiperight', isRTL ? goToNextSlide : goToPrevSlide);

    return () => {
      hammer.off('swipeleft', isRTL ? goToPrevSlide : goToNextSlide);
      hammer.off('swiperight', isRTL ? goToNextSlide : goToPrevSlide);
    };
  }, [currentSlide]);

  return (
    <div className="carousel slide" ref={containerRef}>
      <div className="carousel-inner" role="listbox">
        {images.length > 1 &&
          <ol className="carousel-indicators">
            {images.map((image, index) =>
              <li
                key={`indicator-${index}`}
                onClick={() => goToSlide(index)}
                className={index === currentSlide ? 'active' : ''}
                role="presentation"
              />
            )}
          </ol>
        }
        {images.length > 1 &&
          <button className="carousel-control-prev" onClick={goToPrevSlide}>
            <span className="carousel-control-prev-icon" aria-hidden="true"><i></i></span>
            <span className="visually-hidden">{__('Previous', 'planet4-blocks')}</span>
          </button>
        }
        {images.map((image, index) => (
          <div
            key={image.image_src}
            className={`carousel-item ${index === currentSlide ? 'active' : ''}`}
            ref={element => slidesRef.current[index] = element}
          >
            <img
              loading="lazy"
              src={image.image_src}
              {...(image.image_srcset ? {srcSet: image.image_srcset} : {})}
              sizes={IMAGE_SIZES.carousel}
              style={{objectPosition: image.focus_image}}
              alt={image.alt_text}
              title={image.alt_text}
              data-index={index}
              onClick={onImageClick}
              role="presentation"
            />

            {(image.caption || image.credits) && (
              <div className="carousel-caption">
                <p>
                  {getCaptionWithCredits(image)}
                </p>
              </div>
            )}
          </div>
        ))}
        {images.length > 1 && (
          <button className="carousel-control-next" onClick={goToNextSlide}>
            <span className="carousel-control-next-icon" aria-hidden="true"><i></i></span>
            <span className="visually-hidden">{__('Next', 'planet4-blocks')}</span>
          </button>
        )}
      </div>
    </div>
  );
};
