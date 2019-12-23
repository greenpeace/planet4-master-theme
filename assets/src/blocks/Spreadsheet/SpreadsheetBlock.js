import { Spreadsheet } from './Spreadsheet';

export class SpreadsheetBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;

    registerBlockType( 'planet4-blocks/spreadsheet', {
      title: __( 'Spreadsheet', 'planet4-blocks-backend' ),
      icon: 'editor-table',
      category: 'planet4-blocks',
      attributes: {
        url: {
          type: 'string',
          default: '',
        },
      },
      edit: ( { isSelected, attributes, setAttributes } ) => {
        function onUrlChange( value ) {
          setAttributes( { url: value } );
        }

        return <Spreadsheet
          { ...attributes }
          isSelected={ isSelected }
          onUrlChange={ onUrlChange }
        />
      },
      save() {
        return null;
      }
    } );
  };
}
