const {Button} = wp.components;
const {__} = wp.i18n;

export const ImageHoverControls = props => {
  const {
    onEdit,
    onRemove,
    isCompact,
    isAdd,
  } = props;

  return <div className="buttons-overlay">
    { isAdd && <Button
      onClick={onEdit}
      icon="plus-alt2"
      isPrimary
      className="edit-image"
    >
      { __('Add image', 'planet4-blocks-backend') }
    </Button> }

    { !isAdd && <Button
      onClick={onEdit}
      icon="edit"
      isPrimary
      className="edit-image"
    >
      { !isCompact && __('Edit', 'planet4-blocks-backend') }
    </Button> }
    { !isAdd && <Button
      className="remove-image"
      onClick={onRemove}
      icon="trash"
    /> }
  </div>;
};
