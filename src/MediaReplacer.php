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
    private CloudflarePurger $cf;
    private array $replacement_status;
    private array $cache_purge_status;
    private array $user_messages;
    private string $bucket_name;
    private mixed $stateless;

    private const TRANSIENT = [
        'file' => 'file_replacement_notice',
        'cache' => 'purge_cache_notice',
    ];
    private const GC_STORAGE_URL = 'https://storage.googleapis.com/';
    private const P4_SLACK_CHANNEL = 'https://greenpeace.enterprise.slack.com/archives/C014UMRC4AJ';
    private const REPLACED_META_KEY = '_replaced';

    /**
     * MediaReplacer constructor.
     */
    public function __construct()
    {
        // If the plugin is not active, abort.
        if (function_exists('is_plugin_active') && !is_plugin_active('wp-stateless/wp-stateless-media.php')) {
            return;
        }

        // If the stateless_media function does not exist, abort.
        if (!function_exists('ud_get_stateless_media')) {
            return;
        }

        /** @disregard P1010 Undefined function 'P4\MasterTheme\ud_get_stateless_media' */
        $this->stateless = ud_get_stateless_media();

        // If the stateless mode is disabled, abort.
        if ($this->stateless->get('sm.mode') === 'disabled') {
            return;
        }

        $this->set_variables();
        $this->set_hooks();
    }

    private function set_variables(): void
    {
        $this->cf = new CloudflarePurger();

        $this->bucket_name = $this->stateless->get('sm.bucket');

        $this->cache_purge_status = [
            'success' => [],
            'error' => [],
        ];

        // phpcs:disable Generic.Files.LineLength.MaxExceeded
        $this->user_messages = [
            'replace' => __('Replace Media', 'planet4-master-theme-backend'),
            'metabox' => __('Use this to replace the current file without changing the file URL.', 'planet4-master-theme-backend'),
            'attach' => __('Attachment ID is missing.', 'planet4-master-theme-backend'),
            'file' => __('File is missing.', 'planet4-master-theme-backend'),
            'media' => __('Media Replacer failed:', 'planet4-master-theme-backend'),
            'gd' => __('GD library is missing.', 'planet4-master-theme-backend'),
            'image' => __('Error processing image.', 'planet4-master-theme-backend'),
            'success' => __('These files were successfully replaced:', 'planet4-master-theme-backend'),
            'error' => __('There was an issue replacing these files:', 'planet4-master-theme-backend'),
            'cf_success' => __('The cache was successfully purged for these files:', 'planet4-master-theme-backend'),
            'cf_error' => __('There was an issue purging the cache for these files:', 'planet4-master-theme-backend'),
        ];
        // phpcs:enable Generic.Files.LineLength.MaxExceeded
    }

    private function set_hooks(): void
    {
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
        add_filter('media_row_actions', [$this, 'add_replacer_to_row_action'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_modal_script']);
        add_action('add_meta_boxes', [$this, 'add_replace_media_metabox']);
        add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']);
        add_action('admin_notices', [$this, 'display_admin_notices']);
    }

    /**
     * Enqueues the JavaScript required for the media replacement modal.
     */
    public function enqueue_media_modal_script(): void
    {
        $id = 'custom-media-replacer';
        $path = '/admin/js/media_replacer.js';

        wp_enqueue_script(
            $id,
            get_template_directory_uri() . $path,
            [],
            Loader::theme_file_ver($path),
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
            $this->user_messages['replace'],
            [$this, 'render_replace_media_metabox'],
            'attachment',
            'side',
            'low'
        );
    }

    /**
     * Adds a button to replace media files in the attachments editor.
     *
     * @param WP_Post $post The attachment post object.
     */
    public function render_replace_media_metabox(WP_Post $post): void
    {
        echo "<p>" . $this->user_messages['metabox'] . "</p>";
        echo $this->get_replace_button_html($post);
    }

    /**
     * Adds a button to replace media files in the Attachment details modal.
     *
     * @param array $form_fields Existing attachment form fields.
     * @param WP_Post $post The attachment post object.
     * @return array Modified attachment form fields.
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
     * Adds the Replace Media link to the row action of the Media Library.
     *
     * @param array $actions The list of actions.
     * @param WP_Post $post The attachment post object.
     * @return array The updated list of actions.
     */
    public function add_replacer_to_row_action(array $actions, WP_Post $post): array
    {
        if (current_user_can('edit_post', $post->ID)) {
            $actions['replace-media'] = $this->get_replace_link_html($post);
        }
        return $actions;
    }

    /**
     * Generates the HTML for the Replace Media link in the row action.
     *
     * @param WP_Post $post The attachment post object.
     * @return string The HTML for the replace link.
     */
    private function get_replace_link_html(WP_Post $post): string
    {
        $link = '<a
            style="cursor: pointer"
            class="media-replacer-button"
            data-attachment-id="' . esc_attr($post->ID) . '"
            data-mime-type="' . esc_attr($post->post_mime_type) . '"
        >
        ' . $this->user_messages['replace'] . '
        </a>
        ';

        return $link . $this->get_replace_input_html($post);
    }

    /**
     * Generates the HTML for the Replace Media button.
     *
     * @param WP_Post $post The attachment post object.
     * @return string The HTML for the replace button.
     */
    private function get_replace_button_html(WP_Post $post): string
    {
        $button = '<button
            type="button"
            class="button media-replacer-button"
            data-attachment-id="' . esc_attr($post->ID) . '"
            data-mime-type="' . esc_attr($post->post_mime_type) . '"
        >
        ' . $this->user_messages['replace'] . '
        </button>';

        return $button . $this->get_replace_input_html($post);
    }

    /**
     * Generates the HTML for the Replace Media hidden input.
     *
     * @param WP_Post $post The attachment post object.
     * @return string The HTML for the input.
     */
    private function get_replace_input_html(WP_Post $post): string
    {
        return
        '<input
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
                throw new \LogicException($this->user_messages['attach']);
            }

            // Check if the file is set
            if (empty($_FILES['file'])) {
                throw new \LogicException($this->user_messages['file']);
            }

            $attachment_id = intval($_POST['attachment_id']);
            $file = $_FILES['file'];
            $file_mime_type = mime_content_type($file['tmp_name']);

            // Determine if the file is an image based on its MIME type
            $image_type = array_search($file_mime_type, array_column(ImageHandler::IMAGE_MIME_TYPES, 'mime'));

            if ($image_type !== false) {
                $this->replace_images($file, $attachment_id);
            } else {
                $this->replace_media_file($file, $attachment_id, $file_mime_type);
            }
        } catch (\LogicException $e) {
            $this->error_handler($e->getMessage());
        }
    }

    /**
     * Replaces the non-image media file.
     *
     * @param array $file The uploaded file data.
     * @param int $old_file_id The ID of the attachment to replace.
     * @param string $file_mime_type The MIME type of the uploaded file.
     */
    private function replace_media_file(
        array $file,
        int $old_file_id,
        string $file_mime_type
    ): void {
        try {
            $file_meta = get_post_meta($old_file_id);
            $filename = $file_meta['_wp_attached_file'][0];
            $sm_cloud_data = unserialize($file_meta['sm_cloud'][0]);

            $this->upload_file(
                $sm_cloud_data['name'],
                $file['tmp_name'],
                $file_mime_type,
                [
                    'size' => '__full',
                    'object-id' => $old_file_id,
                    'source-id' => md5($filename . $this->bucket_name), //NOSONAR
                    'file-hash' => md5($filename), //NOSONAR
                ],
                $old_file_id
            );

            $this->purge_cache();
            wp_send_json_success();
        } catch (\LogicException $e) {
            $this->error_handler($e->getMessage());
        }
    }

    /**
     * Replaces images (main image and also all the thumbnails).
     * Thumbnails are manually created using the GD library.
     * Images are uploaded to Google Storage using WP Stateless functions.
     *
     * @param array $file The uploaded file data.
     * @param string $id The attachment ID.
     */
    private function replace_images(array $file, string $id): void
    {
        try {
            // The GD extension is needed for image manipulation.
            // If it's not loaded, abort.
            if (!extension_loaded('gd')) {
                throw new \LogicException($this->user_messages['gd']);
            }

            $new_image_path = $file['tmp_name'];
            $new_image_info = getimagesize($new_image_path);
            $new_image_width = $new_image_info[0];
            $new_image_height = $new_image_info[1];
            $new_image_type = $new_image_info[2];

            // Validate image type against allowed MIME types
            if (!isset(ImageHandler::IMAGE_MIME_TYPES[$new_image_type])) {
                throw new \LogicException($this->user_messages['image']);
            }

            // Load the image dynamically
            $image_data = ImageHandler::IMAGE_MIME_TYPES[$new_image_type];
            $image = call_user_func($image_data['create'], $new_image_path);
            if (!$image) {
                throw new \LogicException($this->user_messages['image']);
            }

            // Get the image metadata.
            $old_image_meta = get_post_meta($id, 'sm_cloud')[0];
            if (!$old_image_meta) {
                throw new \LogicException($this->user_messages['image']);
            }

            $old_image_extension = pathinfo($old_image_meta['name'], PATHINFO_EXTENSION);
            $old_image_dirname = pathinfo($old_image_meta['name'], PATHINFO_DIRNAME);
            $old_image_filename = pathinfo($old_image_meta['name'], PATHINFO_FILENAME);
            $image_name = $old_image_dirname . '/' . $old_image_filename;

            // Create metadata for uploading the main image.
            $metadata = [
                'width' => $new_image_width,
                'height' => $new_image_height,
                'size' => '__full',
                'object-id' => $id,
                'source-id' => md5($old_image_filename . $this->bucket_name), //NOSONAR
                'file-hash' => md5($old_image_filename), //NOSONAR
            ];

            // Save the file to a temporary location
            $temporary_file_path = tempnam(sys_get_temp_dir(), 'img_') . '.' . $old_image_extension;
            call_user_func($image_data['save'], $image, $temporary_file_path);

            // Replace the main image.
            $this->upload_file(
                $old_image_meta['name'],
                $temporary_file_path,
                $image_data['mime'],
                $metadata,
                $id
            );

            $this->upload_thumbnails(
                $id,
                $image,
                $new_image_width,
                $new_image_height,
                $old_image_meta,
                $image_name,
                $image_data,
                $old_image_extension
            );

            // Free memory
            imagedestroy($image);

            $this->purge_cache();
            wp_send_json_success();
        } catch (\LogicException $e) {
            $this->error_handler($e->getMessage());
        }
    }

    /**
     * Uploads and replaces image thumbnails.
     *
     * @param string $id The attachment ID.
     * @param object $image The GD image resource.
     * @param int $new_image_width The width of the new image.
     * @param int $new_image_height The height of the new image.
     * @param array $old_image_meta Metadata of the old image.
     * @param string $image_name The name of the image.
     * @param array $image_data The image properties (MIME type, extension, etc.).
     * @param string $old_image_extension The original image file extension.
     */
    private function upload_thumbnails(
        string $id,
        object $image,
        int $new_image_width,
        int $new_image_height,
        array $old_image_meta,
        string $image_name,
        array $image_data,
        string $old_image_extension
    ): void {
        // Handle image thumbnails.
        foreach ($old_image_meta['sizes'] as $size => $old_image_data) {
            $old_image_width = (int) ($old_image_data['width']);
            $old_image_height = (int) ($old_image_data['height']);

            // Get the image width and height from the file name.
            // This is necessary for the cases where the width and height
            // cannot be extracted from the "sizes" array.
            if (
                (!$old_image_width || !$old_image_height) &&
                $old_image_data['name'] &&
                preg_match('/-(\d+)x(\d+)\.(jpg|jpeg|png|gif|webp)$/i', $old_image_data['name'], $matches)
            ) {
                $old_image_width = (int) $matches[1];
                $old_image_height = (int) $matches[2];
            }

            // Remove the substring "-scaled" from the image file name.
            // The substring is added by WordPress automatically to big images.
            // See: https://make.wordpress.org/core/2019/10/09/introducing-handling-of-big-images-in-wordpress-5-3/
            $img_name = str_replace('-scaled', '', $image_name);
            $img_name = $img_name . '-' . $old_image_width . 'x' . $old_image_height;

            // Create metadata for the thumbnails.
            $metadata = [
                'width' => $new_image_width,
                'height' => $new_image_height,
                'file-hash' => md5($img_name), //NOSONAR
                'size' => $size,
                'child-of' => $id,
            ];

            // Create thumbnail.
            $thumb = ImageHandler::resize_image(
                $image,
                $new_image_width,
                $new_image_height,
                $old_image_width,
                $old_image_height
            );

            // Save the file to a temporary location
            $temporary_file_path = tempnam(sys_get_temp_dir(), 'thumb_') . '.' . $old_image_extension;
            call_user_func($image_data['save'], $thumb, $temporary_file_path);

            // Replace thumbnail in Google Cloud Storage.
            $this->upload_file(
                $img_name . '.' . $old_image_extension,
                $temporary_file_path,
                $image_data['mime'],
                $metadata
            );

            // Free memory
            imagedestroy($thumb);
        }
    }

    /**
     * Uploads a file to the media storage.
     *
     * @param string $name The file name.
     * @param string $absolute_path The absolute path to the file.
     * @param string $mime The MIME type of the file.
     * @param array $metadata Metadata for the file upload.
     * @param int|null $id (Optional) The file id.
     */
    private function upload_file(
        string $name,
        string $absolute_path,
        string $mime,
        array $metadata,
        int|null $id = null
    ): void {
        try {
            // Prepare the upload arguments
            $image_args = [
                'name' => $name,
                'absolutePath' => $absolute_path,
                'mimeType' => $mime,
                'metadata' => $metadata,
                'cacheControl' => 'no-cache, max-age=0, must-revalidate',
                'contentDisposition' => null,
                'force' => true,
            ];

            // Upload the file to Google Cloud Storage.
            $status = $this->stateless->get_client()->add_media($image_args);

            // Save in the metadata that the attachment has been replaced.
            if ($status && $id) {
                update_post_meta($id, self::REPLACED_META_KEY, true);
            }

            $this->replacement_status[$status ? 'success' : 'error'][] = $name;
            $encoded_msg = json_encode($this->replacement_status, JSON_PRETTY_PRINT);
            set_transient(self::TRANSIENT['file'], $encoded_msg, 5);
        } catch (\LogicException $e) {
            $this->error_handler($e->getMessage());
        }
    }

    /**
     * Handles errors in replacements.
     * Sets error messages.
     *
     * @param string $message The error message.
     */
    private function error_handler(string $message): void
    {
        $msg = $this->user_messages['media'] . $message;

        if (function_exists('\Sentry\captureMessage')) {
            \Sentry\captureMessage($msg);
        }

        //phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        error_log($msg);
        array_push($this->replacement_status['error'], $msg);
        wp_send_json_error($msg);
    }

    /**
     * Purges cached files from the CDN and stores the purge result in a transient.
     *
     */
    private function purge_cache(): void
    {
        // Get all the files that were replaced (successfuly or not).
        if (!$status = get_transient(self::TRANSIENT['file'])) {
            return;
        }

        $status = json_decode($status, true);

        // If there are no succesfuly replaced files, abort.
        if (empty($status['success'])) {
            return;
        }

        $urls_to_purge = [];

        // Loop through the files and generate the "stateless" URLs.
        // For example:
        // Regular URL: https://www.greenpeace.org/static/bucket-name/2025/10/filename.jpg
        // Stateless URL: https://storage.googleapis.com/bucket-name/2025/10/filename.jpg
        foreach ($status['success'] as $url) {
            $url = ltrim($url, '/');
            $stateless_url = rtrim(self::GC_STORAGE_URL, '/') . '/' . trim($this->bucket_name, '/') . '/' . $url;
            $urls_to_purge[] = $stateless_url;
        }

        if (empty($urls_to_purge)) {
            return;
        }

        // Purge the URLs.
        $purge_status = $this->cf->purge($urls_to_purge);

        // Store the result of the purging.
        foreach ($purge_status as $entry) {
            [$response, $chunk] = array_values($entry);
            $this->cache_purge_status[(bool) $response ? 'success' : 'error'][] = $chunk;
        }

        // Pass the result of the purging to a transient.
        $encoded_msg = json_encode($this->cache_purge_status, JSON_PRETTY_PRINT);
        set_transient(self::TRANSIENT['cache'], $encoded_msg, 5);
    }

    /**
     * Displays admin notices.
     */
    public function display_admin_notices(): void
    {
        $this->render_notice(
            'file',
            $this->user_messages['success'],
            $this->user_messages['error']
        );
        $this->render_notice(
            'cache',
            $this->user_messages['cf_success'],
            $this->user_messages['cf_error']
        );
    }

    /**
     * Renders an admin notice.
     *
     * @param string $transient_key The key for the transient notice.
     * @param string $success_message The success message.
     * @param string $error_message The error message.
     */
    private function render_notice(string $transient_key, string $success_message, string $error_message): void
    {
        if (!$status = get_transient(self::TRANSIENT[$transient_key])) {
            return;
        }

        $status = json_decode($status, true);

        // Flatten nested arrays safely, even if empty
        $status['success'] = array_merge(...array_map(
            fn($v) => (array) $v,
            $status['success'] ?? []
        ));
        $status['error'] = array_merge(...array_map(
            fn($v) => (array) $v,
            $status['error'] ?? []
        ));

        if (!empty($status['success'])) {
            echo "<div class='notice notice-success is-dismissible'>";
            echo "<p><strong>" . $success_message . "</strong></p>";
            echo "<ul>";

            foreach ($status['success'] as $item) {
                echo '<li>' . esc_html($item) . '</li>';
            }

            echo "</ul>";
            echo "</div>";
        }

        if (!empty($status['error'])) {
            echo "<div class='notice notice-error is-dismissible'>";
            echo "<p><strong>" . $error_message . "</strong></p>";
            echo "<ul>";

            foreach ($status['error'] as $item) {
                echo '<li>' . esc_html($item) . '</li>';
            }

            echo "</ul>";
            echo "<p><a target='_blank' href='" . self::P4_SLACK_CHANNEL . "'>";
            echo "Click here to receive support on Slack >>>";
            echo "</a></p>";
            echo "</div>";
        }

        delete_transient(self::TRANSIENT[$transient_key]);
    }
}
