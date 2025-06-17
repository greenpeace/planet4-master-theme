export const updateImageBlockAttributes = (image, currentBlock) => {
  const div = document.createElement('div');

  if (image.id) {
    // Check if the current block has content already, then just replace it.
    if (currentBlock.originalContent) {
      div.innerHTML = currentBlock.originalContent;
      div.querySelector('img').setAttribute('class', `wp-image-${image.id}`);
      div.querySelector('img').src = image.source_url;
      if (div.querySelector('figcaption')) {
        div.querySelector('figcaption').textContent = image.caption.raw;
      }
    } else {
      // If there is no content then create block content.
      div.innerHTML = `
        <figure class="wp-block-image size-large">
          <img src=${image.source_url} alt=${image.alt_text} class="wp-image-${image.id}"/>
          <figcaption class="wp-element-caption">
            ${image.caption.raw}
          </figcaption>
        </figure>`;
    }
  }

  return {
    attributes: {
      id: image.id,
      url: image.source_url,
      caption: image.caption.raw,
      alt: image.alt_text,
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

  if (currentBlock.originalContent) {
    div.innerHTML = currentBlock.originalContent;
    div.querySelector('img').setAttribute('class', `wp-image-${image.id}`);
    div.querySelector('img').src = image.source_url;
  } else {
    div.innerHTML = `
      <div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media"><img src=${image.source_url} alt=${image.alt_text} class="wp-image-${image.id} size-full"/></figure><div class="wp-block-media-text__content"></div></div>
    `;
  }

  return {
    attributes: {
      mediaLink: image.link,
      mediaUrl: image.source_url,
      mediaAlt: image.alt_text,
      mediaId: image.id,
      mediaType: 'image',
      useFeaturedImage: false,
    },
    originalContent: div.innerHTML,
  };
};

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
  const sizes = image.media_details?.sizes || {};
  const sizeSlug = sizes.full ? 'full' : 'large';
  const div = document.createElement('div');

  if (currentBlock.originalContent) {
    div.innerHTML = currentBlock.originalContent;
    div.querySelector('img').setAttribute('class', `wp-block-cover__image-background wp-image-${image.id} size-${sizeSlug}`);
    div.querySelector('img').src = image.source_url;
    div.querySelector('img').setAttribute('alt', image.alt_text);
  } else {
    div.innerHTML = `
      <div class="wp-block-cover is-light"><img class="wp-block-cover__image-background wp-image-${image.id} size-${sizeSlug}" alt=${image.alt_text} src=${image.source_url} data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim" style="background-color:#FFF"></span><div class="wp-block-cover__inner-container"></div></div>
    `;
  }

  return {
    attributes: {
      alt: image.alt_text,
      customOverlayColor: '#FFF',
      dimRatio: 50,
      url: image.source_url,
      id: image.id,
      isUserOverlayColor: false,
      isDark: false,
      sizeSlug,
    },
    originalContent: div.innerHTML,
  };
};
