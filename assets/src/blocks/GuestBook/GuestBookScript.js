import {BLOCK_NAME} from './GuestBookBlock';
import {GuestBookFrontend} from './GuestBookFrontend';

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${BLOCK_NAME}"]`).forEach(
  blockNode => {
    const rootElement = wp.element.createRoot(blockNode);
    rootElement.render(<GuestBookFrontend />);
  }
);
