import {CarouselHeaderEditor} from './CarouselHeaderEditor.js';
import {carouselHeaderV1} from './deprecated/carouselHeaderV1.js';
import {CarouselHeaderFrontend} from './CarouselHeaderFrontend';

const {registerBlockType} = wp.blocks;
const {RawHTML, renderToString} = wp.element;

const BLOCK_NAME = 'planet4-blocks/carousel-header';

const attributes = {
  carousel_autoplay: {
    type: 'boolean',
    default: false,
  },
  slides: {
    type: 'array',
    default: [{
      image: null,
      focal_points: {},
      header: '',
      description: '',
      link_text: '',
      link_url: '',
      link_url_new_tab: false,
    }],
    validation: slides => {
      const invalidSlides = slides.filter(slide => slide.image === null);

      const isValid = invalidSlides.length === 0;
      const messages = invalidSlides.map(invalidSlide => {
        return `Carousel Header Block: Slide ${slides.findIndex(slide => slide === invalidSlide) + 1} has no image`;
      });

      return {isValid, messages};
    },
  },
  currentImageIndex: {
    type: 'integer',
    default: 0,
  },
};

export const registerCarouselHeaderBlock = () =>
  registerBlockType(BLOCK_NAME, {
    title: 'Carousel Header',
    icon: 'welcome-widgets-menus',
    category: 'planet4-blocks',
    supports: {
      multiple: false, // Use the block just once per post.
      html: false, // Disable "Edit as HTMl" block option.
    },
    attributes,
    edit: CarouselHeaderEditor,
    save: ({attributes: saveAttributes}) => {
      const markup = renderToString(<div
        data-hydrate={'planet4-blocks/carousel-header'}
        data-attributes={JSON.stringify(saveAttributes)}
      >
        <CarouselHeaderFrontend {...saveAttributes} />
      </div>);
      return <RawHTML>{ markup }</RawHTML>;
    },
    deprecated: [
      carouselHeaderV1,
    ],
  });
