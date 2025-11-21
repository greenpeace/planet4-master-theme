import {createRoot} from 'react-dom/client';

import {TableOfContentsFrontend} from '../../TableOfContents/TableOfContentsFrontend.js';
import {HappyPointFrontend} from '../../HappyPoint/HappyPointFrontend.js';
import {ColumnsFrontend} from '../../Columns/ColumnsFrontend.js';
import {SecondaryNavigationFrontend} from '../../SecondaryNavigation/SecondaryNavigationFrontend.js';

// Render React components
const COMPONENTS = {
  'planet4-blocks/submenu': TableOfContentsFrontend,
  'planet4-blocks/happypoint': HappyPointFrontend,
  'planet4-blocks/columns': ColumnsFrontend,
  'planet4-blocks/secondary-navigation': SecondaryNavigationFrontend,
};

export const setupBlockFrontend = () => {
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

      if (!blockNode._reactRoot) {
        blockNode._reactRoot = createRoot(blockNode);
      }
      blockNode._reactRoot.render(<BlockFrontend {...attributes.attributes} />);
    }
  );

};
