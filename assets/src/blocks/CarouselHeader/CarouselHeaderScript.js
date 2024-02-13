import {CarouselHeaderFrontend} from './CarouselHeaderFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';
import {createRoot} from 'react-dom/client';

hydrateBlock('planet4-blocks/carousel-header', CarouselHeaderFrontend, {decoding: true});
// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render=\'planet4-blocks/carousel-header\']').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = createRoot(blockNode);
    rootElement.render(<CarouselHeaderFrontend {...attributes.attributes} />);
  }
);
