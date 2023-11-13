export const BLOCK_NAME = 'planet4-blocks/actions-list';

// Register the ActionsList block.
export const registerActionsList = () => {
  const {registerBlockVariation} = wp.blocks;
  const {__} = wp.i18n;

  // We need to make sure that we have access to the Planet 4 options,
  // to know which post type to use in the query depending on the new IA.
  const interval = setInterval(() => {
    if (typeof window.p4ge_vars === 'undefined') {
      return;
    }

    clearInterval(interval);
    const IS_NEW_IA = window.p4ge_vars.planet4_options.new_ia === 'on';
    const ACT_PAGE = window.p4ge_vars.planet4_options.act_page || -1;

    const queryPostType = IS_NEW_IA ? 'p4_action' : 'page';

    return registerBlockVariation('core/query', {
      name: BLOCK_NAME,
      title: 'Actions List',
      description: __('A list of possible actions', 'planet4-blocks-backend'),
      icon: 'list-view',
      isActive: ({namespace, query}) => namespace === BLOCK_NAME && query.postType === queryPostType,
      scope: ['inserter'],
      allowedControls: ['taxQuery'],
      category: 'planet4-blocks-beta',
      attributes: {
        className: 'actions-list',
        namespace: BLOCK_NAME,
        query: {
          pages: 0,
          offset: 0,
          order: 'desc',
          orderBy: 'date',
          author: '',
          search: '',
          exclude: [],
          sticky: '',
          inherit: false,
          perPage: 3,
          postType: queryPostType,
          ...!IS_NEW_IA && {postParent: ACT_PAGE},
        },
        displayLayout: {
          type: 'flex',
          columns: 3,
        },
      },
      innerBlocks: [
        ['core/heading', {placeholder: __('Enter title', 'planet4-blocks-backend')}],
        ['core/paragraph', {
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
        ['core/post-template', {}, [
          ['core/post-featured-image', {isLink: true, linkTarget: '_blank'}],
          ['core/group', {}, [
            ['core/post-terms', {term: 'post_tag', separator: ' '}],
            ['core/post-title', {isLink: true, linkTarget: '_blank'}],
            ['core/post-excerpt'],
          ]],
          ['core/group', {layout: {type: 'flex', justifyContent: 'right'}}, [
            ['core/read-more', {
              className: 'btn btn-small btn-primary',
              content: __('Take Action', 'planet4-blocks-backend'),
              linkTarget: '_blank',
            }],
          ]],
        ]],
      ],
    });
  }, 1000);
};

