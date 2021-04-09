export const carouselHeaderV1 = {
  isEligible({ slides }) {
    return !!slides.find(slide => slide.header_size !== 'undefined');
  },
  migrate( { slides, ...attributes } ) {
    return {
      ...attributes,
      slides: slides.map(({ header_size, ...otherSlideAttributes }) => otherSlideAttributes),
    };
  },
  save: () => null
}
