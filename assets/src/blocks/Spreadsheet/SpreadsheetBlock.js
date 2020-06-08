import { SpreadsheetEditor } from './SpreadsheetEditor';
import { CSS_VARIABLES_ATTRIBUTE } from '../CssVariablesAttribute';
import { frontendRendered } from '../frontendRendered';

const BLOCK_NAME = 'planet4-blocks/spreadsheet';

export class SpreadsheetBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;
    const attributes = {
      url: {
        type: 'string',
        default: '',
      },
      css_variables: CSS_VARIABLES_ATTRIBUTE,
    };

    registerBlockType( BLOCK_NAME, {
      title: __( 'Spreadsheet', 'planet4-blocks-backend' ),
      icon: 'editor-table',
      category: 'planet4-blocks-beta',
      attributes,
      deprecated: [
        {
          attributes,
          save() {
            return null;
          },
        }
      ],
      edit: ( { isSelected, attributes, setAttributes } ) => {
        return <SpreadsheetEditor
          attributes={attributes}
          setAttributes={setAttributes}
          isSelected={ isSelected }
        />
      },
      save: frontendRendered( BLOCK_NAME )
    } );
  };
}
