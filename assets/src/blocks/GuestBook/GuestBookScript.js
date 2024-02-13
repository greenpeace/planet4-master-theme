import {createRoot} from 'react-dom/client';
import {BLOCK_NAME} from './GuestBookBlock';
import {GuestBookFrontend} from './GuestBookFrontend';

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${BLOCK_NAME}"]`).forEach(
  blockNode => {
    const rootElement = createRoot(blockNode);
    rootElement.render(<GuestBookFrontend />);
  }
);
