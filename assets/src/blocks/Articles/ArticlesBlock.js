import { ArticlesEditor } from './ArticlesEditor';
import { frontendRendered } from '../frontendRendered';
import { withSelect } from '@wordpress/data';

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
      edit: withSelect(select => {
        const postType = select('core/editor').getCurrentPostType();
        const postId = select('core/editor').getCurrentPostId();
        return { postType, postId };
      })(({ isSelected, attributes, setAttributes, postType, postId }) => {
        return <ArticlesEditor
          attributes={attributes}
          postType={postType}
          setAttributes={setAttributes}
          isSelected={isSelected}
          postId={postId}
        />
      }),
      save: frontendRendered(BLOCK_NAME)
    });
  };
}

