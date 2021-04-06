import { ArrowsAndIndicators } from './ArrowsAndIndicators';

import {
  Children,
  cloneElement,
  forwardRef,
} from '@wordpress/element';

const StaticSlidesContainer = ({
  children,
  goToSlide = null,
  currentSlide = null,
  goToPrevSlide = null,
  goToNextSlide = null,
  slides = [],
}, ref) => {
  const renderChildSlides = passedProps => Children.map(children, child => {
      return child && cloneElement(child, passedProps)
    });

  return (
    <section
      className='block block-header block-wide carousel-header_full-width-classic'
      ref={ref}
    >
      <div className='carousel-wrapper-header'>
        <div className='carousel-inner' role='listbox'>
          {renderChildSlides({ currentSlide, goToSlide })}
          <ArrowsAndIndicators
            goToPrevSlide={goToPrevSlide}
            goToNextSlide={goToNextSlide}
            goToSlide={goToSlide}
            slides={slides}
            currentSlide={currentSlide}
          />
        </div>
      </div>
    </section>
  );
}

export const SlidesContainer = forwardRef(StaticSlidesContainer);
