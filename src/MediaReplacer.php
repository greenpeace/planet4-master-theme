<?php

namespace P4\MasterTheme;

use P4\MasterTheme\CloudflarePurger;

/**
 * Class MediaReplacer.
 *
 * This class is used to replace media elements in WordPress.
 */
class MediaReplacer
{
    protected CloudflarePurger $cloud_flare_purger;

    /**
     * MediaReplacer constructor.
     */
    public function __construct()
    {
        $this->cloud_flare_purger = new CloudflarePurger();

        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_modal_script']);
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
        add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']);
        add_action('admin_notices', [$this, 'display_admin_notices']);
    }

    /**
     * Enqueues the JavaScript required for the media replacement modal.
     */
    public function enqueue_media_modal_script() {
        wp_enqueue_script(
            'custom-media-replacer',
            get_template_directory_uri() . '/admin/js/media_replacer.js',
            [],
            Loader::theme_file_ver("admin/js/media_replacer.js"),
            true
        );
    }

    /**
     * Adds a button to replace media files in the attachment edit form.
     *
     * @param array $form_fields The existing form fields for the attachment.
     * @param WP_Post $post The current attachment post object.
     * @return array Modified form fields with the replace media button added.
     */
    public function add_replace_media_button($form_fields, $post) {
        // Check if the post type is 'attachment' and exclude image mime types
        $image_mime_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            'image/tiff',
            'image/svg+xml'
        ];

        if ($post->post_type === 'attachment' && !in_array($post->post_mime_type, $image_mime_types)) {
            $form_fields['replace_media_button'] = array(
                'input' => 'html',
                'html' => '
                    <button type="button" class="button media-replacer-button" data-attachment-id="' . esc_attr($post->ID) . '">Replace Media</button>
                    <input type="file" class="replace-media-file" style="display: none;" accept="*/*" />
                ',
            );
        }
        return $form_fields;
    }

    /**
     * Handles the AJAX request for replacing media files.
     * Checks for the attachment ID and uploaded file, uploads the file, 
     * and replaces the old media file with the new one.
     */
    public function ajax_replace_media() {
        // Check if the attachment ID and file are set
        if (isset($_POST['attachment_id']) && !empty($_FILES['file'])) {
            $attachment_id = intval($_POST['attachment_id']);
    
            // Handle the uploaded file
            $file = $_FILES['file'];
            $upload_overrides = array('test_form' => false);
    
            // Upload the file
            $movefile = wp_handle_upload($file, $upload_overrides);
    
            if ($movefile && !isset($movefile['error'])) {
                // Replace the media file
                $this->replace_media_file($attachment_id, $movefile['file']);
    
                // Set a success message
                set_transient('media_replacement_message', 'Media replaced successfully!', 5);
                wp_send_json_success();
            } else {
                // Set an error message
                set_transient('media_replacement_error', $movefile['error'], 5);
                wp_send_json_error($movefile['error']);
            }
        } else {
            // Set an error message
            set_transient('media_replacement_error', 'Attachment ID or file is missing.', 5);
            wp_send_json_error('Attachment ID or file is missing.');
        }
    }

    /**
     * Displays admin notices for media replacement messages.
     */
    public function display_admin_notices() {
        // Check for success message
        if ($message = get_transient('media_replacement_message')) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            delete_transient('media_replacement_message');
        }

        // Check for error message
        if ($error = get_transient('media_replacement_error')) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
            delete_transient('media_replacement_error');
        }
    }

    /**
     * Replaces the media file associated with the old attachment ID 
     * with the new file located at the specified path.
     *
     * @param int $old_file_id The ID of the old attachment.
     * @param string $new_file_path The path to the new file.
     */
    private function replace_media_file($old_file_id, $new_file_path) {
        // Get the old file path
        $old_file_path = get_attached_file($old_file_id);

        // Check if the old file exists
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }

        // Move the new file to the old file's location
        rename($new_file_path, $old_file_path);

        // Update the attachment metadata with new information
        $filetype = wp_check_filetype($old_file_path);
        $attachment_data = array(
            'ID' => $old_file_id,
            'post_mime_type' => $filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($old_file_path)),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Update the database record for the file
        wp_update_post($attachment_data);

        // Update file metadata
        // By calling the "wp_update_attachment_metadata" function, the WP Stateless plugin syncs the file with Google Storage.
        // https://github.com/udx/wp-stateless/blob/0871da645453240007178f4a5f243ceab6a188ea/lib/classes/class-bootstrap.php#L376
        $attach_data = wp_generate_attachment_metadata($old_file_id, $old_file_path);
        wp_update_attachment_metadata($old_file_id, $attach_data);
    }
}
