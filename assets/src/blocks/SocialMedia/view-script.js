import {createRoot} from 'react-dom/client';
import metadata from './block.json';
import {SocialMediaFrontend} from './SocialMediaFrontend';

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<SocialMediaFrontend {...attributes.attributes} />);
  }
);
