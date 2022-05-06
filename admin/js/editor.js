/* global wp */
const { unregisterBlockStyle, registerBlockStyle } = wp.blocks;
const { __ } = wp.i18n;

wp.domReady(() => {
  // Remove Take Action and Campaign covers styles for Covers block in campaigns
  const postType = wp.data.select('core/editor').getCurrentPostType();
  if (postType === 'campaign') {
    unregisterBlockStyle('planet4-blocks/covers', 'take-action');
    unregisterBlockStyle('planet4-blocks/covers', 'campaign');
  }

  // Remove Button block native styles
  unregisterBlockStyle('core/button', 'fill');
  unregisterBlockStyle('core/button', 'outline');

  // Add our custom Button block styles
  const buttonStyles = [
    {
      name: 'secondary',
      label: __('Secondary', 'planet4-blocks-backend'),
      isDefault: true
    },
    {
      name: 'cta',
      label: __('Primary', 'planet4-blocks-backend')
    },
  ];

  registerBlockStyle('core/button', buttonStyles);

  ['core/media-text', 'core/group', 'core/column'].forEach(
    block => registerBlockStyle(block, [
      {
        name: 'small-padding',
        label: __('Small padding', 'planet4-blocks-backend'),
      }, {
        name: 'medium-padding',
        label: __('Medium padding', 'planet4-blocks-backend'),
      }, {
        name: 'large-padding',
        label: __('Large padding', 'planet4-blocks-backend'),
      },
    ]),
  );

  ['core/group'].forEach(block => {
    registerBlockStyle(block, {
      name: 'space-evenly',
      label: __('Space evenly', 'planet4-blocks-backend'),
    });
  });

  ['core/paragraph'].forEach(block => {
    registerBlockStyle(block, {
      name: 'roboto-font-family',
      label: __('Roboto font family', 'planet4-blocks-backend'),
    });
  });

  registerBlockStyle('core/columns', {
    name: 'mobile-carousel',
    label: __('Mobile carousel', 'planet4-blocks-backend'),
  });

  // Add our custom Heading styles
  const headingStyles = [
    {
      name: 'chevron',
      label: __('Chevron', 'planet4-blocks-backend')
    }
  ];

  registerBlockStyle('core/heading', headingStyles);
});
