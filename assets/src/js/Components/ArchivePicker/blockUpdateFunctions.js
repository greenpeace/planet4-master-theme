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

  const updatedAttributes = {
    attributes: {
      id: image.id,
      url: image.source_url,
      caption: image.caption.raw,
      alt: image.alt_text,
    },
    originalContent: div.innerHTML,
  };

  return updatedAttributes;
};

export const updateHappyPointAttributes = id => {
  // Need to update only the id property for the Happy Point block
  return {attributes: {id}};
};
