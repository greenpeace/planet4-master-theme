import { Counter } from './Counter.js';

export class CounterBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { withSelect } = wp.data;

    registerBlockType( 'planet4-blocks/counter', {
      title: 'Counter',
      icon: 'dashboard',
      category: 'planet4-blocks',

      // Transform the shortcode into a Gutenberg block
      // this is used when a user clicks "Convert to blocks"
      // on the "Classic Editor" block
      transforms: {
        from: [
          {
            type: 'shortcode',
            // Shortcode tag can also be an array of shortcode aliases
            tag: 'shortcake_counter',
            attributes: {
              style: {
                type: 'string',
                shortcode: ( { named: { style = 'plain' } } ) => style,
              },
              title: {
                type: 'string',
                shortcode: ( { named: { title = '' } } ) => title,
              },
              description: {
                type: 'string',
                shortcode: ( { named: { description = '' } } ) => description,
              },
              completed: {
                type: 'integer',
                shortcode: ( { named: { completed = 0 } } ) => completed,
              },
              completed_api: {
                type: 'string',
                shortcode: ( { named: { completed_api = '' } } ) => completed_api,
              },
              target: {
                type: 'integer',
                shortcode: ( { named: { target = 0 } } ) => target,
              },
              text: {
                type: 'string',
                shortcode: ( { named: { text = '' } } ) => text,
              }
            },
          },
        ]
      },
      attributes: {
        title: {
          type: 'string',
        },
        description: {
          type: 'string',
        },
        style: {
          type: 'string',
          default: 'plain'
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
      },
      // withSelect is a "Higher Order Component", it works as
      // a Decorator, it will provide some basic API functionality
      // through `select`.
      edit: ({ isSelected, attributes, setAttributes }) => {
        function onTitleChange( value ) {
          setAttributes( { title: value } );
        }

        function onDescriptionChange( value ) {
          setAttributes( { description: value } );
        }

        function onSelectedLayoutChange( value ) {
          setAttributes( { style: value } );
        }

        function onCompletedChange( value ) {
          setAttributes( { completed: Number(value) } );
        }

        function onCompletedAPIChange( value ) {
          setAttributes( { completed_api: value } );
        }

        function onTargetChange( value ) {
          setAttributes( { target: Number(value) } );
        }

        function onTextChange( value ) {
          setAttributes( { text: value } );
        }

        // We pass down all the attributes to Covers as props using
        // the spread operator. Then we selectively add more
        // props.
        return <Counter
          { ...attributes }
          isSelected={ isSelected }
          onTitleChange={ onTitleChange }
          onDescriptionChange={ onDescriptionChange }
          onSelectedLayoutChange={ onSelectedLayoutChange }
          onCompletedChange={ onCompletedChange }
          onCompletedAPIChange={ onCompletedAPIChange }
          onTargetChange={ onTargetChange }
          onTextChange={ onTextChange }   />
      },
      save() {
        return null;
      }
    } );
  };
}

