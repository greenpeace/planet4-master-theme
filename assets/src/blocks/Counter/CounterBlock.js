import { CounterEditor } from './CounterEditor';
import { frontendRendered } from '../frontendRendered';

const BLOCK_NAME = 'planet4-blocks/counter';

export class CounterBlock {
  constructor() {
    const { registerBlockType, unregisterBlockStyle, registerBlockStyle } = wp.blocks;
    const { __ } = wp.i18n;
    const attributes = {
      title: {
        type: 'string',
        default: ''
      },
      description: {
        type: 'string',
        default: ''
      },
      completed: {
        type: 'integer',
        default: ''
      },
      completed_api: {
        type: 'string',
        default: ''
      },
      target: {
        type: 'integer',
        default: ''
      },
      text: {
        type: 'string',
        default: ''
      },
      style: { // Needed to convert existing blocks
        type: 'string',
        default: ''
      }
    };

    registerBlockType( BLOCK_NAME, {
      title: __( 'Counter', 'planet4-blocks-backend' ),
      icon: 'dashboard',
      category: 'planet4-blocks',
      attributes,
      supports: {
        html: false, // Disable "Edit as HTMl" block option.
      },
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
    });

    // Remove the default style since it's the same as "text only"
    unregisterBlockStyle(BLOCK_NAME, 'default');

    const styles = [
        {
          name: 'plain',
          label: 'Text Only',
          isDefault: true
        },
        {
          name: 'bar',
          label: 'Progress Bar'
        },
        {
          name: 'arc',
          label: 'Progress Dial'
        },

      ]

    if (window.p4ge_vars.features.feature_engaging_networks) {
      styles.push({
        name: 'en-forms-bar',
        label: __('Progress Bar inside EN Form', 'planet4-blocks-backend')
      });
    }
    // Add our custom styles
    registerBlockStyle( BLOCK_NAME, styles);
  };
}
