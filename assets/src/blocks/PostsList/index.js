const POSTS_LIST_BLOCK = 'planet4-blocks/posts-list-block';

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
