import { Spreadsheet } from './Spreadsheet';

export class SpreadsheetBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;

    const color_name_map =
      { C9E7FA: 'blue',
        D0FAC9: 'green',
        DCDCDC: 'grey' };

    registerBlockType( 'planet4-blocks/spreadsheet', {
      title: __( 'Spreadsheet', 'planet4-blocks-backend' ),
      icon: 'editor-table',
      category: 'planet4-blocks-beta',
      attributes: {
        url: {
          type: 'string',
          default: '',
        },
        color: {
          type: 'string',
          default: '#DCDCDC'
        },
        color_name: {
          type: 'string',
          default: 'grey'
        }
      },
      edit: ( { isSelected, attributes, setAttributes } ) => {
        function onUrlChange( value ) {
          setAttributes( { url: value } );
        }

        function onTableColorChange( colors, value ) {
          setAttributes( {color: value} );
        }

        return <Spreadsheet
          { ...attributes }
          isSelected={ isSelected }
          onUrlChange={ onUrlChange }
          onTableColorChange={ onTableColorChange }
        />
      },
      save() {
        return null;
      }
    } );
  };
}
