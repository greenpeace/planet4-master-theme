
const {unregisterBlockStyle, registerBlockStyle} = wp.blocks;
const {__} = wp.i18n;

const BLOCK_STYLES_TO_ADD = [
  {
    blockName: 'core/accordion',
    blockStyles: [
      {
        name: 'outline',
        label: __('Outline', 'planet4-master-theme-backend'),
        isDefault: true,
      },
      {
        name: 'green',
        label: __('Green', 'planet4-master-theme-backend'),
      },
      {
        name: 'light',
        label: __('Light', 'planet4-master-theme-backend'),
      },
    ],
  },
  {
    blockName: 'core/button',
    blockStyles: [
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
    ],
  },
  {
    blockName: 'core/columns',
    blockStyles: [{
      name: 'mobile-carousel',
      label: __('Mobile carousel', 'planet4-master-theme-backend'),
    }],
  },
  {
    blockName: 'core/group',
    blockStyles: [
      {
        name: 'space-evenly',
        label: __('Space evenly', 'planet4-master-theme-backend'),
      },
    ],
  },
  {
    blockName: 'core/image',
    blockStyles: [
      {
        name: 'rounded-180',
        label: __('Rounded 180x180', 'planet4-master-theme-backend'),
      },
      {
        name: 'rounded-90',
        label: __('Rounded 90x90', 'planet4-master-theme-backend'),
      },
    ],
  },
  {
    blockName: 'core/heading',
    blockStyles: [{
      name: 'chevron',
      label: __('Chevron', 'planet4-master-theme-backend'),
    }],
  },
  {
    blockName: 'core/media-text',
    blockStyles: [
      {
        name: 'parallax',
        label: __('Parallax - add a parallax effect to the image when scrolling the page', 'planet4-master-theme-backend'),
      },
    ],
  },
];

const BLOCK_STYLES_TO_REMOVE = [
  {
    blockName: 'core/accordion',
    blockStyles: ['default'],
  },
  {
    blockName: 'core/button',
    blockStyles: ['fill', 'outline'],
  },
  {
    blockName: 'core/quote',
    blockStyles: ['default', 'plain'],
  },
  {
    blockName: 'core/image',
    blockStyles: ['rounded'],
  },
];

export const registerBlockStyles = () => {
  // Remove certain block styles
  BLOCK_STYLES_TO_REMOVE.forEach(block => {
    block.blockStyles.forEach(style => unregisterBlockStyle(block.blockName, style));
  });

  // Add our custom block styles
  BLOCK_STYLES_TO_ADD.forEach(block => registerBlockStyle(block.blockName, block.blockStyles));
};
