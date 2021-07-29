import { Covers } from './Covers.js';

export class CoversBlock {
    constructor() {
      const { registerBlockType } = wp.blocks;
      const { withSelect } = wp.data;

      registerBlockType( 'planet4-blocks/covers', {
        title: 'Covers',
        icon: 'slides',
        category: 'planet4-blocks',

        // Transform the shortcode into a Gutenberg block
        // this is used when a user clicks "Convert to blocks"
        // on the "Classic Editor" block
        transforms: {
          from: [
            {
              type: 'shortcode',
              // Shortcode tag can also be an array of shortcode aliases
              tag: 'shortcake_newcovers',
              attributes: {
                cover_type: {
                  type: 'string',
                  // This `shortcode` definition will be used as a callback,
                  // it is a function which expects an object with at least
                  // a `named` key with `cover_type` property whose default value is 1.
                  // See: https://simonsmith.io/destructuring-objects-as-function-parameters-in-es6
                  shortcode: ({named: {cover_type = '1'}}) => cover_type,
                },
                title: {
                  type: 'string',
                  shortcode: ({named: {title = ''}}) => title,
                },
                description: {
                  type: 'string',
                  shortcode: ({named: {description = ''}}) => description,
                },
                covers_view: {
                  type: 'string',
                  shortcode: ({named: {covers_view}}) => {
                    switch (covers_view) {
                      case '0':
                        return '1';
                      case '3':
                        return '2';
                      case '1':
                        return '3';
                    }
                    return '1';
                  },
                },
                tags: {
                  type: 'array',
                  shortcode: ({named: {tags = ''}}) => tags.split(',').map(tag => Number(tag)).filter(tag => tag > 0),
                },
                post_types: {
                  type: 'array',
                  shortcode: ({named: {post_types = ''}}) => post_types.split(',').map(post_type => Number(post_type)).filter(post_type => post_type > 0),
                },
                posts: {
                  type: 'array',
                  shortcode: ({named: {posts = ''}}) => posts.split(',').map(post => Number(post)).filter(post => post > 0),
                },
              },
            },
          ]
        },
        // This attributes definition mimics the one in the PHP side.
        attributes: {
          title: {
            type: 'string',
          },
          description: {
            type: 'string',
          },
          tags: {
            type: 'array',
            default: []
          },
          posts: {
            type: 'array',
            default: []
          },
          post_types: {
            type: 'array',
            default: []
          },
          covers_view: {
            type: 'string',
            default: '1'
          },
          cover_type: {
            type: 'string',
          }
        },
        // withSelect is a "Higher Order Component", it works as
        // a Decorator, it will provide some basic API functionality
        // through `select`.
        edit: withSelect( ( select ) => {
          const currentPostType = select('core/editor').getCurrentPostType();

          return {
            currentPostType,
          };
        } )( ( {
          currentPostType,
          isSelected,
          attributes,
          setAttributes
        } ) => {

            if ( !currentPostType ) {
                return "Populating block's fields...";
            }

            return <Covers
              attributes={ attributes}
              setAttributes={ setAttributes}
              isSelected={ isSelected }
              currentPostType={ currentPostType }
            />
        } ),
        save() {
          return null;
        }
      } );
    };
}

