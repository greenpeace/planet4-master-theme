<?php

namespace P4\MasterTheme;

/**
 * Handles custom attachment behaviors and metadata in the P4 Master Theme.
 */
class AttachmentsController
{
    public const META_FIELDS = [
        'restriction' => '_media_restriction',
        'credit' => '_credit_text',
    ];

    public const IMG_ATTACHMENT_FIELDS = [
        'restriction' => 'restrictions_text',
        'credit' => 'credit_text',
    ];

    public const SM_CLOUD = 'sm_cloud';

    /**
     * Constructor: Hooks into WordPress actions and filters to extend attachment functionality.
     */
    public function __construct()
    {
        add_action('add_attachment', [$this, 'set_sm_cloud_metadata'], 99);
        add_action('add_attachment', [$this, 'update_iptc_metadata']);
        add_action('wpml_after_update_attachment_texts', [$this, 'sync_translation_sm_cloud_meta'], 1, 2);
        add_filter('image_downsize', [$this, 'overrule_wp_stateless_for_no_images'], 100, 2);
        add_filter('attachment_fields_to_edit', [$this, 'add_image_attachment_fields_to_edit'], 10, 2);
        add_filter('attachment_fields_to_save', [$this, 'add_image_attachment_fields_to_save'], 10, 2);
    }

    /**
     * Forces the update of `sm_cloud` metadata on attachment import.
     *
     * Used during WP Import step 1 to ensure WP-Stateless can find the file
     * in Google Cloud Storage (GCS) during subsequent steps.
     *
     * @param int $post_id Attachment post ID.
     */
    public function set_sm_cloud_metadata(int $post_id): void
    {
        if (
            ! defined('WP_IMPORTING')
            || ! WP_IMPORTING
            || ! isset($_GET['step']) // phpcs:ignore WordPress.Security.NonceVerification
            || '1' !== $_GET['step'] // phpcs:ignore WordPress.Security.NonceVerification
            || ! class_exists('wpCloud\StatelessMedia\Bootstrap')
        ) {
            return;
        }

        if (version_compare(\wpCloud\StatelessMedia\Bootstrap::$version, '3.0', '<')) {
            return;
        }

        $post = get_post($post_id);
        if (! $post || 'attachment' !== $post->post_type) {
            return;
        }

        $cloud_meta = get_post_meta($post_id, self::SM_CLOUD, true);
        if (! empty($cloud_meta)) {
            return;
        }

        $metadata = wp_get_attachment_metadata($post_id);
        wp_update_attachment_metadata($post_id, $metadata);
    }

    /**
     * Fix WP-Stateless behavior where non-images are incorrectly returned as images.
     *
     * Prevents functions like `wp_get_attachment_link()` from treating documents
     * (e.g. PDFs) as images by returning false when the file isn't an image.
     *
     * @param mixed    $downsize Result from previous image_downsize filter.
     * @param \WP_Post $id       Attachment ID.
     *
     * @return mixed Modified result or false if not an image.
     */
    public function overrule_wp_stateless_for_no_images($downsize, \WP_Post $id): mixed
    {
        return wp_attachment_is_image($id) ? $downsize : false;
    }

    /**
     * Copy `sm_cloud` metadata to translated attachments (WPML support).
     *
     * @param int        $original_attachment_id ID of the original attachment.
     * @param \stdClass  $translation            WPML translation object.
     */
    public function sync_translation_sm_cloud_meta(int $original_attachment_id, \stdClass $translation): void
    {
        $original_sm_cloud = get_post_meta($original_attachment_id, self::SM_CLOUD, true);
        update_post_meta($translation->element_id, self::SM_CLOUD, $original_sm_cloud);
    }

    /**
     * Adds custom fields to the media attachment edit form.
     *
     * @param array    $form_fields Fields to display in the media form.
     * @param \WP_Post $post        Attachment post object.
     *
     * @return array Modified array of fields.
     */
    public function add_image_attachment_fields_to_edit(array $form_fields, \WP_Post $post): array
    {
        // Add a Credit field.
        $form_fields[self::IMG_ATTACHMENT_FIELDS['credit']] = [
            'label' => __('Credit', 'planet4-master-theme-backend'),
            'input' => 'text',
            'value' => get_post_meta($post->ID, self::META_FIELDS['credit'], true),
            'helps' => __('The owner of the image.', 'planet4-master-theme-backend'),
        ];

        // Add a Restrictions field (if present).
        $img_restrictions = get_post_meta($post->ID, self::META_FIELDS['restriction'], true);
        if ($img_restrictions) {
            $form_fields[self::IMG_ATTACHMENT_FIELDS['restriction']] = [
                'label' => __('Restrictions', 'planet4-master-theme-backend'),
                'input' => 'html',
                'html' => $img_restrictions,
            ];
        }

        return $form_fields;
    }

    /**
     * Saves the custom media metadata fields on attachment save.
     *
     * @param array $post       The attachment post data.
     * @param array $attachment Form data submitted for the attachment.
     *
     * @return array Modified post data.
     */
    public function add_image_attachment_fields_to_save(array $post, array $attachment): array
    {
        if (isset($attachment[self::IMG_ATTACHMENT_FIELDS['credit']])) {
            update_post_meta(
                $post['ID'],
                self::META_FIELDS['credit'],
                $attachment[self::IMG_ATTACHMENT_FIELDS['credit']]
            );
        }

        return $post;
    }

    /**
     * Extracts IPTC metadata from a newly added image attachment and stores relevant fields as post meta.
     * IPTC metadata is a standardized format used to embed information such as author, copyright,
     * usage restrictions, and credits directly within image files.
     *
     * @param int $post_id Attachment post ID.
     */
    public function update_iptc_metadata(int $post_id): void
    {
        $file = get_attached_file($post_id);

        if (!file_exists($file)) {
            return;
        }

        // Extracts image metadata and populates $image_info['APP13'] with raw IPTC data if available.
        // This is a by-reference output parameter. When getimagesize() is called with a second argument,
        // PHP fills it with additional data, including IPTC metadata (APP13) if present.
        $info = @getimagesize($file, $image_info);

        if (!isset($image_info['APP13'])) {
            return;
        }

        $iptc = iptcparse($image_info['APP13']) ?: [];

        // If IPTC "Special Instructions" (tag 2:040) exists, save it as the 'restriction' meta field
        if (!empty($iptc['2#040']) && !empty($iptc['2#040'][0])) {
            update_post_meta($post_id, self::META_FIELDS['restriction'], $iptc['2#040'][0]);
        }

        // If IPTC "Credit" (tag 2:110) exists, save it as the 'credit' meta field
        if (empty($iptc['2#110']) || empty($iptc['2#110'][0])) {
            return;
        }

        update_post_meta($post_id, self::META_FIELDS['credit'], $iptc['2#110'][0]);
    }
}
