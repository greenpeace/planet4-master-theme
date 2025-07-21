<?php

namespace P4\MasterTheme;

use WP_Post;

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
        add_action('add_meta_boxes', [$this, 'add_meta_box_search'], 10, 2);
        add_action('save_post', [$this, 'save_meta_box_search'], 10, 2);
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
                'name' => __('Include Related Posts', 'planet4-master-theme-backend'),
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
     * Creates a Metabox on the side of the Add/Edit Post/Page
     * that is used for applying weight to the current Post/Page in search results.
     *
     * @param string  $post_type Post type.
     * @param WP_Post|WP_Comment $post      Post object.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_meta_boxes callback
     */
    public function add_meta_box_search(string $post_type, $post): void
    {
        add_meta_box(
            'meta-box-search',
            'Search',
            [$this, 'view_meta_box_search'],
            ['post', 'page'],
            'side',
            'default',
            [$post]
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Renders a Metabox on the side of the Add/Edit Post/Page.
     *
     * @param WP_Post $post The currently Added/Edited post.
     * phpcs:disable Generic.WhiteSpace.ScopeIndent
     */
    public function view_meta_box_search(WP_Post $post): void
    {
        $weight = get_post_meta($post->ID, 'weight', true);
        $options = get_option('planet4_options');

        echo '<label for="my_meta_box_text">'
            . esc_html__('Weight', 'planet4-master-theme-backend')
            . ' (1-' . esc_attr(Search\Search::DEFAULT_MAX_WEIGHT) . ')</label>
                <input id="weight" type="text" name="weight" value="' . esc_attr($weight) . '" />';

        $script_data = [
            'act_page' => $options['act_page'] ?? null,
            'action_weight' => Search\Search::DEFAULT_ACTION_WEIGHT,
            'page_weight' => Search\Search::DEFAULT_PAGE_WEIGHT,
        ];

        do_action('enqueue_metabox_search_script', $script_data);
    }
    // phpcs:enable Generic.WhiteSpace.ScopeIndent

    /**
     * Saves the Search weight of the Post/Page.
     *
     * @param int     $post_id The ID of the current Post.
     * @param WP_Post $post The current Post.
     */
    public function save_meta_box_search(int $post_id, WP_Post $post): void
    {
        global $pagenow;

        // Ignore autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        // Check user's capabilities.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        // Make sure there's input.
        $weight = filter_input(
            INPUT_POST,
            'weight',
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => Search\Search::DEFAULT_MIN_WEIGHT,
                    'max_range' => Search\Search::DEFAULT_MAX_WEIGHT,
                ],
            ]
        );

        // If this is a new Page then set default weight for it.
        if (!$weight && 'post-new.php' === $pagenow && 'page' === $post->post_type) {
            $weight = Search\Search::DEFAULT_PAGE_WEIGHT;
        }

        // Store weight.
        update_post_meta($post_id, 'weight', $weight);
    }
}
