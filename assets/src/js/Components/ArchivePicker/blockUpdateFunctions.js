/**
 * Updates the attributes and content of the Image block based on a given image object.
 *
 * This function generates new content for a block by either updating the existing HTML
 * of the block if it exists (`originalContent`), or creating new block content from scratch.
 * It returns an object with updated attributes and the rendered HTML content.
 *
 * @param {Object} image        - The image data object.
 *
 * @param {Object} currentBlock - The current block object.
 *
 * @return {Object} An object containing the updated block attributes and rendered HTML content.
 */
export const updateImageBlockAttributes = (image, currentBlock) => {
  const {id, source_url, caption, alt_text} = image;
  const div = document.createElement('div');

  if (id) {
    // Check if the current block has content already, then just replace it.
    if (currentBlock.originalContent) {
      div.innerHTML = currentBlock.originalContent;
      div.querySelector('img').setAttribute('class', `wp-image-${id}`);
      div.querySelector('img').src = source_url;
      if (div.querySelector('figcaption')) {
        div.querySelector('figcaption').textContent = caption.raw;
      }
    } else {
      // If there is no content then create block content.
      div.innerHTML = `
        <figure class="wp-block-image size-large">
          <img src=${source_url} alt=${alt_text} class="wp-image-${id}"/>
          <figcaption class="wp-element-caption">
            ${caption.raw}
          </figcaption>
        </figure>`;
    }
  }

  return {
    attributes: {
      id,
      url: source_url,
      caption: caption.raw,
      alt: alt_text,
    },
    originalContent: div.innerHTML,
  };
};

export const updateHappyPointAttributes = id => {
  // Need to update only the id property for the Happy Point block
  return {attributes: {id}};
};

export const updateMediaAndTextAttributes = (image, currentBlock) => {
  const div = document.createElement('div');
  const {id, link, source_url, alt_text} = image;

  if (currentBlock.originalContent) {
    div.innerHTML = currentBlock.originalContent;
    div.querySelector('img').setAttribute('class', `wp-image-${id}`);
    div.querySelector('img').src = source_url;
  } else {
    div.innerHTML = `
      <div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media"><img src=${source_url} alt=${alt_text} class="wp-image-${id} size-full"/></figure><div class="wp-block-media-text__content"></div></div>
    `;
  }

  return {
    attributes: {
      mediaLink: link,
      mediaUrl: source_url,
      mediaAlt: alt_text,
      mediaId: id,
      mediaType: 'image',
      useFeaturedImage: false,
    },
    originalContent: div.innerHTML,
  };
};

/**
 * Updates the attributes of the Carousel block with new image data at the current slide index.
 *
 * This function modifies the `slides` array of the current Carousel block, updating the slide
 * at the `currentImageIndex` with a new image ID, image URL, and a constructed `srcset` string.
 *
 * @param {Object} image        - The image data object.
 *
 * @param {Object} currentBlock - The current carousel block object.
 *
 * @return {Object} An object containing updated block attributes.
 */
export const updateCarouselBlockAttributes = (image, currentBlock) => {
  const slides = currentBlock.attributes.slides;
  const currentImageIndex = currentBlock.attributes.currentImageIndex;
  const {thumbnail, medium_large, medium, large, full} = image.media_details.sizes;
  const imageSrcSet = `
  ${thumbnail.source_url} ${thumbnail.width}w,
  ${medium_large.source_url} ${medium_large.width}w,
  ${medium.source_url} ${medium.width}w,
  ${large.source_url} ${large.width}w,
  ${full.source_url} ${full.width}w,
  `;

  slides[currentImageIndex].image = image.id;
  slides[currentImageIndex].image_url = image.source_url;
  slides[currentImageIndex].image_srcset = imageSrcSet;

  return {
    attributes: {
      slides,
    },
  };
};

export const updateCoverBlockAttributes = (image, currentBlock) => {
  const {id, alt_text, source_url} = image;
  const sizes = image.media_details?.sizes || {};
  const sizeSlug = sizes.full ? 'full' : 'large';
  const div = document.createElement('div');

  if (currentBlock.originalContent) {
    div.innerHTML = currentBlock.originalContent;
    div.querySelector('img').setAttribute('class', `wp-block-cover__image-background wp-image-${id} size-${sizeSlug}`);
    div.querySelector('img').src = source_url;
    div.querySelector('img').setAttribute('alt', alt_text);
  } else {
    div.innerHTML = `
      <div class="wp-block-cover is-light"><img class="wp-block-cover__image-background wp-image-${id} size-${sizeSlug}" alt=${alt_text} src=${source_url} data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim" style="background-color:#FFF"></span><div class="wp-block-cover__inner-container"></div></div>
    `;
  }

  return {
    attributes: {
      alt: alt_text,
      customOverlayColor: '#FFF',
      dimRatio: 50,
      url: source_url,
      id,
      isUserOverlayColor: false,
      isDark: false,
      sizeSlug,
    },
    originalContent: div.innerHTML,
  };
};

/**
 * Updates the attributes of the Topic Link block with image data.
 *
 * This function extracts key information from the provided image object
 * and returns an object containing the corresponding block attributes.
 *
 * @param {Object} image - The image data object.
 *
 * @return {Object} An object containing updated block attributes.
 */
export const updateTopicLinkBlockAttributes = image => {
  const {id, alt_text, source_url} = image;

  return {
    attributes: {
      imageId: id,
      imageAlt: alt_text,
      imageUrl: source_url,
    },
  };
};
