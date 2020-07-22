import { ArticlesEditor } from './ArticlesEditor';
import { frontendRendered } from '../frontendRendered';
import { useSelect } from '@wordpress/data';
import { useArticlesFetch } from './useArticlesFetch';

const BLOCK_NAME = 'planet4-blocks/articles';

export class ArticlesBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;

    // This attributes definition mimics the one in the PHP side.
    const attributes = {
      article_heading: {
        type: 'string',
        default: __('Latest Articles', 'planet4-blocks')
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
        type: 'string',
        default: __('Load More', 'planet4-blocks')
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
      }
    };

    registerBlockType(BLOCK_NAME, {
      title: __('Articles', 'planet4-blocks-backend'),
      icon: 'excerpt-view',
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
      edit: ({isSelected, attributes, setAttributes}) => {

        const {postType, postId} = useSelect((select) => ({
            postType: select('core/editor').getCurrentPostType(),
            postId: select('core/editor').getCurrentPostId()
          })
          , []);

        const {posts, totalPosts} = useArticlesFetch(attributes, postType, postId);

        return <ArticlesEditor
          attributes={ attributes }
          setAttributes={ setAttributes }
          isSelected={ isSelected }
          postType={ postType }
          postId={ postId }
          posts={ posts }
          totalPosts={ totalPosts }
        />;
      },
      save: frontendRendered(BLOCK_NAME)
    });
  };
}

