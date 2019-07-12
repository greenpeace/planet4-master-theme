import { CoversIcon } from './CoversIcon.js';
import { Covers } from './Covers.js';

export class CoversBlock {
    constructor() {
      const { registerBlockType } = wp.blocks;
      const { withSelect } = wp.data;

      registerBlockType( 'planet4-blocks/covers', {
        title: 'Covers',
        icon: CoversIcon,
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
                  type: 'integer',
                  // This `shortcode` definition will be used as a callback,
                  // it is a function which expects an object with at least
                  // a `named` key with `cover_type` property whose default value is 1.
                  // See: https://simonsmith.io/destructuring-objects-as-function-parameters-in-es6
                  shortcode: ( { named: { cover_type = '1' } } ) => cover_type,
                },
                title: {
                  type: 'string',
                  shortcode: ( { named: { title = '' } } ) => title,
                },
                description: {
                  type: 'string',
                  shortcode: ( { named: { description = '' } } ) => description,
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
            type: 'integer',
            default: 1
          }
        },
        // withSelect is a "Higher Order Component", it works as
        // a Decorator, it will provide some basic API functionality
        // through `select`.
        edit: withSelect( ( select ) => {
          const tagsTaxonomy = 'post_tag';
          const postTypesTaxonomy = 'p4-page-type';
          const args = {
            hide_empty: false,
          };
          const { getEntityRecords } = select( 'core' );

          // We should probably wrap all these in a single call,
          // or maybe use our own way of retrieving data from the
          // API, I don't know how this scales.
          const tagsList = getEntityRecords( 'taxonomy', tagsTaxonomy, args );
          const postTypesList = getEntityRecords( 'taxonomy', postTypesTaxonomy );
          const posts = getEntityRecords( 'postType', 'post' );

          return {
            postTypesList,
            tagsList,
            posts
          };
        } )( ( {
          postTypesList,
          tagsList,
          posts,
          isSelected,
          attributes,
          setAttributes
        } ) => {

            if ( !tagsList || !postTypesList || !posts ) {
                return "Populating block's fields...";
            }

            // TO-DO: Check for posts types and posts too...
            if ( !tagsList && !tagsList.length === 0 ) {
                return "No tags...";
            }

            // These methods are passed down to the
            // Covers component, they update the corresponding attribute.

            function onRowsChange( value ) {
              setAttributes( { covers_view: value } );
            }

            function onTitleChange( value ) {
              setAttributes( { title: value } );
            }

            function onDescriptionChange( value ) {
              setAttributes( { description: value } );
            }

            function onSelectedTagsChange( tagIds ) {
              setAttributes( { tags: tagIds } );
            }

            function onSelectedPostsChange( value ) {
              setAttributes( { selectedPosts: value.tokens } );
            }

            function onSelectedPostTypesChange( postTypeIds ) {
              setAttributes( { post_types: postTypeIds } );
            }

            function onSelectedLayoutChange( value ) {
              setAttributes( { cover_type: Number(value) } );
            }

            // We pass down all the attributes to Covers as props using
            // the spread operator. Then we selectively add more
            // props.
            return <Covers
              { ...attributes }
              isSelected={ isSelected }
              tagsList={ tagsList }
              postTypesList={ postTypesList }
              posts={ posts }
              onSelectedTagsChange={ onSelectedTagsChange }
              onSelectedLayoutChange={ onSelectedLayoutChange }
              onTitleChange={ onTitleChange }
              onDescriptionChange={ onDescriptionChange }
              onSelectedPostsChange={ onSelectedPostsChange }
              onSelectedPostTypesChange={ onSelectedPostTypesChange }
              onRowsChange={ onRowsChange } />
        } ),
        save() {
          return null;
        }
      } );
    };
}

