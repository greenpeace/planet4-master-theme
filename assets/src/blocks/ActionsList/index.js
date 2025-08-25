import {LISTS_BREADCRUMBS, carouselButtons} from '../PostsList';
import {TAX_BREADCRUMB_BLOCK_NAME} from '../../block-editor/setupTaxonomyBreadcrumbBlock';

export const ACTIONS_LIST_BLOCK_NAME = 'planet4-blocks/actions-list';

export const ACTIONS_LIST_LAYOUT_TYPES = [
  {label: 'Grid', value: 'grid', columnCount: 3, maxPosts: 24},
  {label: 'Carousel', value: 'flex', columnCount: 6},
];

const {registerBlockVariation} = wp.blocks;
const {__} = wp.i18n;

const IS_NEW_IA = window.p4_vars.options.new_ia;
const ACT_PAGE = window.p4_vars.options.take_action_page || -1;

const queryPostType = IS_NEW_IA ? 'p4_action' : 'page';

export const ACTIONS_LIST_BLOCK_ATTRIBUTES = {
  namespace: ACTIONS_LIST_BLOCK_NAME,
  className: 'actions-list p4-query-loop is-custom-layout-grid',
  query: {
    pages: 0,
    perPage: 24,
    offset: 0,
    order: 'desc',
    orderBy: 'date',
    author: '',
    search: '',
    exclude: [],
    sticky: '',
    inherit: false,
    postType: queryPostType,
    postIn: [],
    hasPassword: false,
    block_name: ACTIONS_LIST_BLOCK_NAME,
    ...!IS_NEW_IA && {postParent: ACT_PAGE},
  },
  layout: {
    type: 'grid',
    columnCount: 3,
  },
};

export const getActionsListBlockTemplate = (title = __('', 'planet4-blocks-backend')) => ([
  ['core/heading', {lock: {move: true}, content: title, placeholder: __('Enter title', 'planet4-blocks-backend')}],
  ['core/paragraph', {
    lock: {move: true},
    placeholder: __('Enter description', 'planet4-blocks-backend'),
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
    ['core/paragraph', {content: __('No posts found. (This default text can be edited)', 'planet4-blocks-backend')}],
  ]],
  ['core/post-template', {lock: {move: true, remove: true}}, [
    ['core/post-featured-image', {isLink: true}],
    ['core/group', {}, [
      [TAX_BREADCRUMB_BLOCK_NAME, {
        taxonomy: LISTS_BREADCRUMBS[0].value,
        post_type: queryPostType,
      }],
      ['core/post-title', {isLink: true, level: 3}],
      ['core/post-excerpt'],
    ]],
    ['core/group', {className: 'read-more-nav'}, [
      ['planet4-blocks/action-button-text'],
    ]],
  ]],
  carouselButtons,
  ['core/buttons', {
    className: 'load-more-actions-container',
    layout: {type: 'flex', justifyContent: 'center'},
  }, [
    ['core/button',
      {
        className: 'is-style-secondary',
        text: __('Load more', 'planet4-blocks'),
        tagName: 'button',
      },
    ],
  ]],
]);

// Register the ActionsList block.
export const registerActionsListBlock = () => {
  registerBlockVariation('core/query', {
    name: ACTIONS_LIST_BLOCK_NAME,
    title: 'Actions List',
    description: __('Integrate images and text cards to automatically display tags, take action pages, or Posts in a three or four column layout displayed in a grid or carousel.', 'planet4-blocks-backend'),
    icon: 'list-view',
    scope: ['inserter'],
    allowedControls: ['taxQuery', 'pages', 'offset'],
    category: 'planet4-blocks',
    isActive: ({namespace, query}) => namespace === ACTIONS_LIST_BLOCK_NAME && query.postType === queryPostType,
    attributes: ACTIONS_LIST_BLOCK_ATTRIBUTES,
    innerBlocks: getActionsListBlockTemplate(),
  });
};
