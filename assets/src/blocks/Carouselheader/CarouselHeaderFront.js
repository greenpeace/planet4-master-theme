import { ZoomAndSlideCarouselHeader } from './ZoomAndSlideCarouselHeader';
import { FullWidthCarouselHeader } from './FullWidthCarouselHeader';

export const initializeCarouselHeader = function() {
  const $CarouselHeaderWrapper = $('#carousel-wrapper-header');
  if ($CarouselHeaderWrapper.length > 0) {
    switch ($CarouselHeaderWrapper.data('block-style')) {
    case 'full-width-classic':
      FullWidthCarouselHeader.setup();
      break;
    default:
      ZoomAndSlideCarouselHeader.setup();
      break;
    }
  }
};
