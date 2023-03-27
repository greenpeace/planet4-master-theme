import {CarouselHeaderFrontend} from './CarouselHeaderFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';

hydrateBlock('planet4-blocks/carousel-header', CarouselHeaderFrontend);
// Fallback for non migrated content. Remove after migration.
document.querySelectorAll('[data-render=\'planet4-blocks/carousel-header\']').forEach(
  blockNode => {
    const attributes = JSON.parse(blockNode.dataset.attributes);
    wp.element.render(<CarouselHeaderFrontend {...attributes.attributes} />, blockNode);
  }
);
