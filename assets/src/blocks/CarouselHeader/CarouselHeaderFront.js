import { FullWidthCarouselHeader } from './FullWidthCarouselHeader';

export const initializeCarouselHeader = () => {
  const carouselHeaderWrapper = document.getElementById('carousel-wrapper-header');
  if (carouselHeaderWrapper) {
    FullWidthCarouselHeader.setup();
  }
};
