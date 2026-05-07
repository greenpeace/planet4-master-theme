import {ImagePlaceholder} from './ImagePlaceholder';
import {toSrcSet} from './CarouselHeaderEditor';

const {MediaUpload, MediaUploadCheck} = wp.blockEditor;
const {Button, Dropdown} = wp.components;
const {__, sprintf} = wp.i18n;

// Maximum image file size allowed for carousel header slides (1 MB).
const MAX_IMAGE_FILESIZE_BYTES = 1024 * 1024;

// Resolve the URL for the largest registered (resized) image, never the original upload.
// Falls back gracefully if a size is not available.
const getLargestSizeUrl = image => {
  const sizes = image?.sizes || {};
  return (
    sizes['retina-large']?.url ||
    sizes.large?.url ||
    sizes.medium_large?.url ||
    sizes.medium?.url ||
    // As a last resort use the original URL so the slide is not left empty.
    image?.url
  );
};

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
        const {id, alt_text, sizes, filesizeInBytes, fileLength} = image;
        const fileSize = filesizeInBytes ?? fileLength ?? 0;

        if (fileSize > MAX_IMAGE_FILESIZE_BYTES) {
          // eslint-disable-next-line no-alert
          window.alert(sprintf(
            // translators: %s is the maximum allowed image size in megabytes.
            __('The selected image is too large. Please use an image smaller than %s MB for the carousel header.', 'planet4-master-theme-backend'),
            (MAX_IMAGE_FILESIZE_BYTES / (1024 * 1024)).toFixed(0)
          ));
          return;
        }

        // Use the largest registered size instead of the original image so we never serve a multi-MB upload to the front end.
        const resizedUrl = getLargestSizeUrl(image);
        changeSlideImage(index, id, resizedUrl, alt_text, toSrcSet(Object.values(sizes)));
      }}
      allowedTypes={['image/jpeg', 'image/webp']}
      value={image_id}
      title={__('Select or Upload Photo (only jpg/webp, max 1 MB)', 'planet4-master-theme-backend')}
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
