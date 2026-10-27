import metadata from './block.json';
import {TimelineFrontend} from './timeline';
import {renderToString} from 'react-dom/server';

const {RawHTML} = wp.element;

export const save = props => {
  const markup = renderToString(
    <div
      data-hydrate={metadata.name}
      data-attributes={JSON.stringify(props.attributes)}
    >
      <TimelineFrontend {...props} />
    </div>
  );
  return <RawHTML>{markup}</RawHTML>;
};
