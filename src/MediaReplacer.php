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
        // Check if the attachment ID is set
        if (isset($_POST['attachment_id'])) {
            $attachment_id = intval($_POST['attachment_id']);
            $this->replace_media_image($attachment_id); // Call the function with the attachment ID
            wp_send_json_success(); // Send a success response
        } else {
            wp_send_json_error('Attachment ID is missing.'); // Error response
        }
    }

    function enqueue_media_modal_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $(document).on('click', '.custom-button', function(e) {
                    e.preventDefault();
                    var attachmentId = $(this).data('attachment-id');
    
                    // Send AJAX request to replace the media
                    $.ajax({
                        url: ajaxurl, // WordPress AJAX URL
                        type: 'POST',
                        data: {
                            action: 'replace_media', // Custom action name
                            attachment_id: attachmentId // Pass the attachment ID
                        },
                        success: function(response) {
                            location.reload(true);
                        },
                        error: function(xhr, status, error) {
                            alert('Error: ' + error); // Error message
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
                'html' => '<button type="button" class="button custom-button" data-attachment-id="' . esc_attr($post->ID) . '">Replace Media</button>',
            );
        }
        return $form_fields;
    }

    function replace_media_image($old_image_id) {
        // Assuming $new_image_id is defined or fetched appropriately
        $new_image_id = 224; // Replace with the new image ID
        
        // Get file paths
        $old_image_path = get_attached_file($old_image_id);
        $new_image_path = get_attached_file($new_image_id);
        
        // Check if the old image exists
        if (file_exists($old_image_path)) {
            unlink($old_image_path); // Delete the old image
        }

        // Check if the new image exists before copying
        if (file_exists($new_image_path)) {
            copy($new_image_path, $old_image_path); // Copy the new image to the old image path

            // Get the image's file type (based on extension)
            $filetype = wp_check_filetype($old_image_path);
    
            // Update the attachment metadata with new information
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

        } else {
            error_log('New image does not exist at ' . $new_image_path); // Log an error if the new image is not found
        }
    }  
}
