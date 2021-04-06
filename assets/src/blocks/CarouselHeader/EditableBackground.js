import { MediaUpload } from '@wordpress/block-editor';
import { ImagePlaceholder } from './ImagePlaceholder';
import { EditableBackgroundActions } from './EditableBackgroundActions';

export const EditableBackground = ({
  image_url,
  image_id,
  index,
  focalPoints,
  children,
  changeSlideAttribute,
}) => (
  <MediaUpload
    onSelect={({ id }) => changeSlideAttribute('image', index)(id)}
    allowedTypes={['image']}
    value={image_id}
    render={mediaUploadInstance => (
      <>
        <div
          className='background-holder'
          style={{
            backgroundImage: `url(${image_url || ''})`,
            backgroundPosition: `${focalPoints?.x * 100}% ${focalPoints?.y * 100}%`,
          }}
        >
          {!image_url && <ImagePlaceholder />}
          {children}
        </div>

        <EditableBackgroundActions
          image_url={image_url}
          onRemove={() => changeSlideAttribute('image', index)(null)}
          onChange={mediaUploadInstance.open}
        />
      </>
    )}
  />
);
