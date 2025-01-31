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
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/webp',
        'image/tiff',
        'image/svg+xml',
    ];

    /**
     * MediaReplacer constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_modal_script']);
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
        add_action('add_meta_boxes', [$this, 'add_replace_media_metabox']);
        add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']);
        add_action('admin_notices', [$this, 'display_admin_notices']);

        // echo "<pre>";
        // $serialized_data = get_post_meta(1281)['sm_cloud'][0];
        // $data = unserialize($serialized_data);
        // var_dump($data['name']);
        // $absolutePath = '2025/01/ec8a67a2-gp-img-1.jpg';
        // var_dump( ud_get_stateless_media()->get_client()->get_media($absolutePath) );
        // echo "</pre>";

        // echo get_post_type(771);


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
        // Check if the post excludes image mime types
        // if (in_array($post->post_mime_type, self::IMAGE_MIME_TYPES)) {
        //     $message = __('Images cannot be replaced yet.', 'planet4-master-theme-backend');
        //     echo "<p>" . $message . "</p>";
        //     return;
        // }

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

        // Check if the post excludes image mime types
        // if (in_array($post->post_mime_type, self::IMAGE_MIME_TYPES)) {
        //     return $form_fields;
        // }

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

            // If the file is an image, replace the image variants in Google Storage.
            if (in_array($file['type'], self::IMAGE_MIME_TYPES)) {
                $file_replaced = $this->generate_thumbnails_before_upload($file, $attachment_id);
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

    function generate_thumbnails_before_upload($file, $id) {
        // Check if GD is available
        if (!extension_loaded('gd')) {
            return new WP_Error('gd_missing', 'GD extension is not available.');
        }

        // Get the image path
        $image_path = get_attached_file($id);
        $image_info = getimagesize($image_path);
        $image_type = $image_info[2];

        // Check if the image type is valid
        if ($image_type == IMAGETYPE_JPEG) {
            $image = imagecreatefromjpeg($image_path);
        } elseif ($image_type == IMAGETYPE_PNG) {
            $image = imagecreatefrompng($image_path);
        } elseif ($image_type == IMAGETYPE_GIF) {
            $image = imagecreatefromgif($image_path);
        } else {
            return new WP_Error('invalid_image_type', 'Invalid image type.');
        }

        // Define the sizes you want to generate (e.g., thumbnail size)
        $sizes = array(
            'thumbnail' => array(150, 150), // 150x150 px
            'medium' => array(300, 300),    // 300x300 px
            'large' => array(1024, 1024),   // 1024x1024 px
        );

        // $image_variants = get_post_meta($id, 'sm_cloud')[0]['sizes'];


        // Generate the thumbnails
        $thumbnails = array();

        // foreach ($image_variants as $size => $image) {
        //     $thumb = imagecreatetruecolor($image['width'], $image['height']);
        //     imagecopyresampled($thumb, $image, 0, 0, 0, 0, $image['width'], $image['height'], $image_info[0], $image_info[1]);

        //     // Save the thumbnail to a temporary location
        //     $thumbnail_file = tempnam(sys_get_temp_dir(), 'thumb_') . ".jpg";
        //     imagejpeg($thumb, $thumbnail_file);

        //     // Store the generated thumbnail file path for later use
        //     $thumbnails[$size] = $thumbnail_file;

        //     $variant_image_args = array(
        //         'name' => 'pedro-' . $size,
        //         'force' => true,
        //         'absolutePath' => $thumbnail_file,
        //         'cacheControl' => 'public, max-age=36000, must-revalidate',
        //         'contentDisposition' => null,
        //         'mimeType' => 'image/jpeg',
        //     );

        //     ud_get_stateless_media()->get_client()->add_media($variant_image_args);


        //     imagedestroy($thumb); // Free up memory
        // }


        foreach ($sizes as $size => $dimensions) {
            $thumb = imagecreatetruecolor($dimensions[0], $dimensions[1]);
            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $dimensions[0], $dimensions[1], $image_info[0], $image_info[1]);

            // Save the thumbnail to a temporary location
            $thumbnail_file = tempnam(sys_get_temp_dir(), 'thumb_') . ".jpg";
            imagejpeg($thumb, $thumbnail_file);

            // Store the generated thumbnail file path for later use
            $thumbnails[$size] = $thumbnail_file;

            $variant_image_args = array(
                'name' => 'pedro-' . $size . '.' . $file_extension,
                'force' => true,
                'absolutePath' => $thumbnail_file,
                'cacheControl' => 'public, max-age=36000, must-revalidate',
                'contentDisposition' => null,
                'mimeType' => 'image/jpeg',
            );

            ud_get_stateless_media()->get_client()->add_media($variant_image_args);


            imagedestroy($thumb); // Free up memory
        }

        imagedestroy($image); // Free up memory

        return $id;

        // Step 2: Now upload the original file and thumbnails using wp_handle_upload()
        // First, upload the original file
        // $upload_overrides = array('test_form' => false);
        // $movefile = wp_handle_upload($file, $upload_overrides);

        // if ($movefile && !isset($movefile['error'])) {
        //     // Create the attachment for the original file
        //     $attachment = array(
        //         'guid' => $movefile['url'],
        //         'post_mime_type' => $file['type'],
        //         'post_title' => sanitize_file_name($file['name']),
        //         'post_content' => '',
        //         'post_status' => 'inherit',
        //     );
        //     $attachment_id = wp_insert_attachment($attachment, $movefile['file']);

        //     // Generate metadata for the original image and upload the thumbnails
        //     $metadata = wp_generate_attachment_metadata($attachment_id, $movefile['file']);

        //     // Add the generated thumbnails to metadata manually
        //     foreach ($thumbnails as $size => $thumbnail_file) {
        //         $thumbnail_id = wp_insert_attachment(array(
        //             'post_title' => basename($thumbnail_file),
        //             'post_mime_type' => 'image/jpeg',
        //             'guid' => $movefile['url'],
        //             'post_status' => 'inherit',
        //         ), $thumbnail_file);

        //         // Add this thumbnail to the metadata
        //         $metadata['sizes'][$size] = wp_generate_attachment_metadata($thumbnail_id, $thumbnail_file);
        //     }

        //     // Update the metadata of the original image with the thumbnails
        //     wp_update_attachment_metadata($attachment_id, $metadata);

        //     // Clean up: delete temporary thumbnails
        //     foreach ($thumbnails as $thumbnail) {
        //         unlink($thumbnail); // Remove the temporary thumbnail file
        //     }

        //     return $attachment_id; // Return the attachment ID of the original image
        // } else {
        //     return new WP_Error('upload_error', 'File upload failed', $movefile['error']);
        // }
    }


    // private function replace_image_variants($id)
    // {
    //     $serialized_data = get_post_meta($id)['sm_cloud'][0];
    //     $data = unserialize($serialized_data);
    //     wp_delete_post($id, true);
    //     return $data['name'];
    // }

    // private function replace_image_variants($file, $id)
    // {
    //     // Get old attachment data before deletion
    //     $attachment = get_post($id);

    //     // Get upload directory info
    //     $upload_dir = wp_upload_dir();

    //     // Ensure the old file is deleted
    //     // $old_file_path = get_attached_file($id);
    //     // if ($old_file_path && file_exists($old_file_path)) {
    //     //     unlink($old_file_path); // Delete the old file
    //     // }

    //     // // Delete the old attachment
    //     $deletion = wp_delete_attachment($id, true);

    //     // if (!$deletion) {
    //     //     return false;
    //     // }

    //     // Set a custom filename
    //     // $serialized_data = get_post_meta($id)['sm_cloud'][0];
    //     // $data = unserialize($serialized_data);
    //     // $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    //     // $new_filename = 'custom-prefix-' . time() . '.' . $file_ext;
    //     $file['name'] = '78026ddc-202501449cb006-gp-img-2.jpg';

    //     $max_retries = 10;
    //     $retries = 0;
    //     $object_deleted = false;

    //     $absolutePath = '2025/01/78026ddc-202501449cb006-gp-img-2.jpg';
    //     $aaa = ( ud_get_stateless_media()->get_client()->get_media($absolutePath) );

    //     while ($retries < $max_retries) {
    //         // Check if the object exists in the bucket
    //         try {
    //             $aaa;
    //             sleep(1);
    //         } catch (Exception $e) {
    //             // Object not found, assumed deleted
    //             $object_deleted = true;
    //             break;
    //         }
    //         $retries++;
    //     }

    //     // Upload the new file
    //     $upload = wp_handle_upload($file, ['test_form' => false]);

    //     if (!$upload || isset($upload['error'])) {
    //         return false;
    //     }

    //     // Create a new attachment post
    //     $attachment_data = [
    //         'post_mime_type' => $upload['type'],
    //         'post_title'     => 'test name pedro',
    //         'post_content'   => '',
    //         'post_status'    => 'inherit',
    //     ];

    //     $new_attachment_id = wp_insert_attachment($attachment_data, $upload['file']);

    //     if (is_wp_error($new_attachment_id)) {
    //         return false;
    //     }

    //     // Make sure WP-Stateless processes the new file
    //     // do_action('wp_generate_attachment_metadata', $new_attachment_id);

    //     // Generate metadata and create new thumbnails
    //     require_once ABSPATH . 'wp-admin/includes/image.php';
    //     $attach_data = wp_generate_attachment_metadata($new_attachment_id, $upload['file']);
    //     wp_update_attachment_metadata($new_attachment_id, $attach_data);

    //     // Clear any cached WP-Stateless data
    //     // delete_post_meta($new_attachment_id, 'sm_cloud');

    //     // // Delete the old attachment
    //     // $deletion = wp_delete_attachment($id, true);

    //     return $new_attachment_id;
    // }

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
