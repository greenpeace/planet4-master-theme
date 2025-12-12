import {TAX_BREADCRUMB_BLOCK_NAME} from '../../block-editor/setupTaxonomyBreadcrumbBlock';

export const POSTS_LIST_BLOCK_NAME = 'planet4-blocks/posts-list';

export const POSTS_LISTS_LAYOUT_TYPES = [
  {label: 'List', value: 'default', columnCount: 3},
  {label: 'Grid', value: 'grid', columnCount: 4},
  {label: 'Carousel', value: 'flex', columnCount: 8},
];
export const LISTS_BREADCRUMBS = [
  {label: 'Category', value: 'category'},
  {label: 'Post Type', value: 'p4-page-type'},
];

const {__} = wp.i18n;

const newsPageLink = window.p4_vars.news_page_link;

const seeAllLink = ['core/navigation-link', {...!newsPageLink ? {className: 'd-none'} : {
  url: newsPageLink,
  label: __('See all posts', 'planet4-master-theme'),
  className: 'see-all-link',
}}];

export const carouselButtons = ['core/buttons', {
  className: 'carousel-controls',
  lock: {move: true},
  layout: {type: 'flex', justifyContent: 'space-between', orientation: 'horizontal', flexWrap: 'nowrap'},
}, [
  ['core/button',
    {
      className: 'carousel-control-prev',
      text: __('Previous Carousel Slide', 'planet4-master-theme-backend'),
      tagName: 'button',
      type: 'button',
    },
  ],
  ['core/button',
    {
      className: 'carousel-control-next',
      text: __('Next Carousel Slide', 'planet4-master-theme-backend'),
      tagName: 'button',
      type: 'button',
    },
  ],
]];

export const POSTS_LIST_BLOCK_ATTRIBUTES = {
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
    block_name: POSTS_LIST_BLOCK_NAME,
  },
  layout: {
    type: 'default',
    columnCount: 3,
  },
};

export const getPostListBlockTemplate = (title = __('Related Posts', 'planet4-master-theme-backend')) => ([
  ['core/group', {layout: {type: 'flex', justifyContent: 'space-between'}}, [
    ['core/heading', {lock: {move: true}, content: title}],
    seeAllLink,
  ]],
  ['core/paragraph', {
    lock: {move: true},
    placeholder: __('Enter description', 'planet4-master-theme-backend'),
    style: {
      spacing: {
        margin: {
          top: '24px',
          bottom: '32px',
        },
      },
    },
  }],
  ['core/query-no-results', {}, [
    ['core/paragraph', {content: __('No posts found. (This default text can be edited)', 'planet4-master-theme-backend')}],
  ]],
  ['core/post-template', {lock: {move: true, remove: true}}, [
    ['core/columns', {}, [
      ['core/post-featured-image', {isLink: true}],
      ['core/group', {}, [
        ['core/group', {layout: {type: 'flex'}}, [
          [TAX_BREADCRUMB_BLOCK_NAME, {
            taxonomy: LISTS_BREADCRUMBS[0].value,
            post_type: 'posts',
          }],
          ['core/post-terms', {
            term: 'post_tag',
            separator: ' ',
          }],
        ]],
        ['core/post-title', {isLink: true, level: 3}],
        ['core/post-excerpt'],
        ['core/group', {className: 'posts-list-meta'}, [
          ['core/post-author-name', {isLink: true}],
          ['core/post-date'],
        ]],
      ]],
    ]],
  ]],
  carouselButtons,
  seeAllLink,
]);

export const registerPostsListBlock = () => {
  const {registerBlockVariation} = wp.blocks;

  return registerBlockVariation('core/query', {
    name: POSTS_LIST_BLOCK_NAME,
    title: 'Posts List',
    icon: 'list-view',
    description: __('Insert a list or grid of the latest articles, press releases, and/or publications, organized by publication date. ', 'planet4-master-theme-backend'),
    category: 'planet4-blocks',
    scope: ['inserter'],
    allowedControls: ['taxQuery', 'pages', 'offset'],
    isActive: ({namespace, query}) => namespace === POSTS_LIST_BLOCK_NAME && query.postType === 'post',
    attributes: POSTS_LIST_BLOCK_ATTRIBUTES,
    innerBlocks: getPostListBlockTemplate(),
  });
};
