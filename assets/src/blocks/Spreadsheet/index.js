import metadata from './block.json';
import {SpreadsheetEditor} from './edit';
import {renderToString} from 'react-dom/server';
import {SpreadsheetFrontend} from './spreadsheet';
import {frontendRendered} from '../../functions/frontendRendered';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;
const {RawHTML} = wp.element;

const BLOCK_NAME = 'planet4-blocks/spreadsheet';

const attributes = {
  url: {
    type: 'string',
    default: '',
  },
  color: {
    type: 'string',
    default: 'grey',
  },
};

const CSS_VARIABLES_ATTRIBUTE = {
  type: 'object',
  default: {},
};

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <SpreadsheetEditor {...props} />
    </div>
  ),
  save: props => {
    const markup = renderToString(<div
      data-hydrate={BLOCK_NAME}
      data-attributes={JSON.stringify(props.attributes)}
    >
      <SpreadsheetFrontend {...props.attributes} />
    </div>);
    return <RawHTML>{markup}</RawHTML>;
  },
  deprecated: [
    {
      attributes,
      save: frontendRendered(BLOCK_NAME),
    },
    {
      attributes: {
        url: {
          type: 'string',
          default: '',
        },
        css_variables: CSS_VARIABLES_ATTRIBUTE,
      },
      save() {
        return null;
      },
    },
  ],
});
