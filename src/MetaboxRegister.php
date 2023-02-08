<?php

namespace P4\MasterTheme;

use CMB2_Field;

/**
 * Class MetaboxRegister
 */
class MetaboxRegister
{
    /**
     * The maximum number of take action pages to show in dropdown.
     *
     * @const int MAX_TAKE_ACTION_PAGES
     */
    public const MAX_TAKE_ACTION_PAGES = 100;

    /**
     * MetaboxRegister constructor.
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Class hooks.
     */
    private function hooks(): void
    {
        add_action('cmb2_init', [ $this, 'register_p4_meta_box' ]);
    }

    /**
     * Register P4 meta box.
     */
    public function register_p4_meta_box(): void
    {
        $this->register_meta_box_post();
    }

    /**
     * Register Post meta box.
     */
    public function register_meta_box_post(): void
    {

        $p4_post = new_cmb2_box(
            [
                'id' => 'p4_metabox_post',
                'title' => __('Post Articles Element Fields', 'planet4-master-theme-backend'),
                'object_types' => [ 'post' ],
            ]
        );

        $p4_post->add_field(
            [
                'name' => __('Author Override', 'planet4-master-theme-backend'),
                'desc' => __('Enter author name if you want to override the author', 'planet4-master-theme-backend'),
                'id' => 'p4_author_override',
                'type' => 'text_medium',
            ]
        );

        $p4_post->add_field(
            [
                'name' => __('Include Articles In Post', 'planet4-master-theme-backend'),
                'id' => 'include_articles',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No',
                ],
            ]
        );

        $p4_post->add_field(
            [
                'name' => __('Background Image Override', 'planet4-master-theme-backend'),
                'desc' => __(
                    'Upload an image or select one from the media library to override the background image',
                    'planet4-master-theme-backend'
                ),
                'id' => 'p4_background_image_override',
                'type' => 'file',
                'options' => [
                    'url' => false,
                ],
                'text' => [
                    'add_upload_file_text' => __('Add Image', 'planet4-master-theme-backend'),
                ],
                'preview_size' => 'large',
            ]
        );
    }

    /**
     * Look up the ID of the global campaign and save it on the post.
     *
     * @param bool       $updated Whether the field is being updated.
     * @param string     $action The action being performed on the field.
     * @param CMB2_Field $field The field being updated.
     */
    public static function save_global_project_id(bool $updated, string $action, CMB2_Field $field): void
    {
        if (! $updated) {
            return;
        }
        if ('removed' === $action) {
            update_post_meta($field->object_id(), 'p4_global_project_tracking_id', null);

            return;
        }

        $project_id = AnalyticsValues::from_cache_or_api_or_hardcoded()->get_id_for_global_project($field->value());
        update_post_meta($field->object_id, 'p4_global_project_tracking_id', $project_id);
    }
}
