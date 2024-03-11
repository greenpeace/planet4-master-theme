import {createRoot} from 'react-dom/client';
import {SubmenuFrontend} from './blocks/Submenu/SubmenuFrontend';
import {HappypointFrontend} from './blocks/Happypoint/HappypointFrontend';
import {MediaFrontend} from './blocks/Media/MediaFrontend';
import {setupMediaElementJS} from './blocks/Media/setupMediaElementJS';

// Render React components
const COMPONENTS = {
  'planet4-blocks/submenu': SubmenuFrontend,
  'planet4-blocks/happypoint': HappypointFrontend,
  'planet4-blocks/media-video': MediaFrontend,
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
