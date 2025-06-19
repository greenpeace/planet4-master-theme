<?php

namespace P4\MasterTheme;

/**
 * Class P4\MasterTheme\AttachmentsController
 */
class AttachmentsController
{
    /**
     * Credit meta field key
     *
     */
    public const CREDIT_META_FIELD = '_credit_text';

    /**
     * Restrictions meta field key
     *
     */
    public const RESTRICTIONS_META_FIELD = '_media_restriction';

    /**
     * The constructor.
     */
    public function __construct()
    {
        add_action('add_attachment', [$this, 'set_sm_cloud_metadata'], 99);
        add_action('wpml_after_update_attachment_texts', [$this, 'aaaaa'], 1, 2);
        add_filter('image_downsize', [$this, 'overrule_wp_stateless_for_no_images'], 100, 2);
        add_filter('attachment_fields_to_edit', [$this, 'add_image_attachment_fields_to_edit'], 10, 2);
        add_filter('attachment_fields_to_save', [$this, 'add_image_attachment_fields_to_save'], 10, 2);
    }

    // Calls attachment metadata update on importer job.
    // This triggers the wp-stateless hook (if it exists),
    // which sets the sm_cloud metadata for the uploaded file.
    // Wp-stateless is then able to find the file on GCS on step 2,
    // instead of looking for it in the local uploads folder.
    public function set_sm_cloud_metadata ($post_id): void
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

        $cloud_meta = get_post_meta($post_id, 'sm_cloud', true);
        if (! empty($cloud_meta)) {
            return;
        }

        $metadata = wp_get_attachment_metadata($post_id);
        wp_update_attachment_metadata($post_id, $metadata);
    }

    /**
     * WP Stateless plugin short-circuits the image_downsize() process with
     * wpCloud\StatelessMedia\Bootstrap::image_downsize().
     * Contrary to the native function, it will return attachment data even if the
     * attachment is not an image. The attachment is then treated as an image by the
     * function wp_get_attachment_link() generating the link, even for a PDF.
     * We overrule wp-stateless response if file is not an image.
     *
     * @param array    $downsize
     * @param \WP_Post $id
     *
     * @return mixed
     */
    public function overrule_wp_stateless_for_no_images ($downsize, $id)
    {
        return wp_attachment_is_image($id) ? $downsize : false;
    }

    public function aaaaa ($original_attachment_id, $translation): void {
        $original_sm_cloud = get_post_meta($original_attachment_id, 'sm_cloud', true);
        update_post_meta($translation->element_id, 'sm_cloud', $original_sm_cloud);
    }

    /**
     * Add custom media metadata fields.
     *
     * @param array    $form_fields An array of fields included in the attachment form.
     * @param \WP_Post $post The attachment record in the database.
     *
     * @return array Final array of form fields to use.
     */
    public function add_image_attachment_fields_to_edit(array $form_fields, \WP_Post $post): array
    {
        // Add a Credit field.
        $form_fields['credit_text'] = [
            'label' => __('Credit', 'planet4-master-theme-backend'),
            'input' => 'text', // this is default if "input" is omitted.
            'value' => get_post_meta($post->ID, self::CREDIT_META_FIELD, true),
            'helps' => __('The owner of the image.', 'planet4-master-theme-backend'),
        ];

        // Add a Restrictions field.
        $img_restrictions = get_post_meta($post->ID, self::RESTRICTIONS_META_FIELD, true);
        if ($img_restrictions) {
            $form_fields['restrictions_text'] = [
                'label' => __('Restrictions', 'planet4-master-theme-backend'),
                'input' => 'html',
                'html' => $img_restrictions,
            ];
        }

        return $form_fields;
    }

    /**
     * Save custom media metadata fields
     *
     * @param array $post        The $post data for the attachment.
     * @param array $attachment  The $attachment part of the form $_POST ($_POST[attachments][postID]).
     *
     * @return array $post
     */
    public function add_image_attachment_fields_to_save(array $post, array $attachment): array
    {
        if (isset($attachment['credit_text'])) {
            update_post_meta($post['ID'], self::CREDIT_META_FIELD, $attachment['credit_text']);
        }

        return $post;
    }
}
