/* global wp */
const { unregisterBlockStyle, registerBlockStyle } = wp.blocks;
const { __ } = wp.i18n;

wp.domReady(() => {
  // Remove Image block styles
  unregisterBlockStyle('core/image', 'rounded');
  unregisterBlockStyle('core/image', 'default');

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
  const styles = [
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

  registerBlockStyle('core/button', styles);
});
