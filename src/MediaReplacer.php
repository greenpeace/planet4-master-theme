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
     * We need this list as for now we will only replace non-image files.
     */
    private const IMAGE_MIME_TYPES = [
        IMAGETYPE_JPEG => [
            'mime' => 'image/jpeg',
            'create' => 'imagecreatefromjpeg',
            'save' => 'imagejpeg',
            'extension' => 'jpg'
        ],
        IMAGETYPE_PNG => [
            'mime' => 'image/png',
            'create' => 'imagecreatefrompng',
            'save' => 'imagepng',
            'extension' => 'png'
        ],
        IMAGETYPE_GIF => [
            'mime' => 'image/gif',
            'create' => 'imagecreatefromgif',
            'save' => 'imagegif',
            'extension' => 'gif'
        ],
        IMAGETYPE_BMP => [
            'mime' => 'image/bmp',
            'create' => 'imagecreatefrombmp',
            'save' => 'imagebmp',
            'extension' => 'bmp'
        ],
        IMAGETYPE_WEBP => [
            'mime' => 'image/webp',
            'create' => 'imagecreatefromwebp',
            'save' => 'imagewebp',
            'extension' => 'webp'
        ],
    ];

    /**
     * MediaReplacer constructor.
     */
    public function __construct()
    {
        if (function_exists('is_plugin_active') && !is_plugin_active('wp-stateless/wp-stateless-media.php')) {
            return;
        }

        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_modal_script']);
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
        add_action('add_meta_boxes', [$this, 'add_replace_media_metabox']);
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
        // Check if the attachment post is an image
        // If so, check if the GD extension is loaded
        if (in_array($post->post_mime_type, array_column(self::IMAGE_MIME_TYPES, 'mime')) && !extension_loaded('gd')) {
            $message = __('There was a problem. This image cannot be replaced.', 'planet4-master-theme-backend');
            echo "<p>" . $message . "</p>";
            return;
        }

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

        // Check if the attachment post is an image
        // If so, check if the GD extension is loaded
        if (in_array($post->post_mime_type, array_column(self::IMAGE_MIME_TYPES, 'mime')) && !extension_loaded('gd')) {
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
            $file = $_FILES['file'];
            $file_mime_type = mime_content_type($file['tmp_name']);

            // Determine if the file is an image based on its MIME type
            $image_type = array_search($file_mime_type, array_column(self::IMAGE_MIME_TYPES, 'mime'));

            if ($image_type !== false) {
                $file_replaced = $this->replace_images($file, $attachment_id);
            } else {
                $file_replaced = $this->replace_media_file($file, $attachment_id);
            }

            // If the file was not replaced, abort
            if (!$file_replaced) {
                $error_message = __('Media file could not be replaced.', 'planet4-master-theme-backend');
                set_transient('media_replacement_error', print_r($file_replaced, true), 5);
                wp_send_json_error($error_message);
                return;
            }

            $message = __('Media replaced successfully!', 'planet4-master-theme-backend');
            set_transient('media_replacement_message', print_r($file_replaced, true), 5);
            $this->purge_cloudflare(wp_get_attachment_url($attachment_id));
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
    private function replace_media_file($new_file, $old_file_id): bool
    {
        try {
            // Upload the file
            $movefile = wp_handle_upload($new_file, array('test_form' => false));

            // If the file was not uploaded, abort
            if (!$movefile) {
                $message = __('Media could not be uploaded.', 'planet4-master-theme-backend');
                $error_message = isset($movefile['error']) ? $movefile['error'] : $message;
                set_transient('media_replacement_error', $error_message, 5);
                wp_send_json_error($error_message);
                return false;
            }

            // Get the old file path
            $old_file_path = get_attached_file($old_file_id);

            // Check if the old file exists
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }

            // Move the new file to the old file's location
            $file_renamed = rename($movefile['file'], $old_file_path);

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

            // Update file metadata
            // By calling the "wp_update_attachment_metadata" function,
            // the WP Stateless plugin syncs the file with Google Storage.
            // https://github.com/udx/wp-stateless/blob/0871da645453240007178f4a5f243ceab6a188ea/lib/classes/class-bootstrap.php#L376
            $attach_data = wp_generate_attachment_metadata($old_file_id, $old_file_path);
            $post_meta_updated = wp_update_attachment_metadata($old_file_id, $attach_data);

            // If the post meta was not updated, abort
            return $post_meta_updated;
        } catch (\Exception $e) {
            set_transient('media_replacement_error', $e->getMessage(), 5);
            return false;
        }
    }

    /**
     * Replaces images associated with the old attachment ID.
     * Not only the main image, but also all the thumbnails are replaced.
     *
     * @param array $file An array with data of the new image.
     * @param string $id The id of the image to be replaced.
     */
    private function replace_images($file, $id)
    {
        $new_image_path = $file['tmp_name'];
        $new_image_info = getimagesize($new_image_path);
        $new_image_width = $new_image_info[0];
        $new_image_height = $new_image_info[1];
        $new_image_type = $new_image_info[2];

        $old_image_meta = get_post_meta($id, 'sm_cloud')[0];
        $old_image_dirname = pathinfo($old_image_meta['name'], PATHINFO_DIRNAME);
        $old_image_filename = pathinfo($old_image_meta['name'], PATHINFO_FILENAME);
        $image_name = $old_image_dirname . '/' . $old_image_filename;

        // Validate image type against allowed MIME types
        if (!isset(self::IMAGE_MIME_TYPES[$new_image_type])) {
            return 'Invalid image type.';
        }

        // Load the image dynamically
        $image_data = self::IMAGE_MIME_TYPES[$new_image_type];
        $image = call_user_func($image_data['create'], $new_image_path);
        if (!$image) {
            return 'Failed to create image resource.';
        }

        foreach ($old_image_meta['sizes'] as $size => $old_image_data) {
            $old_image_width = $old_image_data['width'];
            $old_image_height = $old_image_data['height'];

            // Determine cropping dimensions
            $src_aspect = $new_image_width / $new_image_height;
            $dst_aspect = $old_image_width / $old_image_height;

            if ($src_aspect > $dst_aspect) {
                // Source image is wider than the destination, crop width
                $src_height = $new_image_height;
                $src_width = (int) ($new_image_height * $dst_aspect);
                $src_x = (int) (($new_image_width - $src_width) / 2);
                $src_y = 0;
            } else {
                // Source image is taller than the destination, crop height
                $src_width = $new_image_width;
                $src_height = (int) ($new_image_width / $dst_aspect);
                $src_x = 0;
                $src_y = (int) (($new_image_height - $src_height) / 2);
            }

            $thumb = imagecreatetruecolor($old_image_width, $old_image_height);
            imagecopyresampled(
                $thumb,
                $image,
                0, 0, $src_x, $src_y,
                $old_image_width, $old_image_height,
                $src_width, $src_height
            );

            // Save the cropped image to a temporary location
            $thumbnail_file = tempnam(sys_get_temp_dir(), 'thumb_') . '.' . $image_data['extension'];
            call_user_func($image_data['save'], $thumb, $thumbnail_file);

            $variant_image_args = [
                'name' => $image_name . '-' . $old_image_width . 'x' . $old_image_height . '.' . $image_data['extension'],
                'force' => true,
                'absolutePath' => $thumbnail_file,
                'cacheControl' => 'public, max-age=36000, must-revalidate',
                'contentDisposition' => null,
                'mimeType' => $image_data['mime'],
                'metadata' => [
                    'width' => $new_image_width,
                    'height' => $new_image_height,
                    'file-hash' => md5($image_name),
                    'size' => $size,
                    'child-of' => $id,
                ],
            ];

            ud_get_stateless_media()->get_client()->add_media($variant_image_args);
            imagedestroy($thumb); // Free memory
        }
        imagedestroy($image); // Free memory
        return $new_image_info;
    }

    /**
     * Purge the Cloudflare cache for a specific URL.
     *
     * @param string $url The URL to be purged.
     */
    private function purge_cloudflare(string $url): void
    {
        try {
            $cf = new CloudflarePurger();
            $generator = $cf->purge([$url]);
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
