import { CarouselHeaderFrontend } from './CarouselHeaderFrontend';

document.querySelectorAll( `[data-render='planet4-blocks/carousel-header']` ).forEach(
  blockNode => {
    const attributes = JSON.parse( blockNode.dataset.attributes );
    wp.element.render( <CarouselHeaderFrontend { ...attributes.attributes } />, blockNode );
  }
);
