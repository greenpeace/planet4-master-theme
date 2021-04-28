import { Arrows } from './Arrows';
import { Indicators } from './Indicators';

export const ArrowsAndIndicators = ({
  goToPrevSlide = null,
  goToNextSlide = null,
  goToSlide = null,
  currentSlide = null,
  slides = null,
}) => {
  return slides.length > 1 && (
    <>
      <Arrows
        goToPrevSlide={goToPrevSlide}
        goToNextSlide={goToNextSlide}
      />
      <Indicators
        slides={slides}
        currentSlide={currentSlide}
        goToSlide={goToSlide}
      />
    </>
  );
};
