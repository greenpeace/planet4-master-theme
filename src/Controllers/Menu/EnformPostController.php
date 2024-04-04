<?php

/**
 * Enform Post Controller class
 */

namespace P4\MasterTheme\Controllers\Menu;

use P4\MasterTheme\Controllers\EnformFieldsListTable;
use P4\MasterTheme\Controllers\EnformQuestionsListTable;
use P4\MasterTheme\Controllers\EnsapiController as Ensapi;

/**
 * Class EnformPostController
 *
 * Creates and registers p4_en custom post type.
 * Also add filters for p4_en list page.
 */
class EnformPostController extends Controller
{
    /**
     * Post type name.
     */
    public const POST_TYPE = 'p4en_form';

    /**
     * Custom meta field where fields configuration is saved to.
     */
    private const FIELDS_META = 'p4enform_fields';

    /**
     * Hooks all the needed functions to load the class.
     */
    public function load(): void
    {
        parent::load();
        // Register the hooks.
        $this->hooks();
    }

    /**
     * Class hooks.
     */
    private function hooks(): void
    {
        add_action('init', [$this, 'register_post_type']);
        add_shortcode(self::POST_TYPE, [$this, 'handle_form_shortcode']);
        add_filter('post_row_actions', [$this, 'modify_post_row_actions'], 10, 2);

        add_action('add_meta_boxes', [$this, 'add_form_meta_box'], 10, 2);
        add_action('add_meta_boxes', [$this, 'add_selected_meta_box'], 11, 2);
        add_action('add_meta_boxes', [$this, 'add_fields_meta_box'], 12, 2);
        add_action('add_meta_boxes', [$this, 'add_questions_custom_box']);
        add_action('add_meta_boxes', [$this, 'add_optins_custom_box']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'save_fields_meta_box'], 10, 2);

        add_action('wp_ajax_get_supporter_question_by_id', [$this, 'get_supporter_question_by_id']);
        add_action('wp_ajax_nopriv_get_supporter_question_by_id', [$this, 'get_supporter_question_by_id']);
    }

    /**
     * Create menu/submenu entry.
     */
    public function create_admin_menu(): void
    {
        $current_user = wp_get_current_user();

        if (
            ! in_array('administrator', $current_user->roles, true) &&
            ! in_array('editor', $current_user->roles, true)
        ) {
            return;
        }
        add_submenu_page(
            P4_MASTER_THEME_EN_SLUG_NAME,
            __('All EN Forms', 'planet4-engagingnetworks-backend'),
            __('All EN Forms', 'planet4-engagingnetworks-backend'),
            'edit_posts',
            'edit.php?post_type=' . self::POST_TYPE
        );

        add_submenu_page(
            P4_MASTER_THEME_EN_SLUG_NAME,
            __('Add New', 'planet4-engagingnetworks-backend'),
            __('Add New', 'planet4-engagingnetworks-backend'),
            'edit_posts',
            'post-new.php?post_type=' . self::POST_TYPE
        );

        // Set hook after screen is determined to load assets for add/edit page.
        add_action('current_screen', [$this, 'load_assets']);
    }

    /**
     * Register en forms custom post type.
     */
    public function register_post_type(): void
    {
        $labels = [
            'name' => _x('Engaging Network Forms', 'en forms', 'planet4-engagingnetworks-backend'),
            'singular_name' => _x('Engaging Network Form', 'en form', 'planet4-engagingnetworks-backend'),
            'menu_name' => _x('En Forms Menu', 'admin menu', 'planet4-engagingnetworks-backend'),
            'name_admin_bar' => _x('En Form', 'add new on admin bar', 'planet4-engagingnetworks-backend'),
            'add_new' => _x('Add New', 'en form', 'planet4-engagingnetworks-backend'),
            'add_new_item' => __('Add New EN Form', 'planet4-engagingnetworks-backend'),
            'new_item' => __('New EN Form', 'planet4-engagingnetworks-backend'),
            'edit_item' => __('Edit EN Form', 'planet4-engagingnetworks-backend'),
            'view_item' => __('View EN Form', 'planet4-engagingnetworks-backend'),
            'all_items' => __('All EN Forms', 'planet4-engagingnetworks-backend'),
            'search_items' => __('Search EN Forms', 'planet4-engagingnetworks-backend'),
            'parent_item_colon' => __('Parent EN Forms:', 'planet4-engagingnetworks-backend'),
            'not_found' => __('No en forms found.', 'planet4-engagingnetworks-backend'),
            'not_found_in_trash' => __('No en forms found in Trash.', 'planet4-engagingnetworks-backend'),
        ];

        register_post_type(
            self::POST_TYPE,
            [
                'labels' => $labels,
                'description' => __('EN Forms', 'planet4-engagingnetworks-backend'),
                'rewrite' => false,
                'query_var' => false,
                'public' => false,
                'publicly_queryable' => false,
                'capability_type' => 'page',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => null,
                'exclude_from_search' => true,
                'map_meta_cap' => true,
                // necessary in order to use WordPress default custom post type list page.
                'show_ui' => true,
                // hide it from menu, as we are using custom submenu pages.
                'show_in_menu' => false,
                'supports' => ['title'],
                'show_in_rest' => true,
            ]
        );

        $custom_meta_args = [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ];
        register_meta(self::POST_TYPE, self::FIELDS_META, $custom_meta_args);

        \register_rest_field(
            self::POST_TYPE,
            self::FIELDS_META,
            [
                'get_callback' => function ($obj) {
                    return \get_post_meta(
                        (int) $obj['id'],
                        self::FIELDS_META,
                        true
                    );
                },
            ]
        );
    }

    /**
     * Filter for post_row_actions. Alters edit action link and removes Quick edit action.
     *
     * @param array $actions An array of row action links. Defaults are
     *                          'Edit', 'Quick Edit', 'Restore', 'Trash',
     *                          'Delete Permanently', 'Preview', and 'View'.
     * @param \WP_Post $post The post object.
     *
     * @return array  The filtered actions array.
     */
    public function modify_post_row_actions(array $actions, \WP_Post $post): array
    {
        // Check if post is of p4en_form_post type.
        if (self::POST_TYPE === $post->post_type) {
            /*
             * Hide Quick Edit.
             */
            $custom_actions = [
                'inline hide-if-no-js' => '',
            ];

            $actions = array_merge($actions, $custom_actions);
        }

        return $actions;
    }

    /**
     * Adds shortcode for this custom post type.
     *
     * @param array $atts Array of attributes for the shortcode.
     */
    public function handle_form_shortcode(array $atts): void
    {
        global $pagenow;

        // Define attributes and their defaults.
        $atts = array_merge(
            [
                'id' => 'id',
                'en_form_style' => 'full-width',
            ],
            $atts
        );

        $post_id = filter_input(INPUT_GET, 'post', FILTER_VALIDATE_INT);

        if (
            ! is_admin() &&
            ! ('post.php' === $pagenow && $post_id && self::POST_TYPE === get_post_type($post_id)) &&
            ! ('admin-ajax.php' === $pagenow && self::POST_TYPE === get_post_type($atts['id']))
        ) {
            return;
        }
        $fields = get_post_meta($atts['id'], self::FIELDS_META, true);

        $data = [
            'form_fields' => $fields,
            'en_form_style' => $atts['en_form_style'],
        ];

        $this->view->enform_post($data);
    }

    /**
     * Creates a Meta box for the Selected Components of the current EN Form.
     *
     * @param string   $post_type The current post type (unused).
     * @param \WP_Post $post The currently Added/Edited EN Form.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function add_form_meta_box(string $post_type, \WP_Post $post): void
    {
        add_meta_box(
            'meta-box-form',
            __('Form preview', 'planet4-engagingnetworks-backend'),
            [$this, 'view_meta_box_form'],
            [self::POST_TYPE],
            'normal',
            'high',
            $post
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * View an EN form.
     *
     * @param \WP_Post $post The currently Added/Edited EN Form.
     */
    public function view_meta_box_form(\WP_Post $post): void
    {
        echo do_shortcode('[' . self::POST_TYPE . ' id="' . $post->ID . '" /]');
    }

    /**
     * Creates a Meta box for the Selected Components of the current EN Form.
     *
     * @param string   $post_type The current post type (unused).
     * @param \WP_Post $post The currently Added/Edited EN Form.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function add_selected_meta_box(string $post_type, \WP_Post $post): void
    {
        add_meta_box(
            'meta-box-selected',
            __('Selected Components', 'planet4-engagingnetworks-backend'),
            [$this, 'view_selected_meta_box'],
            [self::POST_TYPE],
            'normal',
            'high',
            $post
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Prepares data to render the Selected Components meta box.
     *
     * @param \WP_Post $post The currently Added/Edited EN Form.
     */
    public function view_selected_meta_box(\WP_Post $post): void
    {
        $form_fields = get_post_meta($post->ID, self::FIELDS_META, true);
        $this->view->en_selected_meta_box(
            [
                'fields' => wp_json_encode($form_fields),
            ]
        );
    }

    /**
     * Adds available fields custom meta box to p4en_form edit post page.
     *
     * @param string   $post_type The current post type (unused).
     * @param \WP_Post $post The currently Added/Edited EN Form.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function add_fields_meta_box(string $post_type, \WP_Post $post): void
    {
        add_meta_box(
            'fields_list_box',
            __('Available Fields', 'planet4-engagingnetworks-backend'),
            [$this, 'display_fields_custom_box'],
            self::POST_TYPE,
            'normal',
            'high',
            $post
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Display fields custom box content.
     */
    public function display_fields_custom_box(): void
    {
        $list_table = new EnformFieldsListTable();
        $list_table->prepare_items();
        $list_table->display();
    }

    /**
     * Adds a meta box for the EN questions.
     *
     * Adds available questions custom meta box to p4en_form edit post page.
     */
    public function add_questions_custom_box(): void
    {
        add_meta_box(
            'questions_list_box',
            __('Available Questions', 'planet4-engagingnetworks-backend'),
            [$this, 'display_questions_custom_box'],
            self::POST_TYPE
        );
    }

    /**
     * Display questions custom box content.
     */
    public function display_questions_custom_box(): void
    {
        $list_table = new EnformQuestionsListTable('GEN');
        $list_table->prepare_items();
        $list_table->display();
    }

    /**
     * Adds available opt-ins custom meta box to p4en_form edit post page.
     */
    public function add_optins_custom_box(): void
    {
        add_meta_box(
            'optins_list_box',
            __('Available Opt-ins', 'planet4-engagingnetworks-backend'),
            [$this, 'display_optins_custom_box'],
            self::POST_TYPE
        );
    }

    /**
     * Display opt-ins custom box content.
     */
    public function display_optins_custom_box(): void
    {
        $list_table = new EnformQuestionsListTable('OPT');
        $list_table->prepare_items();
        $list_table->display();
    }

    /**
     * Retrieves data of a specific question/opt-in.
     */
    public function get_supporter_question_by_id(): void
    {
        // If this is an ajax call.
        if (! wp_doing_ajax()) {
            return;
        }
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $main_settings = get_option('p4en_main_settings');
        $ens_private_token = $main_settings['p4en_private_api'];
        $ens_api = new Ensapi($ens_private_token);
        $response = $ens_api->get_supporter_question_by_id($id);

        wp_send_json($response);
    }

    /**
     * Add underscore templates to footer.
     */
    public function print_admin_footer_scripts(): void
    {
        $this->view->view_template('selected_enform_fields', [], 'block_templates/');
    }

    /**
     * Hook load new page assets conditionally based on current page.
     */
    public function load_assets(): void
    {
        global $pagenow, $typenow;
        $pages = [
            'post.php',
            'post-new.php',
        ];

        // Load assets conditionally using pagenow, typenow on new/edit form page.
        if (! in_array($pagenow, $pages, true) || self::POST_TYPE !== $typenow) {
            return;
        }
        add_action("load-$pagenow", [$this, 'load__new_page_assets']);
        add_action('admin_print_footer_scripts', [$this, 'print_admin_footer_scripts'], 1);
    }

    /**
     * Load assets for new/edit form page.
     */
    public function load__new_page_assets(): void
    {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style(
            'p4en_admin_style_blocks',
            get_template_directory_uri() . '/admin/css/admin_en.css',
            [],
            \P4\MasterTheme\Loader::theme_file_ver('admin/css/admin_en.css'),
        );
        \P4\MasterTheme\Loader::enqueue_versioned_script(
            'admin/js/enforms.js',
            [
                'jquery',
                'wp-backbone',
            ]
        );
    }

    /**
     * Saves the p4 enform fields of the Post.
     *
     * @param int             $post_id The ID of the current Post.
     * @param \WP_Post $post The current Post.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function save_fields_meta_box(int $post_id, \WP_Post $post): void
    {
        global $pagenow;

        // Ignore autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user's capabilities.
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check post input.
        $form_fields = filter_input(
            INPUT_POST,
            self::FIELDS_META
        );

        // If this is a new post then set form fields meta.
        if (! $form_fields || 'post.php' !== $pagenow) {
            return;
        }

        $form_fields = json_decode(($form_fields));

        // Store form fields meta.
        update_post_meta($post_id, self::FIELDS_META, $form_fields);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
