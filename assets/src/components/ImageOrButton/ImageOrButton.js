import {Button} from '@wordpress/components';
import {MediaUpload, MediaUploadCheck} from '@wordpress/block-editor';

export const ImageOrButton = props => {
  const {
    disabled = false,
    imageId,
    imageUrl,
    imgClass,
    buttonLabel,
    help,
    title,
    onSelectImage,
  } = props;

  const getImageOrButton = openEvent => {
    if (imageId) {
      return (
        <img
          src={imageUrl}
          onClick={openEvent}
          className={imgClass}
          alt=""
          role="presentation"
        />

      );
    }

    return (
      <div className="button-container">
        <Button
          onClick={openEvent}
          className="button"
          disabled={disabled}>
          { buttonLabel }
        </Button>

        <div>{ help }</div>
      </div>
    );
  };

  return <div className="ImageOrButton">
    <MediaUploadCheck>
      <MediaUpload
        title={title}
        type="image"
        onSelect={onSelectImage}
        value={imageId}
        allowedTypes={['image']}
        render={({open}) => getImageOrButton(open)}
      />
    </MediaUploadCheck>
  </div>;
};
