export const POSTS_LIST_BLOCK_NAME = 'planet4-blocks/posts-list';
export const POSTS_LISTS_LAYOUT_TYPES = [
  {label: 'List', value: 'default', columnCount: 3},
  {label: 'Grid', value: 'grid', columnCount: 4},
  {label: 'Carousel', value: 'flex', columnCount: 8},
];

export const registerPostsListBlock = () => {
  const {registerBlockVariation} = wp.blocks;
  const {__} = wp.i18n;

  const newsPageLink = window.p4_vars.news_page_link;

  const seeAllLink = ['core/navigation-link', {...!newsPageLink ? {className: 'd-none'} : {
    url: newsPageLink,
    label: __('See all stories', 'planet4-blocks-backend'),
    className: 'see-all-link',
  }}];

  return registerBlockVariation('core/query', {
    name: POSTS_LIST_BLOCK_NAME,
    title: 'Posts List',
    icon: 'list-view',
    description: __('Insert a list or grid of the latest articles, press releases, and/or publications, organized by publication date. ', 'planet4-blocks-backend'),
    category: 'planet4-blocks-beta',
    scope: ['inserter'],
    allowedControls: ['taxQuery'],
    isActive: ({namespace, query}) => namespace === POSTS_LIST_BLOCK_NAME && query.postType === 'post',
    attributes: {
      namespace: POSTS_LIST_BLOCK_NAME,
      className: 'posts-list p4-query-loop is-custom-layout-list',
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
        hasPassword: false,
      },
      layout: {
        type: 'default',
        columnCount: 3,
      },
    },
    innerBlocks: [
      ['core/group', {layout: {type: 'flex', justifyContent: 'space-between'}}, [
        ['core/heading', {lock: {move: true}, content: __('Related Posts', 'planet4-blocks-backend')}],
        seeAllLink,
      ]],
      ['core/paragraph', {
        lock: {move: true},
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
      ['core/query-no-results', {}, [
        ['core/paragraph', {content: __('No posts found. (This default text can be edited)', 'planet4-blocks-backend')}],
      ]],
      ['core/post-template', {lock: {move: true, remove: true}}, [
        ['core/columns', {}, [
          ['core/post-featured-image', {isLink: true}],
          ['core/group', {}, [
            ['core/group', {layout: {type: 'flex'}}, [
              ['core/post-terms', {
                term: 'category',
                separator: ' | ',
              }],
              ['core/post-terms', {
                term: 'post_tag',
                separator: ' ',
              }],
            ]],
            ['core/post-title', {isLink: true}],
            ['core/post-excerpt'],
            ['core/group', {className: 'posts-list-meta'}, [
              ['core/post-author-name', {isLink: true}],
              ['core/post-date'],
            ]],
          ]],
        ]],
      ]],
      ['core/buttons', {
        className: 'carousel-controls',
        lock: {move: true},
        layout: {type: 'flex', justifyContent: 'space-between', orientation: 'horizontal', flexWrap: 'nowrap'},
      }, [
        ['core/button', {className: 'carousel-control-prev', text: __('Prev', 'planet4-blocks-backend')}],
        ['core/button', {className: 'carousel-control-next', text: __('Next', 'planet4-blocks-backend')}],
      ]],
      seeAllLink,
    ],
  });
};
