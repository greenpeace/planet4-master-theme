import {
  Button,
} from '@wordpress/components';
const { __ } = wp.i18n;

export const EditableBackgroundActions = ({ image_url, onRemove, onChange }) => (
  <div className='carousel-header-top-image-actions'>
    {image_url && onRemove && (
      <Button
        icon='trash'
        isSecondary
        onClick={ev => {
          onRemove();
          ev.stopPropagation()
        }}
      >
        {__('Remove image', 'planet4-blocks-backend')}
      </Button>
    )}
    <Button
      icon={image_url ? 'edit' : 'plus-alt2'}
      isPrimary
      onClick={onChange}
    >
      {image_url ?
        __('Change image', 'planet4-blocks-backend') :
        __('Add image', 'planet4-blocks-backend')
      }
    </Button>
  </div>
);
