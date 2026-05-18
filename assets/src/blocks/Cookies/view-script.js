import {createRoot} from 'react-dom/client';
import metadata from './block.json';
import {CookiesFrontend} from './cookies';

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CookiesFrontend {...attributes.attributes} />);
  }
);
