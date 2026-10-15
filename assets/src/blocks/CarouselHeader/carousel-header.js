import {useSlides} from './useSlides';
import {Slide} from './Slide';
import {CarouselControls} from './CarouselControls';
import {SlideBackground} from './SlideBackground';
import {StaticCaption} from './StaticCaption';

const {useRef, useMemo} = wp.element;
const {__} = wp.i18n;

export const CarouselHeaderFrontend = ({slides, carousel_autoplay, className, decoding}) => {
  const slidesRef = useRef([]);
  const containerRef = useRef(null);
  const headingsRef = useRef([]);
  const indicatorsRef = useRef();
  const {
    currentSlide,
    goToSlide,
    goToNextSlide,
    goToPrevSlide,
    handleAutoplay,
    setAutoplay,
    autoplay,
    handleUserInteraction,
  } = useSlides(slidesRef, slides.length, containerRef, carousel_autoplay, headingsRef, indicatorsRef);

  return useMemo(() => (
    <section
      className={`block block-header alignfull carousel-header ${className ?? ''}`}
      ref={containerRef}
      aria-label={__('Greenpeace highlights', 'planet4-blocks')}
      aria-roledescription="carousel"
      onFocus={() => setAutoplay(false)}
      onTouchStart={() => setAutoplay(false)}
    >
      {(slides.length > 1) ? (
        <CarouselControls
          goToPrevSlide={goToPrevSlide}
          goToNextSlide={goToNextSlide}
          setAutoplay={setAutoplay}
          goToSlide={goToSlide}
          handleAutoplay={handleAutoplay}
          slides={slides}
          currentSlide={currentSlide}
          autoplay={autoplay}
          autoplayToggle={carousel_autoplay}
          ref={indicatorsRef}
        />
      ) : null}
      <div className="carousel-wrapper-header">
        <ul className="carousel-inner" role="listbox">
          {slides.map((slide, index) => {
            const isActive = currentSlide === index;

            return (
              <Slide
                key={index}
                active={isActive}
                focusable={isActive}
                ref={element => slidesRef ? slidesRef.current[index] = element : null}
                handleUserInteraction={handleUserInteraction}
              >
                <SlideBackground decoding={decoding} slide={slide} />
                <StaticCaption slide={slide} focusable={isActive} />
              </Slide>);
          })
          }
        </ul>
      </div>
    </section>
  ), [
    className,
    currentSlide,
    decoding,
    autoplay,
    slides,
    setAutoplay,
    carousel_autoplay,
    handleAutoplay,
    goToSlide,
    goToPrevSlide,
    goToNextSlide,
    handleUserInteraction,
  ]);
};
