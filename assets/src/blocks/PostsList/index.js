const POSTS_LIST_BLOCK = 'planet4-blocks/posts-list-block';

import {InspectorControls} from '@wordpress/block-editor';
import {PanelBody} from '@wordpress/components';
import {PostSelector} from '../../block-editor/PostSelector/PostSelector';

export const registerPostsListBlock = () => {
  const {registerBlockVariation} = wp.blocks;
  const {__} = wp.i18n;

  return registerBlockVariation('core/query', {
    name: POSTS_LIST_BLOCK,
    title: 'Posts List',
    icon: 'list-view',
    description: __('Posts List is the place in Planet 4 that the latest articles, press releases and publications can be found.', 'planet4-blocks-backend'),
    category: 'planet4-blocks-beta',
    isActive: ({namespace, query}) => {
      return (
        namespace === POSTS_LIST_BLOCK &&
              query.postType === 'post'
      );
    },
    attributes: {
      namespace: POSTS_LIST_BLOCK,
      className: 'posts-list-query',
      query: {
        perPage: 3,
        pages: 0,
        offset: 0,
        postType: 'post',
        order: 'desc',
        orderBy: 'date',
        author: '',
        search: '',
        exclude: [],
        sticky: '',
        inherit: false,
        postIn: [],
      },
    },
    scope: ['inserter'],
    allowedControls: ['taxQuery'],
    innerBlocks: [
      ['core/heading', {content: __('Related Posts', 'planet4-blocks-backend')}],
      ['core/paragraph', {
        placeholder: __('Enter description', 'planet4-blocks-backend'),
        style: {
          spacing: {
            margin: {
              top: '24px',
              bottom: '36px',
            },
          },
        },
      }],
      ['core/post-template', {}, [
        ['core/columns', {}, [
          ['core/post-featured-image', {isLink: true}],
          ['core/group', {}, [
            ['core/post-terms', {
              term: 'category',
              separator: ' | ',
            }],
            ['core/post-title', {isLink: true}],
            ['core/post-excerpt'],
            ['core/group', {className: 'posts-list-meta'}, [
              ['core/post-author-name', {isLink: true}],
              ['core/post-date'],
            ]],
          ]],
        ]],
      ]],
    ],
  }
  );
};

export const withManualPostSelection = BlockEdit => props => {
  const {__} = wp.i18n;
  const {name, attributes, setAttributes} = props;
  const updateQuery = (value, query) => {
    setAttributes({query: {
      ...query,
      postIn: value && value.length > 0 ? value : [],
    }});
  };

  if (name === 'core/query' && attributes.namespace === POSTS_LIST_BLOCK) {
    const {query} = attributes;
    return (
      <>
        <BlockEdit {...props} />
        <InspectorControls>
          <PanelBody title={__('Manual override', 'planet4-blocks-backend')} initialOpen={query.postIn.length > 0}>
            <PostSelector
              label={__('CAUTION: Adding articles individually will override the automatic functionality of this block. For good user experience, please include at least three articles so that spacing and alignment of the design remains intact.', 'planet4-blocks-backend')}
              selected={query.postIn || []}
              onChange={value => updateQuery(value, query)}
              postType={query.postType || 'post'}
              placeholder={__('Select articles', 'planet4-blocks-backend')}
              maxLength={10}
              maxSuggestions={20}
            />
          </PanelBody>
        </InspectorControls>
      </>
    );
  }

  return <BlockEdit {...props} />;
};

wp.hooks.addFilter('editor.BlockEdit', 'core/query', withManualPostSelection);

