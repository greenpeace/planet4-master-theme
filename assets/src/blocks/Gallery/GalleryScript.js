import {GalleryFrontend} from './GalleryFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';

hydrateBlock('planet4-blocks/gallery', GalleryFrontend, {renderLightbox: true});

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render="planet4-blocks/gallery"]').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = wp.element.createRoot(blockNode);
    rootElement.render(<GalleryFrontend {...attributes} renderLightbox />);
  }
);
