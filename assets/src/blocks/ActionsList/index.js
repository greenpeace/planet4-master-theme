export const ACTIONS_LIST_BLOCK_NAME = 'planet4-blocks/actions-list';
export const ACTIONS_LIST_LAYOUT_TYPES = [
  {label: 'Grid', value: 'grid', columnCount: 3},
  {label: 'Carousel', value: 'flex', columnCount: 6},
];

// Register the ActionsList block.
export const registerActionsListBlock = () => {
  const {registerBlockVariation} = wp.blocks;
  const {__} = wp.i18n;

  const IS_NEW_IA = window.p4_vars.options.new_ia === 'on';
  const ACT_PAGE = window.p4_vars.options.take_action_page || -1;

  const queryPostType = IS_NEW_IA ? 'p4_action' : 'page';

  registerBlockVariation('core/query', {
    title: 'Actions List',
    description: __('A list of possible actions', 'planet4-blocks-backend'),
    icon: 'list-view',
    scope: ['inserter'],
    allowedControls: ['taxQuery'],
    category: 'planet4-blocks-beta',
    isActive: ({namespace, query}) => namespace === ACTIONS_LIST_BLOCK_NAME && query.postType === queryPostType,
    attributes: {
      namespace: ACTIONS_LIST_BLOCK_NAME,
      className: 'actions-list p4-query-loop is-custom-layout-grid',
      query: {
        pages: 0,
        perPage: 3,
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
        ...!IS_NEW_IA && {postParent: ACT_PAGE},
      },
      layout: {
        type: 'grid',
        columnCount: 3,
      },
    },
    innerBlocks: [
      ['core/heading', {lock: {move: true}, placeholder: __('Enter title', 'planet4-blocks-backend')}],
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
      ['core/post-template', {lock: {move: true, remove: true}}, [
        ['core/post-featured-image', {isLink: true}],
        ['core/group', {}, [
          ['core/post-terms', {term: 'post_tag', separator: ' '}],
          ['core/post-title', {isLink: true}],
          ['core/post-excerpt'],
        ]],
        ['core/group', {className: 'read-more-nav'}, [
          ['core/read-more', {
            className: 'btn btn-small btn-primary',
            content: __('Take Action', 'planet4-blocks-backend'),
          }],
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
    ],
  });
};
