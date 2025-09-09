import Hammer from 'hammerjs';
import {useSlides} from './useSlides';
import {Slide} from './Slide';
import {CarouselControls} from './CarouselControls';
import {SlideBackground} from './SlideBackground';
import {StaticCaption} from './StaticCaption';

const {useRef, useEffect, useState} = wp.element;
const isRTL = document.querySelector('html').dir === 'rtl';

export const CarouselHeaderFrontend = ({slides, carousel_autoplay, className, decoding}) => {
  const [autoplayPaused, setAutoplayPaused] = useState(false);
  const slidesRef = useRef([]);
  const containerRef = useRef(null);
  const {
    currentSlide,
    goToSlide,
    goToNextSlide,
    goToPrevSlide,
    setCarouselHeight,
    autoplayCancelled,
  } = useSlides(slidesRef, slides.length - 1, containerRef);

  useEffect(() => {
    if (!containerRef.current) {
      return;
    }

    const carouselElement = containerRef.current;
    const carouselHeadHammer = new Hammer(carouselElement, {recognizers: []});
    const hammer = new Hammer.Manager(carouselHeadHammer.element);
    const swipe = new Hammer.Swipe();
    // Only allow horizontal swiping (not vertical swiping)
    swipe.set({direction: Hammer.DIRECTION_HORIZONTAL});
    hammer.add(swipe);

    hammer.on('swipeleft', isRTL ? goToPrevSlide : goToNextSlide);
    hammer.on('swiperight', isRTL ? goToNextSlide : goToPrevSlide);

    return () => {
      hammer.off('swipeleft', isRTL ? goToPrevSlide : goToNextSlide);
      hammer.off('swiperight', isRTL ? goToNextSlide : goToPrevSlide);
    };
  }, [currentSlide, goToNextSlide, goToPrevSlide]);

  useEffect(() => {
    if (!containerRef.current) {
      return;
    }

    const currentSlideRef = slidesRef.current[currentSlide];
    if (currentSlideRef) {
      setCarouselHeight(currentSlideRef);

      window.addEventListener('resize', () => setCarouselHeight(currentSlideRef));
    }

    return () => window.removeEventListener('resize', () => setCarouselHeight(currentSlideRef));
  }, [currentSlide, setCarouselHeight]);

  // Set up the autoplay for the slides
  const timerRef = useRef(null);
  useEffect(() => {
    if (carousel_autoplay && slides.length > 1 && !autoplayPaused && !autoplayCancelled) {
      if (timerRef.current) {
        clearTimeout(timerRef.current);
      }
      timerRef.current = setTimeout(() => goToNextSlide(true), 10000);
      return () => clearTimeout(timerRef.current);
    } else if (timerRef.current) {
      clearTimeout(timerRef.current);
    }
  }, [currentSlide, goToNextSlide, slides, carousel_autoplay, autoplayPaused, autoplayCancelled]);

  return (
    <section
      className={`block block-header alignfull carousel-header ${className ?? ''}`}
      ref={containerRef}
      onMouseEnter={() => setAutoplayPaused(true)}
      onMouseLeave={() => setAutoplayPaused(false)}
    >
      <div className="carousel-wrapper-header">
        <div className="carousel-inner" role="listbox">
          {slides.map((slide, index) => (
            <Slide
              key={index}
              active={currentSlide === index}
              ref={element => slidesRef ? slidesRef.current[index] = element : null}
            >
              <SlideBackground decoding={decoding} slide={slide} />
              <StaticCaption slide={slide} />
            </Slide>
          ))}
          <CarouselControls
            goToPrevSlide={() => goToPrevSlide(carousel_autoplay)}
            goToNextSlide={() => goToNextSlide(carousel_autoplay)}
            goToSlide={goToSlide}
            slides={slides}
            currentSlide={currentSlide}
          />
        </div>
      </div>
    </section>
  );
};
