// This ESLint error is disabled since 'regenerator-runtime/runtime' has already been added by another package.
// There is no need to explicitly include it in the list of dependencies in the package.json file.
// eslint-disable-next-line import/no-extraneous-dependencies
import 'regenerator-runtime/runtime';

import {setupLightboxForImages} from './blocks/components/Lightbox/setupLightboxForImages';
import {setupParallax} from './blocks/components/Parallax/setupParallax';
import {setupBlockFrontend} from './blocks/components/BlockFrontend/setupBlockFrontend';

document.addEventListener('DOMContentLoaded', () => {
  setupBlockFrontend();
  setupLightboxForImages();
  setupParallax();
});
