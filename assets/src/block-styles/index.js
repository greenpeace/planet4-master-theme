
const {unregisterBlockStyle, registerBlockStyle} = wp.blocks;
const {__} = wp.i18n;

export const registerBlockStyles = () => {
  // Remove Button block native styles
  unregisterBlockStyle('core/button', 'fill');
  unregisterBlockStyle('core/button', 'outline');

  // Add our custom Button block styles
  registerBlockStyle('core/button', [
    {
      name: 'secondary',
      label: __('Secondary', 'planet4-blocks-backend'),
      isDefault: true,
    },
    {
      name: 'cta',
      label: __('Primary', 'planet4-blocks-backend'),
    },
    {
      name: 'transparent',
      label: __('Transparent', 'planet4-blocks-backend'),
    },
  ]);

  ['core/group'].forEach(block => {
    registerBlockStyle(block, [
      {
        name: 'space-evenly',
        label: __('Space evenly', 'planet4-blocks-backend'),
      },
      {
        name: 'reset-margin',
        label: __('Reset margin', 'planet4-blocks-backend'),
      },
    ]);
  });

  registerBlockStyle('core/columns', {
    name: 'mobile-carousel',
    label: __('Mobile carousel', 'planet4-blocks-backend'),
  });

  // Add our custom Heading styles
  registerBlockStyle('core/heading', [
    {
      name: 'chevron',
      label: __('Chevron', 'planet4-blocks-backend'),
    },
  ]);

  // Unregister default Rounded Image block style
  unregisterBlockStyle('core/image', 'rounded');

  // Register our custom Image block styles
  registerBlockStyle('core/image', [
    {
      name: 'big-circle',
      label: __('Rounded 180x180', 'planet4-blocks-backend'),
    },
    {
      name: 'small-circle',
      label: __('Rounded 90x90', 'planet4-blocks-backend'),
    },
  ]);

  // Register our custom Media & Test block styles
  registerBlockStyle('core/media-text', [
    {
      name: 'parallax',
      label: __('Parallax - add a parallax effect to the image when scrolling the page', 'planet4-blocks-backend'),
    },
  ]);
};
