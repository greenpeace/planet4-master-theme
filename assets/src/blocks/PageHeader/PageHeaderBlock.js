const { registerBlockVariation } = wp.blocks;
const { __ } = wp.i18n;

export const registerPageHeaderBlock = () => {

  let classname = 'is-pattern-p4-page-header';

  let scope = ['inserter'];

  let attributes = {
      className: classname,
      mediaType: 'image',
      mediaUrl: `${window.p4bk_vars.themeUrl}/images/placeholders/placeholder-546x415.jpg`,
      imageFill: false,
      align:'full'
  };

  let innerBlocks = (imgPosition) => [
    ['core/group', {}, [
      ['core/heading', {
        level: 1,
        backgroundColor: 'white',
        placeholder: __('Enter title', 'planet4-blocks-backend')
      }]
    ]],
    ['core/paragraph', {
      placeholder: __('Enter description', 'planet4-blocks-backend'),
      style: { typography: { fontSize: '1.25rem'} }
    }],
    ['core/buttons', {}, [
      ['core/button', {className: 'is-style-cta'}]
    ]],
  ];

  [ 'left' , 'right' ].forEach((variation) => {
    registerBlockVariation('core/media-text', {
      name: `page-header-img-${variation}`,
      title: __(`Page header with image on the ${variation}`, 'planet4-blocks-backend'),
      description: __(`Page header with image on the ${variation}`, 'planet4-blocks-backend'),
      scope,
      attributes: { mediaPosition: variation, ...attributes },
      innerBlocks: innerBlocks(variation),
      isActive: (blockAttributes) => (
        blockAttributes.className === classname
        && blockAttributes.mediaPosition === variation
      )
    });
  });
}
