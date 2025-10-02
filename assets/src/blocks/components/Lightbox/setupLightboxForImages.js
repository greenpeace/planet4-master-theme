import {Lightbox} from './Lightbox';
import {createRoot} from 'react-dom/client';

const setupImageAndCaption = (lightBoxNode, imageSelector = 'img', captionSelector = null) => {
  // Returns the callback for `forEach`
  return (imageBlock, index) => {
    const image = imageBlock.querySelector(imageSelector);

    if (!image) {
      return;
    }

    // Derive full/original image URL by removing size suffix
    let fullSrc = image.src ? image.src : image.dataset.src;
    fullSrc = image.src.replace(/-\d+x\d+(?=\.\w+$)/, '');

    const item = {
      src: fullSrc,
      w: 0,
      h: 0,
    };

    const caption = imageBlock.querySelector(captionSelector);
    if (caption) {
      item.title = caption.innerHTML;
    }
    const rootElement = createRoot(lightBoxNode);

    imageBlock.querySelector('img').addEventListener('click', () => rootElement.render(<Lightbox isOpen={true} index={index} items={[item]} />));
  };
};

export const setupLightboxForImages = function() {
  // We can't use `createPortal` outside `render()`,
  // `React.render()` needs a node, even if it ends up being empty.
  // See: https://github.com/facebook/react/issues/12653#issuecomment-382851495
  const lightBoxNode = document.createElement('div');
  lightBoxNode.style.position = 'fixed';

  document.body.appendChild(lightBoxNode);

  const imageBlocks = [...document.querySelectorAll('.wp-block-image:not(.force-no-lightbox)')];
  // Images that are links should not have the lightbox
  const imageBlocksWithoutLinks = imageBlocks.filter(imageBlock => {
    const image = imageBlock.querySelector('img');
    return image.parentElement.tagName !== 'A';
  });
  imageBlocksWithoutLinks.forEach(setupImageAndCaption(lightBoxNode, 'img', 'figcaption'));

  const imagesWithCaptions = document.querySelectorAll('.post-content .wp-caption, .page-content .wp-caption');
  imagesWithCaptions.forEach(setupImageAndCaption(lightBoxNode, 'img', '.wp-caption-text'));

  const imagesInParagraphs = document.querySelectorAll(
    '.post-content p:not(.wp-caption):not(.force-no-lightbox), .page-content p:not(.wp-caption):not(.force-no-lightbox)'
  );
  imagesInParagraphs.forEach(setupImageAndCaption(lightBoxNode, 'img'));

  const mediaAndTextImages = document.querySelectorAll('.wp-block-media-text:not(.force-no-lightbox)');
  mediaAndTextImages.forEach(setupImageAndCaption(lightBoxNode));
};
