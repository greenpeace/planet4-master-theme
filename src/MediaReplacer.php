<?php

namespace P4\MasterTheme;

/**
 * Class MediaReplacer.
 *
 * This class is used to handle blocks configuration.
 */
class MediaReplacer
{
    /**
     * Activator constructor.
     */
    public function __construct()
    {
        add_action('admin_footer', [$this, 'enqueue_media_modal_script']);
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
        add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']); // AJAX action for replacing media
    }

    public function ajax_replace_media() {
        // Check if the attachment ID and file are set
        if (isset($_POST['attachment_id']) && !empty($_FILES['file'])) {
            $attachment_id = intval($_POST['attachment_id']);
    
            // Handle the uploaded file
            $file = $_FILES['file'];
            $upload_overrides = array('test_form' => false); // Avoid form tests
    
            // Upload the file
            $movefile = wp_handle_upload($file, $upload_overrides);
    
            if ($movefile && !isset($movefile['error'])) {
                // Replace the media image
                $this->replace_media_image($attachment_id, $movefile['file']); // Pass the new file path
                wp_send_json_success(); // Send a success response
            } else {
                wp_send_json_error($movefile['error']); // Error response
            }
        } else {
            wp_send_json_error('Attachment ID or file is missing.'); // Error response
        }
    }    

    function enqueue_media_modal_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $(document).on('click', '.custom-button', function(e) {
                    e.preventDefault();
                    var attachmentId = $(this).data('attachment-id');
                    var fileInput = $(this).siblings('.replace-media-file');
    
                    // Show the file input
                    fileInput.trigger('click');
    
                    // When a file is selected
                    fileInput.on('change', function() {
                        var file = fileInput[0].files[0]; // Get the selected file
    
                        if (file) {
                            var formData = new FormData();
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
                                success: function(response) {
                                    if (response.success) {
                                        location.reload(true); // Reload the current page
                                    } else {
                                        alert('Error: ' + response.data); // Show the error message
                                    }
                                },
                                error: function(xhr, status, error) {
                                    alert('Error: ' + error); // Error message
                                }
                            });
                        }
                    });
                });
            });
        </script>
        <?php
    }    
    
    function add_replace_media_button($form_fields, $post) {
        // Check if the post type is 'attachment'
        if ($post->post_type === 'attachment') {
            $form_fields['replace_media_button'] = array(
                'input' => 'html',
                'html' => '
                    <button type="button" class="button custom-button" data-attachment-id="' . esc_attr($post->ID) . '">Replace Media</button>
                    <input type="file" class="replace-media-file" style="display: none;" accept="image/*" />
                ',
            );
        }
        return $form_fields;
    }    

    function replace_media_image($old_image_id, $new_file_path) {
        // Get the old image path
        $old_image_path = get_attached_file($old_image_id);
    
        // Check if the old image exists
        if (file_exists($old_image_path)) {
            unlink($old_image_path); // Delete the old image
        }
    
        // Move the new file to the old image's location
        rename($new_file_path, $old_image_path);
    
        // Update the attachment metadata with new information
        $filetype = wp_check_filetype($old_image_path);
        $attachment_data = array(
            'ID' => $old_image_id,
            'post_mime_type' => $filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($old_image_path)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
    
        // Update the database record for the image
        wp_update_post($attachment_data);
    
        // Update image metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($old_image_id, $old_image_path);
        wp_update_attachment_metadata($old_image_id, $attach_data);
    }    
}
