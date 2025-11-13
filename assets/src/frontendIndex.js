// This ESLint error is disabled since 'regenerator-runtime/runtime' has already been added by another package.
// There is no need to explicitly include it in the list of dependencies in the package.json file.
// eslint-disable-next-line import/no-extraneous-dependencies
import 'regenerator-runtime/runtime';

<<<<<<< HEAD
import {setupLightboxForImages} from './blocks/components/Lightbox/setupLightboxForImages';
import {setupParallax} from './blocks/components/Parallax/setupParallax';
import {setupBlockFrontend} from './blocks/components/BlockFrontend/setupBlockFrontend';
=======
import {createRoot} from 'react-dom/client';
import {TableOfContentsFrontend} from './blocks/TableOfContents/TableOfContentsFrontend';
import {HappyPointFrontend} from './blocks/HappyPoint/HappyPointFrontend';
import {ColumnsFrontend} from './blocks/Columns/ColumnsFrontend';
import {TopicLinkFrontend} from './blocks/TopicLink/TopicLinkFrontend';
import {SecondaryNavigationFrontend} from './blocks/SecondaryNavigation/SecondaryNavigationFrontend';

// Render React components
const COMPONENTS = {
  'planet4-blocks/submenu': TableOfContentsFrontend,
  'planet4-blocks/happypoint': HappyPointFrontend,
  'planet4-blocks/columns': ColumnsFrontend,
  'planet4-blocks/topic-link': TopicLinkFrontend,
  'planet4-blocks/secondary-navigation': SecondaryNavigationFrontend,
};
>>>>>>> 8468564d (PLANET-7794: Load javascript code only where they are needed)

document.addEventListener('DOMContentLoaded', () => {
  setupBlockFrontend();
  setupLightboxForImages();

  // lazy-load rellax only when needed
  if(document.querySelectorAll('.is-style-parallax img').length > 0) {
    import('./blocks/components/Parallax/setupParallax').then(({setupParallax}) => {
      setupParallax();
    });
  }
});
