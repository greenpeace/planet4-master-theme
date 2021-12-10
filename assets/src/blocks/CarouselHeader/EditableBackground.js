import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { ImagePlaceholder } from './ImagePlaceholder';
import { Button, Dropdown } from '@wordpress/components';
import { toSrcSet } from './CarouselHeaderEditor';
const { __ } = wp.i18n;

const usesCdn = window.p4ge_vars.uses_cdn;

// If the site uses CDN through the stateless plugin, it should never have the local folder in the URL.
const isWrongSource = src => {
  return usesCdn && src.includes('wp-content/uploads');
}

const ALERT_TEXT = `
An issue with the image path was detected. This usually happens on the largest version of the image.
Please try removing and re-adding the images until you don't get this alert anymore.
If the alert persists, please provide the prior steps you did (including upload of image) to the Planet 4 team.
`;

// Due to a yet unidentified bug, it sometimes happens that the URL and the largest size don't use the CDN URL.
// This has a nasty consequence: the URL on the server exists, but only temporary. It is removed on deploy. So an editor
// can not see that this issue will occur. It's probably related to WPML.
const preventLocalImage = (url, sizes) => {
  const fixedSizes = sizes.filter(size => !isWrongSource(size.url || size.source_url));
  let fixedUrl = url;
  if (isWrongSource(url)) {
    console.warn(`An image with "wp-content-uploads" in the URL was detected, excluding: ${url}`);
    alert(ALERT_TEXT);
    const largest = fixedSizes.reduce((largest, size) => {
      if (!largest) {
        return size;
      }
      return size.width < largest.width ? largest : size;
    })
    fixedUrl = largest.url;
  }
  return [fixedUrl, fixedSizes];
}

export const EditableBackground = ({
  image_url,
  image_alt,
  image_id,
  image_srcset,
  index,
  focalPoints,
  changeSlideImage,
  addSlide,
  removeSlide,
  slides,
}) => (
  <MediaUploadCheck>
    <MediaUpload
      onSelect={image => {
        const { id, alt_text, url, sizes } = image;
        const [fixedUrl, fixedSizes] = preventLocalImage(url, Object.values(sizes));
        changeSlideImage(index, id, fixedUrl, alt_text, toSrcSet(fixedSizes));
      }}
      allowedTypes={['image']}
      value={image_id}
      render={mediaUploadInstance => (
        <>
          <div className='background-holder'>
            {!image_url ?
              <ImagePlaceholder /> :
              <img
                alt={image_alt}
                src={image_url}
                srcSet={image_srcset}
                style={{ objectPosition: `${focalPoints?.x * 100}% ${focalPoints?.y * 100}%` }}
              />
            }
          </div>
          <Dropdown
            position='bottom left'
            className='carousel-header-editor-controls'
            renderToggle={({ onToggle }) => (
              <Button
                isPrimary
                icon='edit'
                onClick={onToggle}
              >
                {__('Edit', 'planet4-blocks-backend')}
              </Button>
            )}
            renderContent={({ onToggle }) => (
              <div className='carousel-header-editor-controls-menu'>
                <Button
                  icon={image_url ? 'edit' : 'plus-alt2'}
                  onClick={() => {
                    mediaUploadInstance.open();
                    onToggle();
                  }}
                >
                  {image_url ?
                    __('Change image', 'planet4-blocks-backend') :
                    __('Add image', 'planet4-blocks-backend')
                  }
                </Button>
                {image_url && (
                  <Button
                    icon='trash'
                    onClick={() => {
                      changeSlideImage(index, null, '', '');
                      onToggle();
                    }}
                  >
                    {__('Remove image', 'planet4-blocks-backend')}
                  </Button>
                )}
                {slides.length < 4 && (
                  <Button
                    icon='plus-alt2'
                    onClick={() => {
                      addSlide();
                      onToggle();
                    }}
                  >
                    {__('Add slide', 'planet4-blocks-backend')}
                  </Button>
                )}
                {slides.length > 1 &&
                  <Button
                    icon='trash'
                    onClick={() => {
                      removeSlide();
                      onToggle();
                    }}
                  >
                    {__('Remove slide', 'planet4-blocks-backend')}
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
