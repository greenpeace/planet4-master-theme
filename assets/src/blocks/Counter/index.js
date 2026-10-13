import metadata from './block.json';
import {CounterEditor} from './edit';
import {CounterFrontend} from './counter';
import {renderToString} from 'react-dom/server';
import {frontendRendered} from '../../functions/frontendRendered';

import './style.scss';

const {registerBlockType, unregisterBlockStyle, registerBlockStyle} = wp.blocks;
const {useBlockProps} = wp.blockEditor;
const {RawHTML} = wp.element;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <CounterEditor {...props} />
    </div>
  ),
  save: props => {
    const markup = renderToString(
      <div
        data-hydrate={metadata.name}
        data-attributes={JSON.stringify(props.attributes)}
      >
        <CounterFrontend {...props.attributes} />
      </div>
    );
    return <RawHTML>{markup}</RawHTML>;
  },
  deprecated: [
    {
      attributes: metadata.attributes,
      save: frontendRendered(metadata.name),
    },
    {
      attributes: metadata.attributes,
      save() {
        return null;
      },
    },
  ],
});

// Remove the default style since it's the same as "text only"
unregisterBlockStyle(metadata.name, 'default');

const styles = [
  {
    name: 'plain',
    label: 'Text Only',
    isDefault: true,
  },
  {
    name: 'bar',
    label: 'Progress Bar',
  },
];

// Add our custom styles
registerBlockStyle(metadata.name, styles);
