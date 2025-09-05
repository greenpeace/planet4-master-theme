import {Lightbox} from './Lightbox';
import {createRoot} from 'react-dom/client';

/**
 * Extracts the WordPress image ID from an element's class list.
 *
 * @param {HTMLElement} el - The DOM element to inspect.
 * @return {number|null} The image ID if found, otherwise null.
 */
const getImageIdFromClass = el => {
  for (const cls of el.classList) {
    const match = cls.match(/^wp-image-(\d+)$/);
    if (match) {
      return parseInt(match[1], 10);
    }
  }
  return null;
};

/**
 * Fetches metadata for a WordPress image by its DOM element.
 *
 * @param {HTMLImageElement} imgEl - The image element.
 * @return {Promise<{url: string, width: number|null, height: number|null}|null>}
 *   An object containing the image URL and dimensions, or null if not found.
 */
const getImageMeta = async imgEl => {
  const imgId = getImageIdFromClass(imgEl);
  if (!imgId) { return null; }

  try {
    const response = await fetch(`/wp-json/wp/v2/media/${imgId}`);
    if (!response.ok) { throw new Error('Failed to fetch media info'); }

    const data = await response.json();

    const url =
      data?.media_details?.sizes?.full?.source_url ?? // try full size
      data?.media_details?.sizes?.large?.source_url ?? // fallback to large
      data?.source_url; // fallback to original

    const width = data?.media_details?.width ?? null;
    const height = data?.media_details?.height ?? null;

    return {url, width, height};
  } catch (err) {
    return null;
  }
};

/**
 * Prepares a single image block for the lightbox.
 *
 * @param {HTMLElement} lightBoxNode           - The container node for the lightbox portal.
 * @param {string}      [imageSelector='img']  - Selector for the image inside the block.
 * @param {string|null} [captionSelector=null] - Selector for the caption inside the block.
 * @return {function(HTMLElement, number): Promise<void>} Callback for `forEach` over image blocks.
 */
const setupImageAndCaption = (lightBoxNode, imageSelector = 'img', captionSelector = null) => {
  return async (imageBlock, index) => {
    const image = imageBlock.querySelector(imageSelector);

    if (!image) {
      return;
    }

    const imgSrc = image.src ?? image.dataset.src;

    const item = {
      src: imgSrc,
      originalSrc: await getImageMeta(image) ?? imgSrc,
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

/**
 * Initializes the lightbox for images on the page.
 */
export const setupLightboxForImages = function () {
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
