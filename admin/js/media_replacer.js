/* global ajaxurl */

/**
 * JavaScript implementation for replacing media files in the WordPress admin.
 */
document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', e => {
    if (e.target.classList.contains('media-replacer-button')) {
      e.preventDefault();
      const attachmentId = e.target.dataset.attachmentId;
      const fileInput = e.target.nextElementSibling;

      // Show the file input
      fileInput.click();

      // When a file is selected
      fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];

        if (file) {
          const formData = new FormData();
          formData.append('action', 'replace_media');
          formData.append('attachment_id', attachmentId);
          formData.append('file', file);

          const replaceMediaButton = document.querySelector('.media-replacer-button');

          if (replaceMediaButton) {
            replaceMediaButton.disabled = true;
            replaceMediaButton.innerText = 'Replacing media, please wait...';
          }

          // Send AJAX request to replace the media
          fetch(ajaxurl, {
            method: 'POST',
            body: formData,
          })
            .then(response => response.json())
            .then(response => {
              if (response.success) {
                location.reload(true);
              } else {
                // eslint-disable-next-line no-alert
                alert('Error: ' + response.data);
              }
            })
            .catch(error => {
              if (replaceMediaButton) {
                replaceMediaButton.disabled = false;
                replaceMediaButton.innerText = 'Replace Media';
              }

              // eslint-disable-next-line no-alert
              alert('Error: ' + error);
            });
        }
      }, {once: true}); // Use the 'once' option to ensure the event is only handled once
    }
  });
});
