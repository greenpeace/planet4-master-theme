import {SpreadsheetEditor} from './SpreadsheetEditor';
import {frontendRendered} from '../../functions/frontendRendered';
import {SpreadsheetFrontend} from './SpreadsheetFrontend';

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

export const registerSpreadsheetBlock = () => {
  const {registerBlockType} = wp.blocks;
  const {RawHTML, renderToString} = wp.element;

  registerBlockType(BLOCK_NAME, {
    title: 'Spreadsheet',
    icon: 'editor-table',
    category: 'planet4-blocks',
    attributes,
    edit: SpreadsheetEditor,
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
};
