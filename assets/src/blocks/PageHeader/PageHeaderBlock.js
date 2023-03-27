const {registerBlockVariation} = wp.blocks;
const {__} = wp.i18n;

export const registerPageHeaderBlock = () => {
  const classname = 'is-pattern-p4-page-header';

  const scope = ['inserter'];

  const attributes = {
    className: classname,
    mediaType: 'image',
    mediaUrl: `${window.p4bk_vars.themeUrl}/images/placeholders/placeholder-546x415.jpg`,
    imageFill: false,
    align: 'full',
  };

  const innerBlocks = () => [
    ['core/group', {}, [
      ['core/heading', {
        level: 1,
        backgroundColor: 'white',
        placeholder: __('Enter title', 'planet4-blocks-backend'),
      }],
    ]],
    ['core/paragraph', {
      placeholder: __('Enter description', 'planet4-blocks-backend'),
      style: {typography: {fontSize: '1.25rem'}},
    }],
    ['core/buttons', {}, [
      ['core/button', {className: 'is-style-cta'}],
    ]],
  ];

  ['left', 'right'].forEach(variation => {
    registerBlockVariation('core/media-text', {
      name: `page-header-img-${variation}`,
      // eslint-disable-next-line @wordpress/i18n-no-variables, no-restricted-syntax
      title: __(`Page header with image on the ${variation}`, 'planet4-blocks-backend'),
      // eslint-disable-next-line @wordpress/i18n-no-variables, no-restricted-syntax
      description: __(`Page header with image on the ${variation}`, 'planet4-blocks-backend'),
      scope,
      attributes: {mediaPosition: variation, ...attributes},
      innerBlocks: innerBlocks(),
      isActive: blockAttributes => (
        blockAttributes.className === classname &&
        blockAttributes.mediaPosition === variation
      ),
    });
  });
};
