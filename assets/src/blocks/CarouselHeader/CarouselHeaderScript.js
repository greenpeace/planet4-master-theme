import {CarouselHeaderFrontend} from './CarouselHeaderFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';

hydrateBlock('planet4-blocks/carousel-header', CarouselHeaderFrontend, {decoding: true});
// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render=\'planet4-blocks/carousel-header\']').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    const rootElement = wp.element.createRoot(blockNode);
    rootElement.render(<CarouselHeaderFrontend {...attributes.attributes} />);
  }
);
