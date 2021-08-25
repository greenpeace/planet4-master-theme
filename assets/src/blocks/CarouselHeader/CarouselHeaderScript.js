import { CarouselHeaderFrontend } from './CarouselHeaderFrontend';
import { hydrateBlock } from '../../functions/hydrateBlock';

hydrateBlock('planet4-blocks/carousel-header-beta', CarouselHeaderFrontend);
// Fallback for non migrated content. Remove after migration.
document.querySelectorAll( `[data-render='planet4-blocks/carousel-header-beta']` ).forEach(
  blockNode => {
    const attributes = JSON.parse( blockNode.dataset.attributes );
    wp.element.render( <CarouselHeaderFrontend { ...attributes.attributes } />, blockNode );
  }
);
