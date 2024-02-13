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
        },
      ],
    },
  },
  isEligible({slides}) {
    return slides?.some(slide => typeof slide.header_size !== 'undefined');
  },
  migrate({slides, ...attributes}) {
    return {
      ...attributes,
      // eslint-disable-next-line no-unused-vars
      slides: slides.map(({header_size, ...otherSlideAttributes}) => otherSlideAttributes),
    };
  },
  save: () => null,
};
