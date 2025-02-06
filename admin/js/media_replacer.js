/* global ajaxurl */

/**
 * JavaScript implementation for replacing media files in the WordPress admin.
 */
document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', e => {
    const replaceButtonClass = 'media-replacer-button';

    if (!e.target.classList.contains(replaceButtonClass)) {
      return;
    }

    e.preventDefault();

    const attachmentId = e.target.dataset.attachmentId;
    const fileInput = e.target.nextElementSibling;

    // Show the file input
    fileInput.click();

    // When a file is selected
    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (!file) {
        return;
      }

      const formData = new FormData();
      formData.append('action', 'replace_media');
      formData.append('attachment_id', attachmentId);
      formData.append('file', file);

      const replaceMediaButton = document.querySelector(`.${replaceButtonClass}`);
      if (replaceMediaButton) {
        replaceMediaButton.disabled = true;
        replaceMediaButton.innerText = 'Replacing media, please wait...';
      }

      // Send AJAX request to replace the media
      fetch(ajaxurl, {
        method: 'POST',
        body: formData,
      })
        .then(response => {
          response.json();
          location.reload(true);
        })
        .catch(() => {
          if (replaceMediaButton) {
            replaceMediaButton.disabled = false;
            replaceMediaButton.innerText = 'Replace Media';
          }
        });
    }, {once: true}); // Use the 'once' option to ensure the event is only handled once
  });
});
