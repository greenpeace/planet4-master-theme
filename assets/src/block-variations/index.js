const {registerBlockVariation} = wp.blocks;
const {__} = wp.i18n;

/*
 This file will register general block variations. In case of register a very
 specific block variation, such as PostsList or ActionsList ,you can create an isolated file.
*/

export const registerBlockVariations = () => {
  // Group Stretched Link variation
  registerBlockVariation('core/group', {
    name: 'group-stretched-link',
    title: __('Stretched Link', 'planet4-master-theme-backend'),
    description: __('Make the entire block contents clickable, using the first link inside.', 'planet4-master-theme-backend'),
    attributes: {className: 'group-stretched-link'},
    scope: ['inserter', 'transform'],
    isActive: blockAttributes => {
      return blockAttributes.className === 'group-stretched-link';
    },
    icon: 'admin-links',
  });
};
