import 'regenerator-runtime/runtime';

import {createRoot} from 'react-dom/client';
import {TableOfContentsFrontend} from './blocks/TableOfContents/TableOfContentsFrontend';
import {HappypointFrontend} from './blocks/Happypoint/HappypointFrontend';
import {MediaFrontend} from './blocks/Media/MediaFrontend';
import {ColumnsFrontend} from './blocks/Columns/ColumnsFrontend';
import {setupMediaElementJS} from './blocks/Media/setupMediaElementJS';
import {BLOCK_NAME} from './blocks/Constants/Constants';

// Render React components
const COMPONENTS = {
  [BLOCK_NAME.tableOfContents.name]: TableOfContentsFrontend,
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
      const rootElement = createRoot(blockNode);
      rootElement.render(<BlockFrontend {...attributes.attributes} />);
    }
  );

  setupMediaElementJS();
});
