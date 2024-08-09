import {CounterFrontend} from './CounterFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';
import {BLOCK_NAME} from './CounterBlock';

hydrateBlock(BLOCK_NAME, CounterFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${BLOCK_NAME}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = wp.element.createRoot(blockNode);
    rootElement.render(<CounterFrontend {...attributes.attributes} />);
  }
);
