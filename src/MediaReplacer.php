<?php

namespace P4\MasterTheme;

/**
 * Class MediaReplacer.
 *
 * This class is used to handle media replacements.
 */
class MediaReplacer
{
    /**
     * Activator constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_modal_script']);
        add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']);
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
    }

    /**
     * Enqueue the custom script for the media replacer in the WordPress admin area.
     * Ensures that jQuery is also enqueued.
     */
    public function enqueue_media_modal_script(): void
    {
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

    /**
     * Handle the AJAX request for replacing media.
     * Validates the input, uploads the new file, and triggers the media replacement process.
     */
    public function ajax_replace_media(): void
    {
        // Check if the attachment ID and file are set
        if (!isset($_POST['attachment_id'])) {
            wp_send_json_error('Attachment ID or file is missing.');
            return;
        }

        if (empty($_FILES['file'])) {
            wp_send_json_error('Attachment ID or file is missing.');
            return;
        }

        // Handle the uploaded file
        $file = $_FILES['file'];
        $upload_overrides = array('test_form' => false); // Avoid form tests

        // Upload the file
        $movefile = wp_handle_upload($file, $upload_overrides);

        if (!$movefile) {
            $error_message = isset($movefile['error']) ?? 'Move file not working';
            wp_send_json_error($error_message);
            return;
        }

        // Replace the media file
        $attachment_id = intval($_POST['attachment_id']);
        $this->replace_media_file($attachment_id, $movefile['file']);
        wp_send_json_success();
    }

    // phpcs:disable Generic.Files.LineLength.MaxExceeded
    /**
     * Adds a custom "Replace Media" button in the media library for attachments.
     *
     * @param array $form_fields Existing form fields for the attachment.
     * @param object $post The post object representing the attachment.
     * @return array Modified form fields with the "Replace Media" button added.
     */
    public function add_replace_media_button(array $form_fields, object $post): array
    {
        if ($post->post_type !== 'attachment') {
            return $form_fields;
        }

        $form_fields['replace_media_button'] = array(
            'input' => 'html',
            'html' => '
                <button type="button" class="button custom-button" data-attachment-id="' . esc_attr($post->ID) . '">Replace Media</button>
                <input type="file" class="replace-media-file" style="display: none;" accept="image/*,video/*" />
            ',
        );

        return $form_fields;
    }
    // phpcs:enable Generic.Files.LineLength.MaxExceeded

    /**
     * Replace the media file for a given attachment.
     * Handles moving the new file to the old file's location, replacing all size variants.
     *
     * @param int $old_file_id The ID of the old attachment.
     * @param string $new_file_path The path of the new file to replace the old one.
     */
    public function replace_media_file(int $old_file_id, string $new_file_path): void
    {
        // Get the old file path
        $old_file_path = get_attached_file($old_file_id);

        // Check if the old file exists
        if (!file_exists($old_file_path)) {
            return;
        }

        $old_dir = pathinfo($old_file_path, PATHINFO_DIRNAME);
        $old_file_base = pathinfo($old_file_path, PATHINFO_FILENAME);

        // Move the new file to the old original file location
        rename($new_file_path, $old_file_path);

        // Find all files matching the pattern (e.g., c8b8bd7e-gp0stpsh7_flipped-404-*)
        $pattern = $old_dir . '/' . $old_file_base . '*';
        $matching_files = glob($pattern);

        $this->replace_matching_files($matching_files, $old_file_path);
        $this->update_main_file_meta($old_file_path, $old_file_id);
        $this->update_all_sizes_files_meta($old_file_id, $old_dir);
    }

    /**
     * Loop through all the matching files and replace them with the resized version of the new image.
     *
     * @param array $matching_files Array of file paths that match the original file name pattern.
     * @param string $old_file_path The path of the original file.
     */
    private function replace_matching_files(array $matching_files, string $old_file_path): void
    {
        foreach ($matching_files as $old_size_path) {
            // Check if the file is different from the original image
            if ($old_size_path === $old_file_path) {
                continue;
            }

            // Use wp_get_image_editor to resize the new image
            $image_editor = wp_get_image_editor($old_file_path);

            if (is_wp_error($image_editor)) {
                //phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
                error_log('Error initializing image editor: ' . $image_editor->get_error_message());
                continue;
            }

            // Get the dimensions of the old size image
            [$width, $height] = getimagesize($old_size_path);

            // Resize the image to the old size's dimensions
            $image_editor->resize($width, $height, false);
            $resized_image_path = $image_editor->save();

            if (is_wp_error($resized_image_path)) {
                //phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
                error_log('Error saving resized image: ' . $resized_image_path->get_error_message());
                continue;
            }

            // Delete the old size file
            unlink($old_size_path);

            // Move the resized image to the old size file path
            rename($resized_image_path['path'], $old_size_path);
        }
    }

    /**
     * Update the main file's metadata after replacing the media.
     *
     * @param string $old_file_path The path of the old file.
     * @param int $old_file_id The ID of the old attachment.
     */
    private function update_main_file_meta(string $old_file_path, int $old_file_id): void
    {
        $attachment_data = array(
            'ID' => $old_file_id,
            'post_mime_type' => wp_check_filetype($old_file_path)['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($old_file_path)),
            'post_content' => '',
            'post_status' => 'inherit',
        );
        wp_update_post($attachment_data);
    }

    /**
     * Update the metadata for all size variants of the attachment.
     * Ensures that the new dimensions are reflected for each size variant.
     *
     * @param int $old_file_id The ID of the old attachment.
     * @param string $old_dir The directory where the old size files are stored.
     */
    private function update_all_sizes_files_meta(int $old_file_id, string $old_dir): void
    {
        $meta = wp_get_attachment_metadata($old_file_id);

        if (empty($meta)) {
            return;
        }
        foreach ($meta['sizes'] as $size => &$size_info) {
            $old_size_file = $old_dir . '/' . $size_info['file'];
            if (!file_exists($old_size_file)) {
                continue;
            }

            [$width, $height] = getimagesize($old_size_file);
            $size_info['width'] = $width;
            $size_info['height'] = $height;
        }
        wp_update_attachment_metadata($old_file_id, $meta);
    }
}
