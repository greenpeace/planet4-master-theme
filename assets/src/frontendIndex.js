// This ESLint error is disabled since 'regenerator-runtime/runtime' has already been added by another package.
// There is no need to explicitly include it in the list of dependencies in the package.json file.
// eslint-disable-next-line import/no-extraneous-dependencies
import 'regenerator-runtime/runtime';

import {SubmenuFrontend} from './blocks/Submenu/SubmenuFrontend';
import {HappypointFrontend} from './blocks/Happypoint/HappypointFrontend';
import {MediaFrontend} from './blocks/Media/MediaFrontend';
import {ColumnsFrontend} from './blocks/Columns/ColumnsFrontend';
import {setupMediaElementJS} from './blocks/Media/setupMediaElementJS';

// Render React components
const COMPONENTS = {
  'planet4-blocks/submenu': SubmenuFrontend,
  'planet4-blocks/happypoint': HappypointFrontend,
  'planet4-blocks/media-video': MediaFrontend,
  'planet4-blocks/columns': ColumnsFrontend,
};

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-render]').forEach(
    blockNode => {
      const blockName = blockNode.dataset.render;
      if (!COMPONENTS[blockName]) {
        return;
      }

      const BlockFrontend = COMPONENTS[blockName];
      if (!BlockFrontend) {
        return;
      }
      const attributes = JSON.parse(blockNode.dataset.attributes);
      const rootElement = wp.element.createRoot(blockNode);
      rootElement.render(<BlockFrontend {...attributes.attributes} />);
    }
  );

  setupMediaElementJS();
});
