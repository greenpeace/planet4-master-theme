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
        add_filter('attachment_fields_to_edit', [$this, 'add_replace_media_button'], 10, 2);
        add_action('wp_ajax_replace_media', [$this, 'ajax_replace_media']);
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
    
    function add_replace_media_button($form_fields, $post) {
        if ($post->post_type !== 'attachment') {
            return;
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

    function replace_media_file($old_file_id, $new_file_path) {
        // Get the old file path
        $old_file_path = get_attached_file($old_file_id);
    
        // Check if the old file exists
        if (!file_exists($old_file_path)) {
          return;  
        }

        $old_dir = pathinfo($old_file_path, PATHINFO_DIRNAME);
        $old_file_base = pathinfo($old_file_path, PATHINFO_FILENAME); // Get base file name without extension

        // Move the new file to the old original file location
        rename($new_file_path, $old_file_path);

        // Find all files matching the pattern (e.g., c8b8bd7e-gp0stpsh7_flipped-404-*)
        $pattern = $old_dir . '/' . $old_file_base . '*';
        $matching_files = glob($pattern);

        $this->replace_matching_files($matching_files, $old_file_path);
        $this->update_main_file_meta($old_file_path, $old_file_id);
        $this->update_all_sizes_files_meta($old_file_id, $old_dir);
    }

    // Loop through all the matching files and replace them
    function replace_matching_files($matching_files, $old_file_path)
    {
        foreach ($matching_files as $old_size_path) {
            // Check if the file is different from the original image
            if ($old_size_path === $old_file_path) {
                continue;
            }
    
            // Use wp_get_image_editor to resize the new image
            $image_editor = wp_get_image_editor($old_file_path);

            if (is_wp_error($image_editor)) {
                error_log('Error initializing image editor: ' . $image_editor->get_error_message());
                continue;
            }

            // Get the dimensions of the old size image
            list($width, $height) = getimagesize($old_size_path);

            // Resize the image to the old size's dimensions
            $image_editor->resize($width, $height, false); // false to keep aspect ratio
            $resized_image_path = $image_editor->save();

            if (is_wp_error($resized_image_path)) {
                error_log('Error saving resized image: ' . $resized_image_path->get_error_message());
                continue;
            }
            
            // Delete the old size file
            unlink($old_size_path);
                
            // Move the resized image to the old size file path
            rename($resized_image_path['path'], $old_size_path);
        }
    }
    
    // Update file metadata with new dimensions but keep the same file names
    function update_main_file_meta($old_file_path, $old_file_id)
    {
        $attachment_data = array(
            'ID' => $old_file_id,
            'post_mime_type' => wp_check_filetype($old_file_path)['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($old_file_path)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        wp_update_post($attachment_data);
    }

    // Update the metadata (if applicable) with new dimensions for all sizes
    function update_all_sizes_files_meta($old_file_id, $old_dir)
    {
        $meta = wp_get_attachment_metadata($old_file_id);

        if(empty($meta)) {
            return;
        }
        foreach ($meta['sizes'] as $size => &$size_info) {
            $old_size_file = $old_dir . '/' . $size_info['file'];
            if (file_exists($old_size_file)) {
                list($width, $height) = getimagesize($old_size_file);
                $size_info['width'] = $width;
                $size_info['height'] = $height;
            }
        }
        wp_update_attachment_metadata($old_file_id, $meta);
    }
}
