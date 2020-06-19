import { CounterEditor } from './CounterEditor';
import { frontendRendered } from '../frontendRendered';

const BLOCK_NAME = 'planet4-blocks/counter';

export class CounterBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;
    const attributes = {
      title: {
        type: 'string',
      },
      description: {
        type: 'string',
      },
      className: {
        type: 'string',
        default: 'is-style-plain'
      },
      completed: {
        type: 'integer',
      },
      completed_api: {
        type: 'string',
      },
      target: {
        type: 'integer',
      },
      text: {
        type: 'string',
      }
    };

    registerBlockType( BLOCK_NAME, {
      title: __( 'Counter', 'planet4-blocks-backend' ),
      icon: 'dashboard',
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
      edit: ( { isSelected, attributes, setAttributes } ) => {
        return <CounterEditor
          attributes={attributes}
          setAttributes={setAttributes}
          isSelected={ isSelected }
        />
      },
      save: frontendRendered( BLOCK_NAME )
    } );
  };
}