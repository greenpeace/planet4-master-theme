import metadata from './block.json';
import {CarouselHeaderFrontend} from './carousel-header';
import {renderToString} from 'react-dom/server';

const {RawHTML} = wp.element;

export const save = ({attributes}) => {
  const markup = renderToString(
    <div
      data-hydrate={metadata.name}
      data-attributes={JSON.stringify(attributes)}
    >
      <CarouselHeaderFrontend {...attributes} />
    </div>
  );
  return <RawHTML>{markup}</RawHTML>;
};
