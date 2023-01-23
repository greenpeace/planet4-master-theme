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
    const MAX_TAKE_ACTION_PAGES = 100;

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
    private function hooks()
    {
        add_action('cmb2_init', [ $this, 'register_p4_meta_box' ]);
    }

    /**
     * Register P4 meta box.
     */
    public function register_p4_meta_box()
    {
        $this->register_meta_box_post();
        $this->register_meta_box_campaign();
    }

    /**
     * Register Post meta box.
     */
    public function register_meta_box_post()
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
                'desc' => __('Upload an image or select one from the media library to override the background image', 'planet4-master-theme-backend'),
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
     * Register Campaign Information meta box.
     */
    public function register_meta_box_campaign()
    {
        $post_types = [ 'page', 'campaign', 'post' ];
        $analytics_values = AnalyticsValues::from_cache_or_api_or_hardcoded();

        // P4 Datalayer/Campaign fields.
        $p4_campaign_fields = new_cmb2_box(
            [
                'id' => 'p4_campaign_fields',
                'title' => __('Analytics & Tracking', 'planet4-master-theme-backend'),
                'object_types' => $post_types, // Post type.
                'closed' => true, // Keep the metabox closed by default.
                'context' => 'side', // show cmb2box in right sidebar.
                'priority' => 'low',
                'show_names' => false, // Hide the labels.
            ]
        );

        $global_project_options = self::maybe_add_current_post_value($analytics_values->global_projects_options(), 'p4_campaign_name');

        $p4_campaign_fields->add_field(
            [
                'name' => __('Global Project', 'planet4-master-theme-backend'),
                'id' => 'p4_campaign_name',
                'type' => 'select',
                'options' => $global_project_options,
                'attributes' => [
                    'data-validation' => 'required',
                ],
            ]
        );

        $local_projects_options = self::maybe_add_current_post_value($analytics_values->local_projects_options(), 'p4_local_project');
        $p4_campaign_fields->add_field(
            [
                'name' => __('Local Projects', 'planet4-master-theme-backend'),
                'id' => 'p4_local_project',
                'type' => 'select',
                'options' => $local_projects_options,
            ]
        );

        $basket_options = [
            'not set' => __('- Select Basket -', 'planet4-master-theme-backend'),
            'Forests' => 'Forests',
            'Oceans' => 'Oceans',
            'Good Life' => 'Good Life',
            'Food' => 'Food',
            'Climate &amp; Energy' => 'Climate & Energy',
            'Oil' => 'Oil',
            'Plastics' => 'Plastics',
            'Political &amp; Business' => 'Political & Business',
        ];

        $p4_campaign_fields->add_field(
            [
                'name' => __('Basket Name', 'planet4-master-theme-backend'),
                'id' => 'p4_basket_name',
                'type' => 'select',
                'options' => $basket_options,
            ]
        );

        $p4_campaign_fields->add_field(
            [
                'name' => __('Department', 'planet4-master-theme-backend'),
                'id' => 'p4_department',
                'type' => 'text_medium',
                'attributes' => [
                    'placeholder' => __('Add Department', 'planet4-master-theme-backend'),
                ],
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
    public static function save_global_project_id($updated, $action, CMB2_Field $field)
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

    /**
     * If the post has a value that is not in the sheet, keep it in the dropdown so that metaboxes save doesn't set
     * it to another value, but mark it with `[DEPRECATED]` prefix.
     *
     * @param array  $options_array The list of supported global/local projects.
     * @param string $field_name The meta field name.
     * @return array The list with maybe the current post value.
     */
    private static function maybe_add_current_post_value(array $options_array, $field_name): array
    {
        // Not pretty but will work for now.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
        $post_id = $_GET['post'] ?? null;
        if (! $post_id) {
            return $options_array;
        }

        $current_post_meta_value = get_post_meta($post_id, $field_name);

        if (
            isset($current_post_meta_value[0])
            && ! ( array_key_exists(
                $current_post_meta_value[0],
                $options_array
            ) )
        ) {
            $options_array = [ $current_post_meta_value[0] => __('[DEPRECATED] ', 'planet4-master-theme-backend') . $current_post_meta_value[0] ] + $options_array;
        }

        return $options_array;
    }
}
