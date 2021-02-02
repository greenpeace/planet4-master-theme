/* global wp */
const { unregisterBlockStyle } = wp.blocks;

wp.domReady(() => {
  // Remove Image block styles
  unregisterBlockStyle('core/image', 'rounded');
  unregisterBlockStyle('core/image', 'default');

  // Remove Take Action and Campaign covers styles for Covers block in campaigns
  const postType = document.querySelector('form.metabox-base-form input#post_type').value;
  if (postType === 'campaign') {
    unregisterBlockStyle('planet4-blocks/covers', 'take-action');
    unregisterBlockStyle('planet4-blocks/covers', 'campaign');
  }
});
