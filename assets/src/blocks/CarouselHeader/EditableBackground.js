import { MediaUpload } from '@wordpress/block-editor';
import { ImagePlaceholder } from './ImagePlaceholder';
import { EditorControls } from './EditorControls';

export const EditableBackground = ({
  image_url,
  image_id,
  index,
  focalPoints,
  changeSlideAttribute,
  addSlide,
  removeSlide,
  slides,
}) => (
  <MediaUpload
    onSelect={({ id }) => changeSlideAttribute('image', index)(id)}
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

        <EditorControls
          image_url={image_url}
          removeImage={() => changeSlideAttribute('image', index)(null)}
          changeImage={mediaUploadInstance.open}
          addSlide={addSlide}
          removeSlide={removeSlide}
          slides={slides}
        />
      </>
    )}
  />
);
