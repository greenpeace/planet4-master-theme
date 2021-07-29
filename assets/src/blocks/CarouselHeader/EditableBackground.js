import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { ImagePlaceholder } from './ImagePlaceholder';
import { Button, Dashicon } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
const { __ } = wp.i18n;

export const EditableBackground = ({
  image_url,
  image_id,
  index,
  focalPoints,
  changeSlideAttribute,
  addSlide,
  removeSlide,
  slides,
}) => {
  const [openMenu, setOpenMenu] = useState(false);
  const menuRef = useRef(null);

  const handleOutsideClick = e => {
    if (menuRef.current && !menuRef.current.contains(e.target)) {
      setOpenMenu(false);
    }
  };

  // Close menu on outside click
  useEffect(() => {
    document.addEventListener('click', handleOutsideClick);

    return () => document.removeEventListener('click', handleOutsideClick);
  });

  return (
    <MediaUploadCheck>
      <MediaUpload
        onSelect={image => {
          const { id, alt, url } = image;
          changeSlideAttribute('image', index)(id);
          changeSlideAttribute('image_alt', index)(alt);
          changeSlideAttribute('image_url', index)(url);
        }}
        allowedTypes={['image']}
        value={image_id}
        render={mediaUploadInstance => (
          <>
            <div className='background-holder'>
              {!image_url ?
                <ImagePlaceholder /> :
                <img
                  src={image_url}
                  style={{ objectPosition: `${focalPoints?.x * 100}% ${focalPoints?.y * 100}%` }}
                />
              }
            </div>

            <div className='carousel-header-editor-controls'>
              <div ref={element => menuRef.current = element} className='carousel-header-editor-controls-menu'>
                <Button
                  isPrimary
                  icon='edit'
                  onClick={() => setOpenMenu(!openMenu)}
                >
                  {__('Edit', 'planet4-blocks-backend')}
                </Button>
                {openMenu &&
                  <ul>
                    <li onClick={mediaUploadInstance.open}>
                      <Dashicon icon={image_url ? 'edit' : 'plus-alt2'} />
                      {image_url ?
                        __('Change image', 'planet4-blocks-backend') :
                        __('Add image', 'planet4-blocks-backend')
                      }
                    </li>
                    {image_url && (
                      <li onClick={() => changeSlideAttribute('image', index)(null)}>
                        <Dashicon icon='trash' />
                        {__('Remove image', 'planet4-blocks-backend')}
                      </li>
                    )}
                    {slides.length < 4 &&
                      <li onClick={addSlide}>
                        <Dashicon icon='plus-alt2' />
                        {__('Add slide', 'planet4-blocks-backend')}
                      </li>
                    }
                    {slides.length > 1 &&
                      <li onClick={removeSlide}>
                        <Dashicon icon='trash' />
                        {__('Remove slide', 'planet4-blocks-backend')}
                      </li>
                    }
                  </ul>
                }
              </div>
            </div>
          </>
        )}
      />
    </MediaUploadCheck>
  );
}
