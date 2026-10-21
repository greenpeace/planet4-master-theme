import metadata from './block.json';
import {CarouselHeaderFrontend} from './carousel-header';
import {renderToString} from 'react-dom/server';

const {RawHTML} = wp.element;

// PHP decodes block.json as associative arrays, so an empty `focal_points` object can come back as an empty array.
// Keep it as an object so the saved markup stays the same and the block remains valid.
const normalizeSlides = (slides = []) => slides.map(slide => (
  Array.isArray(slide.focal_points) ? {...slide, focal_points: {}} : slide
));

export const save = ({attributes}) => {
  const saveAttributes = {...attributes, slides: normalizeSlides(attributes.slides)};

  const markup = renderToString(
    <div
      data-hydrate={metadata.name}
      data-attributes={JSON.stringify(saveAttributes)}
    >
      <CarouselHeaderFrontend {...saveAttributes} />
    </div>
  );
  return <RawHTML>{markup}</RawHTML>;
};
