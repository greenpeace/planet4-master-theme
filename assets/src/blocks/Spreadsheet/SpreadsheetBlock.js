import { SpreadsheetEditor } from './SpreadsheetEditorScript';
import { CSS_VARIABLES_ATTRIBUTE } from '../CssVariablesAttribute';
import { frontendRendered } from '../frontendRendered';

const {__} = wp.i18n;
const BLOCK_NAME = 'planet4-blocks/spreadsheet';

const attributes = {
  url: {
    type: 'string',
    default: '',
  },
  css_variables: CSS_VARIABLES_ATTRIBUTE,
};

export const registerSpreadsheetBlock = () => {
  const { registerBlockType } = wp.blocks;
  registerBlockType( BLOCK_NAME, {
    title: __( 'Spreadsheet', 'planet4-blocks-backend' ),
    icon: 'editor-table',
    category: 'planet4-blocks',
    attributes,
    deprecated: [
      {
        attributes,
        save() {
          return null;
        },
      }
    ],
    edit: SpreadsheetEditor,
    save: frontendRendered( BLOCK_NAME )
  });
}
