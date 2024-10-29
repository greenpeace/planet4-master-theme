/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
import {getCaptionWithCredits} from './getCaptionWithCredits.js';

export const GalleryLightbox = ({image, index, imgSizes}) => {
  const updateCaption = () => {
    const galleryCaption = document.querySelector('.gallery-caption');
    galleryCaption.textContent = (image.caption || image.credits) ?? getCaptionWithCredits(image);
  };

  const context = {
    uploadedSrc: image.image_src,
    figureClassNames: 'wp-block-image',
    figureStyles: null,
    imgStyles: null,
    targetWidth: 2500,
    targetHeight: 1667,
    scaleAttr: false,
    ariaLabel: 'Enlarge image',
    alt: image.alt_text,
    style: {objectPosition: image.focus_image},
  };

  return (
    <figure
      data-wp-context={JSON.stringify(context)}
      data-wp-interactive="core/image"
      className="wp-lightbox-container"
    >
      <img
        loading="lazy"
        decoding="async"
        data-wp-init="callbacks.setButtonStyles"
        data-wp-on-async--click="actions.showLightbox"
        data-wp-on-async--load="callbacks.setButtonStyles"
        data-wp-on-async-window--resize="callbacks.setButtonStyles"
        data-index={index}
        src={image.image_src}
        alt={image.alt_text}
        srcSet={image.image_srcset}
        sizes={imgSizes.grid}
        title={image.alt_text}
        style={{objectPosition: image.focus_image}}
        onClick={updateCaption}
        onKeyDown={event => {
          if (event.key === 'Enter' || event.key === ' ') {
            updateCaption();
          }
        }}
      />
      <button
        className="lightbox-trigger"
        type="button"
        aria-haspopup="dialog"
        aria-label="Enlarge image"
        data-wp-init="callbacks.initTriggerButton"
        data-wp-on-async--click="actions.showLightbox"
        style={{right: '10px', top: '10px'}}
        onClick={updateCaption}
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 12 12">
          <path fill="#fff" d="M2 0a2 2 0 0 0-2 2v2h1.5V2a.5.5 0 0 1 .5-.5h2V0H2Zm2 10.5H2a.5.5 0 0 1-.5-.5V8H0v2a2 2 0 0 0 2 2h2v-1.5ZM8 12v-1.5h2a.5.5 0 0 0 .5-.5V8H12v2a2 2 0 0 1-2 2H8Zm2-12a2 2 0 0 1 2 2v2h-1.5V2a.5.5 0 0 0-.5-.5H8V0h2Z"></path>
        </svg>
      </button>
    </figure>
  );
};
