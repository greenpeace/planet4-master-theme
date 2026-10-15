import metadata from './block.json';
import {createRoot} from 'react-dom/client';
import {GalleryFrontend} from './GalleryFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';

hydrateBlock(metadata.name, GalleryFrontend);

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<GalleryFrontend {...attributes.attributes} />);
  }
);
