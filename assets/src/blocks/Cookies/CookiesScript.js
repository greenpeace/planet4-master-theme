import {createRoot} from 'react-dom/client';
import {BLOCK_NAME} from './CookiesBlock';
import {CookiesFrontend} from './CookiesFrontend';

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${BLOCK_NAME}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CookiesFrontend {...attributes.attributes} />);
  }
);
