import {createRoot} from 'react-dom/client';
import {CookiesFrontend} from './CookiesFrontend';

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render="planet4-blocks/cookies"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CookiesFrontend {...attributes.attributes} />);
  }
);
