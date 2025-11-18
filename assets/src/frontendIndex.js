// This ESLint error is disabled since 'regenerator-runtime/runtime' has already been added by another package.
// There is no need to explicitly include it in the list of dependencies in the package.json file.
// eslint-disable-next-line import/no-extraneous-dependencies
import 'regenerator-runtime/runtime';

import {setupBlockFrontend} from './blocks/components/BlockFrontend/setupBlockFrontend';

document.addEventListener('DOMContentLoaded', () => {
  setupBlockFrontend();

  // lazy-load photoswipe lightbox only when needed
  for (const selector of [
    '.wp-block-image:not(.force-no-lightbox)',
    '.post-content .wp-caption, .page-content .wp-caption',
    '.post-content p:not(.wp-caption):not(.force-no-lightbox), .page-content p:not(.wp-caption):not(.force-no-lightbox)',
    '.wp-block-media-text:not(.force-no-lightbox)',
  ]) {
    if(document.querySelectorAll(selector).length) {
      import('./blocks/components/Lightbox/setupLightboxForImages').then(({setupLightboxForImages}) => {
        setupLightboxForImages();
      });
      break;
    }
  }

  // lazy-load rellax only when needed
  if(document.querySelectorAll('.is-style-parallax img').length) {
    import('./blocks/components/Parallax/setupParallax').then(({setupParallax}) => {
      setupParallax();
    });
  }
});
