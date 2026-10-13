import metadata from './block.json';
import {CounterFrontend} from './counter';
import {hydrateBlock} from '../../functions/hydrateBlock';
import {createRoot} from 'react-dom/client';

hydrateBlock(metadata.name, CounterFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CounterFrontend {...attributes.attributes} />);
  }
);
