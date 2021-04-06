import { useRef, useEffect } from '@wordpress/element';
import { useSlides } from './useSlides';
import { CarouselHeaderStaticContent } from './CarouselHeaderStaticContent';

const { __ } = wp.i18n;

export const CarouselHeaderFrontend = ({ attributes }) => {
  const { slides, carousel_autoplay } = attributes; // TODO autoplay?

  const slidesRef = useRef([]);
  const { currentSlide, goToSlide, goToNextSlide, goToPrevSlide } = useSlides(slidesRef, slides.length);

  const containerRef = useRef(null);
  useEffect(() => {
    if (!containerRef.current) {
      return;
    }

    const carouselElement = containerRef.current;
    const carouselHeadHammer = new Hammer(carouselElement, { recognizers: [] });
    const hammer = new Hammer.Manager(carouselHeadHammer.element);
    const swipe = new Hammer.Swipe();
    hammer.add(swipe);

    const swipeListeners = [
      goToNextSlide,
      goToPrevSlide
    ];

    // if (isRTL) {
    //   swipeListeners.reverse();
    // }

    hammer.on('swipeleft', swipeListeners[0]);
    hammer.on('swiperight', swipeListeners[1]);
  }, []);

  useEffect(() => {
    if (!containerRef.current) {
      return;
    }

    const carouselElement = containerRef.current;

    if (slidesRef.current[currentSlide]) {
      const getSlideHeight = slideRef => {
        return `${slideRef.querySelector('.carousel-item-mask .background-holder').offsetHeight + slideRef.querySelector('.carousel-caption').offsetHeight} px`;
      };

      const setCarouselHeight = currentSlideRef => {
        if (window.matchMedia('(max-width: 992px)').matches) {
          carouselElement.querySelectorAll('.carousel-inner, .carousel-item-mask').forEach(container =>
            container.style.height = getSlideHeight(currentSlideRef)
          )
        } else {
          carouselElement.querySelectorAll('.carousel-inner, .carousel-item-mask').forEach(container =>
            container.style.height = null
          )
        }
      };

      setCarouselHeight(slidesRef.current[currentSlide]);
    }
  }, []);

  return (
    <CarouselHeaderStaticContent
      slides={slides}
      slidesRef={slidesRef}
      containerRef={containerRef}
      goToNextSlide={goToNextSlide}
      goToPrevSlide={goToPrevSlide}
      goToSlide={goToSlide}
      currentSlide={currentSlide}
    />
  );
}
