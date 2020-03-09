import {Articles} from './Articles.js';

export class ArticlesBlock {
  constructor() {
    const {registerBlockType} = wp.blocks;
    const {withSelect} = wp.data;

    registerBlockType('planet4-blocks/articles', {
      title: 'Articles',
      icon: 'excerpt-view',
      category: 'planet4-blocks',

      // Transform the shortcode into a Gutenberg block
      // this is used when a user clicks "Convert to blocks"
      // on the "Classic Editor" block
      transforms: {
        from: [
          {
            type: 'shortcode',
            // Shortcode tag can also be an array of shortcode aliases
            tag: 'shortcake_articles',
            attributes: {
              article_heading: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.article_heading;
                }
              },
              articles_description: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.articles_description;
                }
              },
              article_count: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.article_count;
                }
              },
              read_more_text: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.read_more_text;
                }
              },
              read_more_link: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.read_more_link;
                }
              },
              button_link_new_tab: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.button_link_new_tab;
                }
              },
              ignore_categories: {
                type: 'boolean',
                shortcode: function (attributes) {
                  return attributes.named.ignore_categories;
                }
              },
              tags: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.tags ? attributes.named.tags.split(',') : [];
                }
              },
              post_types: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.tags ? attributes.named.post_types.split(',') : [];
                }
              },
              posts: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.tags ? attributes.named.posts.split(',') : [];
                }
              },
              exclude_post_id: {
                type: 'integer',
                shortcode: function (attributes) {
                  return Number(attributes.named.exclude_post_id);
                }
              },
            },
          },
        ]
      },
      // This attributes definition mimics the one in the PHP side.
      attributes: {
        article_heading: {
          type: 'string',
        },
        articles_description: {
          type: 'string',
        },
        article_count: {
          type: 'integer',
          default: 3
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
        read_more_text: {
          type: 'string'
        },
        read_more_link: {
          type: 'string',
          default: ''
        },
        button_link_new_tab: {
          type: 'boolean',
          default: false
        },
        ignore_categories: {
          type: 'boolean',
          default: false
        },
        exclude_post_id: {
          type: 'integer',
        },
      },
      edit: withSelect((select) => {
        const postTypesTaxonomy = 'p4-page-type';
        const {getEntityRecords} = select('core');

        // We should probably wrap all these in a single call,
        // or maybe use our own way of retrieving data from the
        // API, I don't know how this scales.
        const postTypesList = getEntityRecords('taxonomy', postTypesTaxonomy);

        return {
          postTypesList,
        };
      })(({
            postTypesList,
            isSelected,
            attributes,
            setAttributes
          }) => {

        if (!postTypesList) {
          return "Populating block's fields...";
        }

        // TO-DO: Check for posts types and posts too...
        if ( postTypesList && postTypesList.length === 0) {
          return "Populating block's fields...";
        }

        return <Articles
          attributes={attributes}
          setAttributes={setAttributes}
          isSelected={isSelected}
          postTypesList={postTypesList}
        />
      }),
      save() {
        return null;
      }
    });
  };
}

