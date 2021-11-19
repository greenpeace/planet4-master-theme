import { Button } from '@wordpress/components';
const { __ } = wp.i18n;

export const ImageHoverControls = props => {
  const {
    onEdit,
    onRemove,
    isCompact,
  } = props;

  return <div className="buttons-overlay">
    <Button
      onClick={ onEdit }
      icon="edit"
      isPrimary
      className="edit-image"
    >
      { !isCompact && __('Edit', 'planet4-blocks-backend') }
    </Button>
    <Button
      className="remove-image"
      onClick={ onRemove }
      icon="trash"
    />
  </div>;
};
