import {createRoot} from 'react-dom/client';
import {CoversFrontend} from './CoversFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';
import {BLOCK_NAME} from './CoversConstants';

hydrateBlock(BLOCK_NAME, CoversFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${BLOCK_NAME}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CoversFrontend {...attributes} />);
  }
);

