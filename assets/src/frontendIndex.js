import 'regenerator-runtime/runtime';

document.addEventListener('DOMContentLoaded', () => {

  // lazy-load photoswipe lightbox only when needed
  for (const selector of [
    '.wp-block-image:not(.force-no-lightbox)',
    '.post-content .wp-caption, .page-content .wp-caption',
    '.post-content p:not(.wp-caption):not(.force-no-lightbox), .page-content p:not(.wp-caption):not(.force-no-lightbox)',
    '.wp-block-media-text:not(.force-no-lightbox)',
  ]) {
    if(document.querySelectorAll(selector).length) {
      import('./blocks/components/Lightbox/setupLightboxForImages').then(({setupLightboxForImages}) => {
        setupLightboxForImages();
      });
      break;
    }
  }

  // lazy-load rellax only when needed
  if(document.querySelectorAll('.is-style-parallax img').length) {
    import('./blocks/components/Parallax/setupParallax').then(({setupParallax}) => {
      setupParallax();
    });
  }

  document.querySelectorAll('.is-style-sticky-container').forEach(cardContainer => {
    let initialTop = 140;
    let initialRotation = 0;
    const rotatations = [-1.5, -1, 0, 1, 1.5];

    for (const card of cardContainer.children) {
      card.classList.add('sticky-item');
      card.style.top = `${initialTop}px`;
      card.style.transform = `rotate(${initialRotation}deg)`;

      initialTop = initialTop + 20;
      initialRotation = rotatations[Math.floor(Math.random() * rotatations.length)];
    }
  });

});
