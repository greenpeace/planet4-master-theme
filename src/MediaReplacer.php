<?php

namespace P4\MasterTheme;

use WP_Post;

/**
 * Class MediaReplacer.
 *
 * This class is used to replace media elements in WordPress.
 */
class MediaReplacer
{
    /**
     * List of image MIME types.
     */
    private const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/webp',
        'image/tiff',
        'image/svg+xml',
    ];

    /**
     * Instance of the CloudflarePurger class.
     */
    public CloudflarePurger $cf;

    /**
     * MediaReplacer constructor.
     */
    public function __construct()
    {
        if (function_exists('is_plugin_active') && is_plugin_active('wp-stateless/wp-stateless-media.php')) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_media_modal_script']);
            add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
            add_action('add_meta_boxes', [$this, 'add_replace_media_metabox']);
            add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']);
            add_action('admin_notices', [$this, 'display_admin_notices']);
            // $this->cf = new CloudflarePurger();
        }
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
     * Adds a metabox to the attachments editor.
     */
    public function add_replace_media_metabox(): void
    {
        add_meta_box(
            'replace_media_metabox',
            'Replace Media',
            [$this, 'render_replace_media_metabox'],
            'attachment',
            'side',
            'low'
        );
    }

    /**
     * Adds a button to replace media files in the attachments editor.
     *
     * @param WP_Post $post The current attachment post object.
     */
    public function render_replace_media_metabox(WP_Post $post): void
    {
        // phpcs:ignore Generic.Files.LineLength.MaxExceeded
        $message = __('Use this to replace the current file without changing the file URL.', 'planet4-master-theme-backend');
        echo "<p>" . $message . "</p>";
        echo $this->get_replace_button_html($post);
    }

    /**
     * Adds a button to replace media files to the Attachment details modal.
     *
     * @param array $form_fields The existing form fields for the attachment.
     * @param WP_Post $post The current attachment post object.
     * @return array Modified form fields with the replace media button added.
     */
    public function add_replace_media_button(array $form_fields, WP_Post $post): array
    {
        // Check if the post type is 'attachment'
        if ($post->post_type !== 'attachment') {
            return $form_fields;
        }

        // Check if the current page is an editing page
        // If so, return as a metabox is added to the page
        if (isset($_GET['action']) && $_GET['action'] === 'edit') {
            return $form_fields;
        }

        $form_fields['replace_media_button'] = array(
            'input' => 'html',
            'html' => $this->get_replace_button_html($post),
        );

        return $form_fields;
    }

    /**
     * Renders the HTML of the Replace Media button.
     *
     * @param WP_Post $post The current attachment post object.
     * @return string The button html.
     */
    private function get_replace_button_html(WP_Post $post): string
    {
        return
        '<button
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
        ';
    }

    /**
     * Handles the AJAX request for replacing media files.
     * Checks for the attachment ID and uploaded file, uploads the file,
     * and replaces the old media file with the new one.
     */
    public function ajax_replace_media(): void
    {
        try {
            // Check if the attachment ID is set
            if (!isset($_POST['attachment_id'])) {
                $message = __('Attachment ID is missing.', 'planet4-master-theme-backend');
                set_transient('media_replacement_error', $message, 5);
                wp_send_json_error($message);
                return;
            }

            // Check if the file is set
            if (empty($_FILES['file'])) {
                $message = __('File is missing.', 'planet4-master-theme-backend');
                set_transient('media_replacement_error', $message, 5);
                wp_send_json_error($message);
                return;
            }

            $attachment_id = intval($_POST['attachment_id']);

            // Handle the uploaded file
            $file = $_FILES['file'];
            $upload_overrides = array('test_form' => false);

            // Upload the file
            $movefile = wp_handle_upload($file, $upload_overrides);

            // If the file was not uploaded, abort
            if (!$movefile) {
                $message = __('Media could not be uploaded.', 'planet4-master-theme-backend');
                $error_message = isset($movefile['error']) ? $movefile['error'] : $message;
                set_transient('media_replacement_error', $error_message, 5);
                wp_send_json_error($error_message);
                return;
            }

            $file_replaced = $this->replace_media_file($attachment_id, $movefile['file']);

            // If the file was not replaced, abort
            if (!$file_replaced) {
                $error_message = __('Media file could not be replaced.', 'planet4-master-theme-backend');
                set_transient('media_replacement_error', $error_message, 5);
                wp_send_json_error($error_message);
                return;
            }

            $message = __('Media replaced successfully!', 'planet4-master-theme-backend');
            set_transient('media_replacement_message', $message, 5);
            wp_send_json_success();
        } catch (\Exception $e) {
            set_transient('media_replacement_error', $e->getMessage(), 5);
            return;
        }
    }

    /**
     * Replaces the media file associated with the old attachment ID
     * with the new file located at the specified path.
     *
     * @param int $old_file_id The ID of the old attachment.
     * @param string $new_file_path The path to the new file.
     */
    private function replace_media_file(int $old_file_id, string $new_file_path): bool
    {
        try {
            // Get the old file path
            $old_file_path = get_attached_file($old_file_id);

            // Check if the old file exists
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }

            // Move the new file to the old file's location
            $file_renamed = rename($new_file_path, $old_file_path);

            // If the file was not renamed, abort
            if (!$file_renamed) {
                return false;
            }

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
            $post_updated = wp_update_post($attachment_data);

            // If the post was not updated, abort
            if (is_wp_error($post_updated) || $post_updated === 0) {
                return false;
            }

            // Sync the file with Google Storage by calling the "wp_update_attachment_metadata" function.
            // https://github.com/udx/wp-stateless/blob/0871da645453240007178f4a5f243ceab6a188ea/lib/classes/class-bootstrap.php#L376
            $attach_data = wp_generate_attachment_metadata($old_file_id, $old_file_path);
            $status = wp_update_attachment_metadata($old_file_id, $attach_data);

            // Purge the Cloudflare cache for the replaced file url.
            // $this->purge_cloudflare(wp_get_attachment_url($old_file_id));

            // If the file is an image, replace the image variants in Google Storage.
            if (in_array($filetype['type'], self::IMAGE_MIME_TYPES)) {
                $this->replace_image_variants($old_file_id);
            }

            return $status;
        } catch (\Exception $e) {
            set_transient('media_replacement_error', $e->getMessage(), 5);
            return false;
        }
    }

    private function replace_image_variants($original_image_id)
    {
        $image_url = 'https://www.greenpeace.org/static/planet4-defaultcontent-stateless-develop/2020/06/e7c50925-gp1styhp.jpg';

        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            error_log('Invalid image URL: ' . $image_url);
            return false;
        }

        // Get the uploads directory
        $upload_dir = wp_upload_dir();
        if (!is_writable($upload_dir['path'])) {
            error_log('Uploads directory is not writable: ' . $upload_dir['path']);
            return false;
        }

        // Generate a temporary file path in the uploads directory
        $temp_file = $upload_dir['path'] . '/temp-image-' . time() . '.jpg';

        // Download the image from the external URL
        $image_data = file_get_contents($image_url);
        if ($image_data === false) {
            error_log('Failed to download image from URL: ' . $image_url);
            return false;
        }

        // Save the downloaded image to the temporary file
        $saved = file_put_contents($temp_file, $image_data);
        if ($saved === false) {
            error_log('Failed to save image to temporary file: ' . $temp_file);
            return false;
        }

        // Log the temporary file path for debugging
        error_log('Downloaded image saved as temporary file: ' . $temp_file);

        $image_sm_meta = get_post_meta($original_image_id, 'sm_cloud')[0];

        foreach($image_sm_meta['sizes'] as $image) {
            // Trigger the image_make_intermediate_size filter
            $width = $image['width'];  // Desired width
            $height = $image['height']; // Desired height
            $crop = true;  // Whether to crop the image to exact dimensions

            $resized_image = image_make_intermediate_size($temp_file, $width, $height, $crop);

            if (!$resized_image) {
                error_log('Failed to generate intermediate size for: ' . $temp_file);
                unlink($temp_file); // Cleanup
                return false;
            }
        }
        // Cleanup: Remove the temporary file after processing
        if (file_exists($temp_file)) {
            unlink($temp_file);
            error_log('Temporary file deleted: ' . $temp_file);
        }

        return true;
    }

    /**
     * Purge the Cloudflare cache for a specific URL.
     *
     * @param string $url The URL to be purged.
     */
    private function purge_cloudflare(string $url): void
    {
        try {
            $generator = $this->cf->purge([$url]);
            $api_responses = [];

            foreach ($generator as $purge_result) {
                [$api_response] = $purge_result;
                $api_responses[] = $api_response;
            }

            if ($api_responses[0]) {
                $message = __('URL was successfully purged from cache: ', 'planet4-master-theme-backend') . $url;
                set_transient('cloudflare_purge_message', $message, 5);
            } else {
                // phpcs:ignore Generic.Files.LineLength.MaxExceeded
                $error_message = __('There was an error purging the URL from cache: ', 'planet4-master-theme-backend') . $url;
                set_transient('cloudflare_purge_error', $error_message, 5);
            }
        } catch (\Exception $e) {
            set_transient('cloudflare_purge_error', $e->getMessage(), 5);
            return;
        }
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
     * Renders admin notices.
     *
     * @param string $transient_key The Transient key.
     * @param string $type The type of message.
     */
    private function render_notice(string $transient_key, string $type): void
    {
        if (!$message = get_transient($transient_key)) {
            return;
        }

        $notice_class = $type === 'success' ? 'notice-success' : 'notice-error';
        echo '
            <div class="notice ' . esc_attr($notice_class) . ' is-dismissible">
            <p>' . esc_html($message) . '</p>
            </div>';
        delete_transient($transient_key);
    }
}
