/* global localizations */

document.addEventListener('DOMContentLoaded', () => {
  // Open media modal when clicking on the insert media buttons
  document.querySelectorAll('.insert-media').forEach(button => button.onclick = event => {
    if (typeof wp === 'undefined') {
      return false;
    }

    // These are needed to avoid having 2 modals open.
    event.preventDefault();
    event.stopPropagation();

    const field = button.closest('.form-field');
    const addField = button.closest('.add-wrap');
    const editField = button.closest('.edit-wrap');

    const mediaModal = wp.media({
      title: localizations.media_title,
      library: {
        type: 'image',
      },
      multiple: false,
    });

    mediaModal.on('select', () => {
      const {id, url, sizes} = mediaModal.state().get('selection').first().toJSON();
      field.querySelector('.field-id').value = id;
      field.querySelector('.field-url').value = url;
      if (addField) {
        addField.querySelector('.attachment-thumbnail').src = sizes.thumbnail.url;
      }
      if (editField) {
        editField.querySelector('.attachment-thumbnail').src = url;
      }
      const hiddenDismissIcon = field.querySelector('.dashicons-dismiss.hidden');
      if (hiddenDismissIcon) {
        hiddenDismissIcon.classList.remove('hidden');
      }
    }).open();
  });

  // Open media modal when clicking on the thumbnail images
  document.querySelectorAll('.form-field').forEach(field => {
    const thumbnail = field.querySelector('.attachment-thumbnail');
    if (!thumbnail) {
      return;
    }
    const relatedButton = field.querySelector('.insert-media');
    thumbnail.onclick = () => relatedButton.click();
  });

  // Clean up the custom fields, since the taxonomy save is made via ajax and the taxonomy page does not reload.
  const addTagForm = document.querySelector('#addtag');
  if (addTagForm) {
    addTagForm.querySelector('#addtag').onclick = () => {
      HTMLFormElement.prototype.submit.call(addTagForm);
      setTimeout(() => {
        document.querySelector('#tag_attachment_id').value = '';
        document.querySelector('#tag_attachment').value = '';
        document.querySelector('.form-field .attachment-thumbnail').src = '';
        document.querySelector('.dashicons-dismiss').classList.add('hidden');
      }, 300);
    };
  }

  // Add dismiss dashicons behaviour
  document.querySelectorAll('.dashicons-dismiss').forEach(icon => icon.onclick = () => {
    const field = icon.closest('.form-field');
    field.querySelector('.field-id').value = 0;
    field.querySelector('.field-url').value = '';
    field.querySelector('.attachment-thumbnail').src = '';
    icon.classList.add('hidden');
  });
});
