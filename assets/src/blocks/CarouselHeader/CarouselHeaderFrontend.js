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
    >
      <div className="carousel-wrapper-header">
        <ul className="carousel-inner" role="listbox">
          {slides.map((slide, index) => <Slide
            key={index}
            active={currentSlide === index}
            focusable={currentSlide === index}
            ref={element => slidesRef ? slidesRef.current[index] = element : null}
          >
            <SlideBackground decoding={decoding} slide={slide} />
            <StaticCaption slide={slide} focusable={currentSlide === index} />
          </Slide>)
          }
        </ul>
      </div>
      {(slides.length > 1) && (
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
          disableControls={carousel_autoplay}
        />
      )}
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
    carousel_autoplay,
  ]);
};
