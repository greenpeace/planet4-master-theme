import { Spreadsheet } from './Spreadsheet';
import { CSS_VARIABLES_ATTRIBUTE } from '../CssVariablesAttribute';

export class SpreadsheetBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;

    registerBlockType( 'planet4-blocks/spreadsheet', {
      title: __( 'Spreadsheet', 'planet4-blocks-backend' ),
      icon: 'editor-table',
      category: 'planet4-blocks-beta',
      attributes: {
        url: {
          type: 'string',
          default: '',
        },
        css_variables: CSS_VARIABLES_ATTRIBUTE,
      },
      edit: ( { isSelected, attributes, setAttributes } ) => {
        return <Spreadsheet
          attributes={attributes}
          setAttributes={setAttributes}
          isSelected={ isSelected }
        />
      },
      save() {
        return null;
      }
    } );
  };
}
