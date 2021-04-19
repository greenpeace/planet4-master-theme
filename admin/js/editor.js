/* global wp */
const { unregisterBlockStyle } = wp.blocks;

wp.domReady(() => {
  // Remove Image block styles
  unregisterBlockStyle('core/image', 'rounded');
  unregisterBlockStyle('core/image', 'default');

  // Remove Take Action and Campaign covers styles for Covers block in campaigns
  const postType = wp.data.select('core/editor').getCurrentPostType();
  if (postType === 'campaign') {
    unregisterBlockStyle('planet4-blocks/covers', 'take-action');
    unregisterBlockStyle('planet4-blocks/covers-beta', 'take-action');
    unregisterBlockStyle('planet4-blocks/covers', 'campaign');
    unregisterBlockStyle('planet4-blocks/covers-beta', 'campaign');
  }
});
