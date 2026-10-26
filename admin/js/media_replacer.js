/* global ajaxurl, p4MediaReplacer */

/**
 * JavaScript implementation for replacing media files in the WordPress admin.
 * Ensures that the correct nonce is used and handles the file upload process.
 */
document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', e => {
    const replaceButtonClass = 'media-replacer-button';

    if (!e.target.classList.contains(replaceButtonClass)) {
      return;
    }

    e.preventDefault();

    // Store a reference to the specific button that was clicked
    const clickedButton = e.target;
    const attachmentId = clickedButton.dataset.attachmentId;
    const fileInput = clickedButton.nextElementSibling;

    if (typeof p4MediaReplacer === 'undefined' || !p4MediaReplacer.nonce) {
      alert('Error: Media replacement is not available, please reload the page and try again.'); // eslint-disable-line no-alert
      return;
    }

    // Show the file input
    fileInput.click();

    // When a file is selected
    fileInput.onchange = () => {
      const file = fileInput.files[0];
      if (!file) {
        return;
      }

      const formData = new FormData();
      formData.append('action', 'replace_media');
      formData.append('nonce', p4MediaReplacer.nonce);
      formData.append('attachment_id', attachmentId);
      formData.append('file', file);

      // Use the specific button reference to disable/change text
      clickedButton.disabled = true;
      clickedButton.innerText = 'Replacing media, please wait...';

      // Restores the button after a failure and clears the input so that choosing the same file again
      // still fires a "change" event
      const resetButton = () => {
        clickedButton.disabled = false;
        clickedButton.innerText = 'Replace Media';
        fileInput.value = '';
      };

      fetch(p4MediaReplacer.ajaxUrl || ajaxurl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      })
        .then(response => {
          if (response.status === 403) {
            return response.json().catch(() => null).then(body => {
              const message = body && body.data ?
                body.data :
                'You do not have permission to replace this file, or your session has expired. Please reload the page and try again.';
              throw new Error(message);
            });
          }

          return response.json();
        })
        .then(response => {
          if (!response.success) {
            resetButton();
            alert('Error: ' + response.data); // eslint-disable-line no-alert
            return;
          }

          // Reload the page once the replacement is completed.
          location.replace(location.href);
        })
        .catch(error => {
          resetButton();
          alert('Error: ' + (error.message || error)); // eslint-disable-line no-alert
        });
    };
  });
});
