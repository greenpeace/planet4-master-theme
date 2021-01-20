import {
  Dashicon,
  Button,
} from '@wordpress/components';

export const EditableBackgroundActions = ({ image_url, onRemove, onChange }) => (
  <div className="carousel-header-top-image-actions">
    {image_url && onRemove && (
      <Button isSecondary className="carousel-header-image-upload-remove" onClick={ev => {
          onRemove();
          ev.stopPropagation()
        }}>
        Remove this image
      </Button>
    )}
    <Button className="carousel-header-image-upload-change" isPrimary onClick={onChange}>
      <Dashicon icon="dashicons-format-image"/>
      Change image
    </Button>
  </div>
);
