/* global wp */

wp.domReady(() => {
  // Image block styles
  wp.blocks.unregisterBlockStyle('core/image', 'rounded');
  wp.blocks.unregisterBlockStyle('core/image', 'default');
});
