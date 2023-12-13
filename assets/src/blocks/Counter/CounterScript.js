import {CounterFrontend} from './CounterFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';
import {BLOCK_NAME} from './CounterBlock';
import {createRoot} from 'react-dom/client';

hydrateBlock(BLOCK_NAME, CounterFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${BLOCK_NAME}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CounterFrontend {...attributes.attributes} />);
  }
);
