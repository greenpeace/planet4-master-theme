import { GalleryFrontend } from './GalleryFrontend';

document.addEventListener( 'DOMContentLoaded', () => {
  const galleryBlocks = [...document.querySelectorAll('[data-render="planet4-blocks/gallery"]')];

  galleryBlocks.forEach(blockNode => {
    const attributes = JSON.parse( blockNode.dataset.attributes );
    wp.element.render( <GalleryFrontend { ...attributes.attributes } />, blockNode );
  });
});
