import {CounterFrontend} from './CounterFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';
import {createRoot} from 'react-dom/client';

hydrateBlock('planet4-blocks/counter', CounterFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render="planet4-blocks/counter"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CounterFrontend {...attributes.attributes} />);
  }
);
