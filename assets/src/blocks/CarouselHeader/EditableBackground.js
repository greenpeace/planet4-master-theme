import {MediaUpload} from "@wordpress/block-editor";
import {ImagePlaceholder} from './ImagePlaceholder';
import {EditableBackgroundActions} from './EditableBackgroundActions';

export const EditableBackground = ({
  image_url,
  image_id,
  index,
  slides,
  setAttributes,
  focalPoints,
  children,
}) => {
  function onImageChange(index, value) {
    let slidesCopy = JSON.parse(JSON.stringify(slides));
    slidesCopy[index].image = value;
    setAttributes({slides: slidesCopy});
  }

  const onChange = (newImage) => onImageChange(index, newImage.id);
  const onRemove = () => onImageChange(index, null);

  return (
    <MediaUpload
      onSelect={onChange}
      allowedTypes={['image']}
      value={image_id}
      render={mediaUploadInstance => {
        return (
          <>
            <div className="background-holder" style={{
                backgroundImage: `url(${image_url ? image_url : ''})`,
                backgroundPosition: `${focalPoints?.x*100}% ${focalPoints?.y*100}%`,
              }}>
              { !image_url && <ImagePlaceholder/> }
              { children }
            </div>

            <EditableBackgroundActions
              image_url={image_url}
              onRemove={onRemove}
              onChange={mediaUploadInstance.open}
            />
          </>
        )
      }}
    />
  );
};
