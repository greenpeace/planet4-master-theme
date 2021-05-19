import { SlidesContainer } from './SlidesContainer';
import { Slide } from './Slide';

// TODO: Move these two into Slide?
import { SlideBackground } from './SlideBackground';
import { StaticCaption } from './StaticCaption';

export const CarouselHeaderStaticContent = ({
  slides = [],
  slidesRef,
  containerRef,
  goToPrevSlide = null,
  goToNextSlide = null,
  goToSlide = null,
  currentSlide = 0,
}) => (
  <SlidesContainer
    slides={slides}
    slidesRef={slidesRef}
    ref={containerRef}
    goToSlide={goToSlide}
    goToNextSlide={goToNextSlide}
    goToPrevSlide={goToPrevSlide}
    currentSlide={currentSlide}
  >
    {slides.map((slide, index) => (
      <Slide
        key={index}
        active={currentSlide == index}
        ref={element => slidesRef ? slidesRef.current[index] = element : null}
      >
        <SlideBackground slide={slide} />
        <StaticCaption slide={slide} />
      </Slide>
    ))}
  </SlidesContainer>
);
