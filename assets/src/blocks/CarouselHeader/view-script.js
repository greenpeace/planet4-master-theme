import metadata from './block.json';
import {CarouselHeaderFrontend} from './carousel-header';
import {hydrateBlock} from '../../functions/hydrateBlock';
import {createRoot} from 'react-dom/client';

hydrateBlock(metadata.name, CarouselHeaderFrontend, {decoding: true});

// Fallback for non migrated content. Remove after migration.
document.querySelectorAll(`[data-render="${metadata.name}"]`).forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CarouselHeaderFrontend {...attributes.attributes} />);
  }
);
