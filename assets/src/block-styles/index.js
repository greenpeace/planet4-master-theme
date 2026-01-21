
const {unregisterBlockStyle, registerBlockStyle} = wp.blocks;
const {__} = wp.i18n;

export const registerBlockStyles = () => {
  // Remove Button block native styles
  unregisterBlockStyle('core/button', 'fill');
  unregisterBlockStyle('core/button', 'outline');

  // Remove Quote block styles.
  unregisterBlockStyle('core/quote', 'default');
  unregisterBlockStyle('core/quote', 'plain');

  // Add our custom Button block styles
  registerBlockStyle('core/button', [
    {
      name: 'secondary',
      label: __('Secondary', 'planet4-master-theme-backend'),
      isDefault: true,
    },
    {
      name: 'cta',
      label: __('Primary', 'planet4-master-theme-backend'),
    },
    {
      name: 'transparent',
      label: __('Transparent', 'planet4-master-theme-backend'),
    },
  ]);

  ['core/group'].forEach(block => {
    registerBlockStyle(block, [
      {
        name: 'space-evenly',
        label: __('Space evenly', 'planet4-master-theme-backend'),
      },
      {
        name: 'reset-margin',
        label: __('Reset margin', 'planet4-master-theme-backend'),
      },
    ]);
  });

  registerBlockStyle('core/columns', {
    name: 'mobile-carousel',
    label: __('Mobile carousel', 'planet4-master-theme-backend'),
  });

  // Add our custom Heading styles
  registerBlockStyle('core/heading', [
    {
      name: 'chevron',
      label: __('Chevron', 'planet4-master-theme-backend'),
    },
  ]);

  // Unregister default Rounded Image block style
  unregisterBlockStyle('core/image', 'rounded');

  // Register our custom Image block styles
  registerBlockStyle('core/image', [
    {
      name: 'rounded-180',
      label: __('Rounded 180x180', 'planet4-master-theme-backend'),
    },
    {
      name: 'rounded-90',
      label: __('Rounded 90x90', 'planet4-master-theme-backend'),
    },
  ]);

  // Register our custom Media & Test block styles
  registerBlockStyle('core/media-text', [
    {
      name: 'parallax',
      label: __('Parallax - add a parallax effect to the image when scrolling the page', 'planet4-master-theme-backend'),
    },
  ]);
};
