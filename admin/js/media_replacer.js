/* global ajaxurl */

jQuery(document).ready($ => {
  $(document).on('click', '.custom-button', function(e) {
    e.preventDefault();
    const attachmentId = $(this).data('attachment-id');
    const fileInput = $(this).siblings('.replace-media-file');

    // Show the file input
    fileInput.trigger('click');

    // When a file is selected
    fileInput.on('change', () => {
      const file = fileInput[0].files[0]; // Get the selected file

      if (file) {
        const formData = new FormData();
        formData.append('action', 'replace_media');
        formData.append('attachment_id', attachmentId);
        formData.append('file', file); // Append the file to FormData

        // Send AJAX request to replace the media
        $.ajax({
          url: ajaxurl, // WordPress AJAX URL
          type: 'POST',
          data: formData,
          contentType: false, // Prevent jQuery from overriding content type
          processData: false, // Prevent jQuery from processing the data
          success(response) {
            if (response.success) {
              location.reload(true); // Reload the current page
            } else {
              alert('Error: ' + response.data); // Show the error message
            }
          },
          error(xhr, status, error) {
            alert('Error: ' + error); // Error message
          },
        });
      }
    });
  });
});
