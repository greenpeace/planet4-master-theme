import Hammer from 'hammerjs';
import {useSlides} from './useSlides';
import {Slide} from './Slide';
import {CarouselControls} from './CarouselControls';
import {SlideBackground} from './SlideBackground';
import {StaticCaption} from './StaticCaption';

const {useRef, useMemo} = wp.element;

export const CarouselHeaderFrontend = ({slides, carousel_autoplay, className, decoding}) => {
  const slidesRef = useRef([]);
  const containerRef = useRef(null);
  const {
    currentSlide,
    goToSlide,
    goToNextSlide,
    goToPrevSlide,
    handleAutoplay,
    setAutoplay,
    autoplay,
  } = useSlides(slidesRef, slides.length, containerRef, carousel_autoplay);

  return useMemo(() => (
    <section
      className={`block block-header alignfull carousel-header ${className ?? ''}`}
      ref={containerRef}
      onMouseEnter={() => {
        if (window.innerWidth > 991) {
          setAutoplay(true);
        }
      }}
      onMouseLeave={() => {
        if (window.innerWidth > 991) {
          setAutoplay(false);
        }
      }}
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
        </div>
      </div>
      <CarouselControls
        goToPrevSlide={() => {
          setAutoplay(false);
          goToPrevSlide();
        }}
        goToNextSlide={() => {
          setAutoplay(false);
          goToNextSlide();
        }}
        goToSlide={goToSlide}
        handleAutoplay={handleAutoplay}
        slides={slides}
        currentSlide={currentSlide}
        autoplay={autoplay}
      />
    </section>
  ), [
    className,
    currentSlide,
    decoding,
    autoplay,
    slides,
    goToSlide,
    setAutoplay,
    handleAutoplay,
    goToPrevSlide,
    goToNextSlide,
  ]);
};
