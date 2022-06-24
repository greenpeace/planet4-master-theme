const { registerBlockType } = wp.blocks;

export const registerActionPageDummyBlock = () => {
  registerBlockType('planet4-blocks/action-page-dummy', {
    title: 'Action Page Dummy',
    category: 'planet4-blocks',
    supports: {
      inserter: false,
    },
    edit: () => null,
    save: () => null,
  });
};
