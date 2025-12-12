import {ImagePlaceholder} from './ImagePlaceholder';
import {toSrcSet} from './CarouselHeaderEditor';

const {MediaUpload, MediaUploadCheck} = wp.blockEditor;
const {Button, Dropdown} = wp.components;
const {__} = wp.i18n;

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
        const {id, alt_text, url, sizes} = image;
        changeSlideImage(index, id, url, alt_text, toSrcSet(Object.values(sizes)));
      }}
      allowedTypes={['image/jpg', 'image/jpeg']}
      value={image_id}
      title={'Select or Upload Photo (only jpg/jpeg)'}
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
