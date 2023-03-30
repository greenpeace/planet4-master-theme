import {SpreadsheetEditor} from './SpreadsheetEditorScript';
import {CSS_VARIABLES_ATTRIBUTE} from '../CssVariablesAttribute';
import {frontendRendered} from '../frontendRendered';

const BLOCK_NAME = 'planet4-blocks/spreadsheet';

export const registerSpreadsheetBlock = () => {
  const {registerBlockType} = wp.blocks;
  registerBlockType(BLOCK_NAME, {
    title: 'Spreadsheet',
    icon: 'editor-table',
    category: 'planet4-blocks',
    attributes: {
      url: {
        type: 'string',
        default: '',
      },
      color: {
        type: 'string',
        default: 'grey',
      },
    },
    deprecated: [
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
    edit: SpreadsheetEditor,
    save: frontendRendered(BLOCK_NAME),
  });
};
