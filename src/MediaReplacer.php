<?php

namespace P4\MasterTheme;

use P4\MasterTheme\CloudflarePurger;
use WP_Post;

/**
 * Class MediaReplacer.
 *
 * This class is used to replace media elements in WordPress.
 */
class MediaReplacer
{
    /**
     * MediaReplacer constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_modal_script']);
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
        add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']);
        add_action('admin_notices', [$this, 'display_admin_notices']);
    }

    /**
     * Enqueues the JavaScript required for the media replacement modal.
     */
    public function enqueue_media_modal_script(): void
    {
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
    public function add_replace_media_button(array $form_fields, WP_Post $post): array
    {
        // Check if the post type is 'attachment' and exclude image mime types
        if ($post->post_type !== 'attachment') {
            return $form_fields;
        }

        $image_mime_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            'image/tiff',
            'image/svg+xml',
        ];

        // Check if the post excludes image mime types
        if (in_array($post->post_mime_type, $image_mime_types)) {
            return $form_fields;
        }

        $form_fields['replace_media_button'] = array(
            'input' => 'html',
            'html' => '
                <button 
                    type="button" 
                    class="button media-replacer-button" 
                    data-attachment-id="' . esc_attr($post->ID) . '"
                    data-mime-type="' . esc_attr($post->post_mime_type) . '"
                >
                    Replace Media
                </button>
                <input 
                    type="file" 
                    class="replace-media-file" 
                    style="display: none;" 
                    accept="' . esc_attr($post->post_mime_type) . '" 
                />
            ',
        );

        return $form_fields;
    }

    /**
     * Handles the AJAX request for replacing media files.
     * Checks for the attachment ID and uploaded file, uploads the file,
     * and replaces the old media file with the new one.
     */
    public function ajax_replace_media(): void
    {
        // Check if the attachment ID is set
        if (!isset($_POST['attachment_id'])) {
            set_transient('media_replacement_error', 'Attachment ID is missing.', 5);
            wp_send_json_error('Attachment ID is missing.');
            return;
        }

        // Check if the file is set
        if (empty($_FILES['file'])) {
            set_transient('media_replacement_error', 'File is missing.', 5);
            wp_send_json_error('File is missing.');
            return;
        }

        $attachment_id = intval($_POST['attachment_id']);

        // Handle the uploaded file
        $file = $_FILES['file'];
        $upload_overrides = array('test_form' => false);

        // Upload the file
        $movefile = wp_handle_upload($file, $upload_overrides);

        if (!$movefile) {
            $error_message = isset($movefile['error']) ? $movefile['error'] : 'Media could not be uploaded.';
            set_transient('media_replacement_error', $error_message, 5);
            wp_send_json_error($error_message);
            return;
        }

        $this->replace_media_file($attachment_id, $movefile['file']);
        
        $this->purge_cloudflare(wp_get_attachment_url($attachment_id));

        set_transient('media_replacement_message', 'Media replaced successfully!', 5);

        wp_send_json_success();
    }

    /**
     * Displays admin notices for media replacement messages.
     */
    public function display_admin_notices(): void
    {
        // Helper function to display notices
        $this->render_notice('media_replacement_message', 'success');
        $this->render_notice('media_replacement_error', 'error');
        $this->render_notice('cloudflare_purge_message', 'success');
        $this->render_notice('cloudflare_purge_error', 'error');
    }

    /**
     * Replaces the media file associated with the old attachment ID
     * with the new file located at the specified path.
     *
     * @param int $old_file_id The ID of the old attachment.
     * @param string $new_file_path The path to the new file.
     */
    private function replace_media_file(int $old_file_id, string $new_file_path): void
    {
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
            'post_status' => 'inherit',
        );

        // Update the database record for the file
        wp_update_post($attachment_data);

        // Update file metadata
        // By calling the "wp_update_attachment_metadata" function,
        // the WP Stateless plugin syncs the file with Google Storage.
        // https://github.com/udx/wp-stateless/blob/0871da645453240007178f4a5f243ceab6a188ea/lib/classes/class-bootstrap.php#L376
        $attach_data = wp_generate_attachment_metadata($old_file_id, $old_file_path);
        wp_update_attachment_metadata($old_file_id, $attach_data);
    }

    /**
     * Purge the Cloudflare cache for a specific URL.
     * 
     * @param string $url The URL to be purged.
     */
    private function purge_cloudflare(string $url): void
    {
        $cf = new CloudflarePurger();
        $generator = $cf->purge([$url]);
        $api_responses = [];

        foreach ($generator as $purge_result) {
            list($api_response) = $purge_result;
            $api_responses[] = $api_response;
        }

        if ($api_responses[0]) {
            $message = 'Cloudflare successfully purged URL: ' . $url;
            set_transient('cloudflare_purge_message', $message, 5);
        } else {
            $error_message = 'Cloudflare could not purge URL: ' . $url;
            set_transient('cloudflare_purge_error', $error_message, 5);
        }
    }

    /**
     * Renders admin notices.
     * 
     * @param string $transient_key The Transient key.
     * @param string $type The type of message.
     */
    private function render_notice(string $transient_key, string $type): void
    {
        if ($message = get_transient($transient_key)) {
            $notice_class = $type === 'success' ? 'notice-success' : 'notice-error';
            echo '<div class="notice ' . esc_attr($notice_class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
            delete_transient($transient_key);
        }
    }
}
