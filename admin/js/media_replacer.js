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

    // Store a reference to the specific button that was clicked
    const clickedButton = e.target;
    const attachmentId = clickedButton.dataset.attachmentId;
    const fileInput = clickedButton.nextElementSibling;

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

      // Use the specific button reference to disable/change text
      clickedButton.disabled = true;
      clickedButton.innerText = 'Replacing media, please wait...';

      // Send AJAX request to replace the media
      fetch(ajaxurl, {
        method: 'POST',
        body: formData,
      })
        .then(response => response.json())
        .then(response => {
          if (!response.success) {
            // Re-enable and reset text only for the failed button
            clickedButton.disabled = false;
            clickedButton.innerText = 'Replace Media';
            alert('Error: ' + response.data); // eslint-disable-line no-alert
            return;
          }

          // Reload the page once the replacement is completed.
          location.replace(location.href);
        })
        .catch(error => {
          clickedButton.disabled = false;
          clickedButton.innerText = 'Replace Media';
          alert('Error: ' + error); // eslint-disable-line no-alert
        });
    }, {once: true}); // Use the 'once' option to ensure the event is only handled once
  });
});
