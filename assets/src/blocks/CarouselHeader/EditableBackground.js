import {ImagePlaceholder} from './ImagePlaceholder';
import {toSrcSet} from './CarouselHeaderEditor';

const {MediaUpload, MediaUploadCheck} = wp.blockEditor;
const {Button, Dropdown} = wp.components;
const {__} = wp.i18n;

// MIME types accepted for carousel header slide images. `allowedTypes` on MediaUpload only filters the Media
// Library grid; drag-and-drop and the "Upload files" tab can still bring in other types, so we re-check here.
const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/webp'];

// Resolve the URL for the largest registered (resized) image, never the original upload.
// Falls back gracefully if a size is not available.
export const getLargestSizeUrl = image => {
  const sizes = image?.sizes || image?.media_details?.sizes || {};
  const pickUrl = size => size?.url || size?.source_url;
  return (
    pickUrl(sizes['retina-large']) ||
    pickUrl(sizes.large) ||
    pickUrl(sizes.medium_large) ||
    pickUrl(sizes.medium) ||
    // As a last resort use the original URL so the slide is not left empty.
    image?.url ||
    image?.source_url
  );
};

// Check if an image is very large.
const isValidSize = key =>
  key !== 'full' &&
  key !== 'scaled' &&
  key !== 'original';

// Remove very large images from the SRC set.
export const getSrcSetSizes = (sizes = {}) =>
  Object.entries(sizes)
    .filter(([key, value]) => isValidSize(key) && value?.source_url)
    .map(([, value]) => value);

export const EditableBackground = ({
  image_url,
  image_alt,
  image_id,
  image_srcset,
  index,
  focalPoints,
  changeSlideImage,
  updateCurrentImageIndex,
  addSlide,
  removeSlide,
  slides,
}) => (
  <MediaUploadCheck>
    <MediaUpload
      onSelect={image => {
        const {id, alt_text, sizes} = image;
        const mimeType = image?.mime ?? image?.mime_type ?? (image?.subtype && `image/${image.subtype}`) ?? '';

        // Reject anything that is not JPG / WebP. Defends against PNGs and other types that can slip in
        // via drag-and-drop, the Upload tab, or pre-existing entries in the Media Library.
        if (!ALLOWED_MIME_TYPES.includes(mimeType)) {
          // eslint-disable-next-line no-alert
          window.alert(__(
            'Only JPG and WebP images are allowed for the Carousel Header. Please choose a different file.',
            'planet4-master-theme-backend'
          ));
          return;
        }

        // Use the largest registered size instead of the original image so we never serve a multi-MB upload to the front end.
        const resizedUrl = getLargestSizeUrl(image);
        changeSlideImage(index, id, resizedUrl, alt_text, toSrcSet(getSrcSetSizes(sizes)));
      }}
      allowedTypes={ALLOWED_MIME_TYPES}
      value={image_id}
      title={__('Select or Upload Photo (jpg/webp only)', 'planet4-master-theme-backend')}
      render={mediaUploadInstance => (
        <>
          <div className="background-holder">
            {!image_url ?
              <ImagePlaceholder /> :
              <img
                alt={image_alt}
                src={image_url}
                srcSet={image_srcset}
                style={{objectPosition: `${focalPoints?.x * 100}% ${focalPoints?.y * 100}%`}}
              />
            }
          </div>
          <Dropdown
            placement="bottom left"
            className="carousel-header-editor-controls"
            renderToggle={({onToggle}) => (
              <Button
                variant="primary"
                icon="edit"
                onClick={onToggle}
              >
                {__('Edit', 'planet4-master-theme-backend')}
              </Button>
            )}
            renderContent={({onToggle}) => (
              <div className="carousel-header-editor-controls-menu">
                <Button
                  icon={image_url ? 'edit' : 'plus-alt2'}
                  onClick={() => {
                    mediaUploadInstance.open();
                    onToggle();
                    updateCurrentImageIndex(index);
                  }}
                >
                  {image_url ?
                    __('Change image', 'planet4-master-theme-backend') :
                    __('Add image', 'planet4-master-theme-backend')
                  }
                </Button>
                {image_url && (
                  <Button
                    icon="trash"
                    onClick={() => {
                      changeSlideImage(index, null, '', '');
                      onToggle();
                    }}
                  >
                    {__('Remove image', 'planet4-master-theme-backend')}
                  </Button>
                )}
                {slides.length < 4 && (
                  <Button
                    icon="plus-alt2"
                    onClick={() => {
                      addSlide();
                      onToggle();
                    }}
                  >
                    {__('Add slide', 'planet4-master-theme-backend')}
                  </Button>
                )}
                {slides.length > 1 &&
                  <Button
                    icon="trash"
                    onClick={() => {
                      removeSlide();
                      onToggle();
                    }}
                  >
                    {__('Remove slide', 'planet4-master-theme-backend')}
                  </Button>
                }
              </div>
            )}
          />
        </>
      )}
    />
  </MediaUploadCheck>
);
