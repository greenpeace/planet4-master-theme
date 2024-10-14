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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_modal_script']);
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
        add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']); // AJAX action for replacing media
    }

    function enqueue_media_modal_script() {
        if (!wp_script_is('jquery', 'enqueued')) {
            wp_enqueue_script('jquery');
        }
    
        wp_enqueue_script(
            'custom-media-replacer',
            get_template_directory_uri() . '/admin/js/media_replacer.js',
            ['jquery'],
            Loader::theme_file_ver("admin/js/media_replacer.js"),
            true
        );
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
