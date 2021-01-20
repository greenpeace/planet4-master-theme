/* global wp */

wp.domReady(() => {
  // Button block styles
  wp.blocks.registerBlockStyle( 'core/button', {
    name: 'secondary',
    label: 'Secondary',
    isDefault: true
  });
  wp.blocks.registerBlockStyle( 'core/button', {
    name: 'cta',
    label: 'CTA'
  });
  wp.blocks.registerBlockStyle( 'core/button', {
    name: 'donate',
    label: 'Donate'
  });
  wp.blocks.unregisterBlockStyle('core/button', 'outline');
  wp.blocks.unregisterBlockStyle('core/button', 'fill');

  // Image block styles
  wp.blocks.unregisterBlockStyle('core/image', 'rounded');
  wp.blocks.unregisterBlockStyle('core/image', 'default');
});
