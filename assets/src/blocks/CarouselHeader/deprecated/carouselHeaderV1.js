export const carouselHeaderV1 = {
  attributes: {
    carousel_autoplay: {
      type: 'boolean',
    },
    slides: {
      type: 'array',
      default: [
        {
          image: null,
          focal_points: {},
          header: '',
          header_size: 'h1',
          description: '',
          link_text: '',
          link_url: '',
          link_url_new_tab: false,
        }
      ],
      validation: slides => {
        const invalidSlides = slides.filter(slide => slide.image === null);

        const isValid = invalidSlides.length === 0;
        const messages = invalidSlides.map( invalidSlide => {
          return `Carousel Header Block: Slide ${ slides.findIndex( slide => slide === invalidSlide ) + 1 } has no image`
        });

        return { isValid, messages };
      }
    },
  },
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
