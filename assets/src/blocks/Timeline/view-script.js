import {createRoot} from 'react-dom/client';
import {TimelineFrontend} from './timeline';
import {hydrateBlock} from '../../functions/hydrateBlock';
import metadata from './block.json';

hydrateBlock(metadata.name, TimelineFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<TimelineFrontend {...attributes} />);

  }
);
