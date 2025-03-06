<?php

namespace P4\MasterTheme;

use WP_Post;

/**
 * Class P4\MasterTheme\ActionPage
 */
class ActionPage
{
    public const POST_TYPE = 'p4_action';
    public const POST_TYPE_SLUG = 'action';

    public const TAXONOMY = 'action-type';
    public const TAXONOMY_PARAMETER = 'action_type';
    public const TAXONOMY_SLUG = 'action-type';

    public const META_FIELDS = [
        'nav_type',
        'p4_hide_page_title_checkbox',
        'p4_og_title',
        'p4_og_description',
        'p4_og_image',
        'p4_og_image_id',
        'p4_seo_canonical_url',
        'p4_campaign_name',
        'p4_local_project',
        'p4_basket_name',
        'p4_department',
    ];

    /**
     * The constructor.
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
        add_action('init', [ $this, 'register_post_type' ]);
        add_action('init', [ $this, 'register_post_meta' ]);
        add_action('init', [ $this, 'register_taxonomy' ], 2);
        add_action('admin_init', [ $this, 'register_p4_setting_field' ]);

        // Add action slug setting field on permalink settings page.
        add_action('load-options-permalink.php', [ $this, 'p4_load_permalinks' ]);

        // Add default action type setting field on writing settings page.
        add_action('load-options-writing.php', [ $this, 'p4_load_writings' ]);

        // Flush and regenerate rewrite rules on taxonomy change.
        add_action('created_term', [ $this, 'trigger_rewrite_rules' ], 10, 3);
        add_action('edited_term', [ $this, 'trigger_rewrite_rules' ], 10, 3);
        add_action('delete_term', [ $this, 'trigger_rewrite_rules' ], 10, 3);

        // Rewrites the permalink to this taxonomy's page.
        add_filter('term_link', [ $this, 'filter_term_permalink' ], 10, 3);
        add_filter('rewrite_rules_array', [ $this, 'replace_taxonomy_terms_in_rewrite_rules' ], 10, 1);
        add_filter('root_rewrite_rules', [ $this, 'add_terms_rewrite_rules' ], 10, 1);

        // Provides a filter element for the taxonomy in the action list.
        add_action('restrict_manage_posts', [ $this, 'filter_actions_by_action_type' ], 10, 1);

        // Rewrites the permalink to a actions belonging to this taxonomy.
        add_filter('post_type_link', [ $this, 'filter_action_permalink' ], 10, 2);

        // Update action type on quick edit of action.
        add_action('save_post_' . self::POST_TYPE, [ $this, 'save_taxonomy_action_type_on_quick_edit' ], 10, 2);
        // Update action type on add/edit of action.
        add_action('rest_after_insert_' . self::POST_TYPE, [ $this, 'save_taxonomy_action_type_on_edit' ], 10, 1);
    }

    /**
     * Get Action post type slug.
     *
     */
    public function get_action_slug(): string
    {
        // Check for "Action post type slug" setting, if added in Permalink Settings >> Optional section.
        $action_slug = get_option('p4_action_posttype_slug');
        if (! $action_slug) {
            $action_slug = self::POST_TYPE_SLUG;
        }

        return $action_slug;
    }

    /**
     * Register Action page post type.
     */
    public function register_post_type(): void
    {
        // IA: display action page type in admin sidebar.
        $enable_action_post_type = (bool) planet4_get_option('new_ia');

        $labels = [
            'name' => _x('Actions', 'post type general name', 'planet4-master-theme-backend'),
            'singular_name' => _x('Action', 'post type singular name', 'planet4-master-theme-backend'),
            'menu_name' => _x('Actions', 'admin menu', 'planet4-master-theme-backend'),
            'name_admin_bar' => _x('Actions', 'add new on admin bar', 'planet4-master-theme-backend'),
            'add_new' => _x('Add New', 'action', 'planet4-master-theme-backend'),
            'add_new_item' => __('Add New Action', 'planet4-master-theme-backend'),
            'new_item' => __('New Action', 'planet4-master-theme-backend'),
            'edit_item' => __('Edit Action', 'planet4-master-theme-backend'),
            'view_item' => __('View Action', 'planet4-master-theme-backend'),
            'all_items' => __('All Actions', 'planet4-master-theme-backend'),
            'search_items' => __('Search Actions', 'planet4-master-theme-backend'),
            'parent_item_colon' => __('Parent Action:', 'planet4-master-theme-backend'),
            'not_found' => __('No actions found.', 'planet4-master-theme-backend'),
            'not_found_in_trash' => __('No actions found in Trash.', 'planet4-master-theme-backend'),
        ];

        $args = [
            'labels' => $labels,
            'description' => __('Use Actions to inspire your website\'s users to take action on issues and campaigns they care about!', 'planet4-master-theme-backend'), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => $enable_action_post_type,
            'query_var' => true,
            'rewrite' => [
                'slug' => '%' . self::TAXONOMY_PARAMETER . '%',
                'with_front' => false,
            ],
            'has_archive' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'menu_position' => 21,
            'menu_icon' => 'dashicons-editor-textcolor',
            'show_in_rest' => true,
            'taxonomies' => [ 'category', 'post_tag' ],
            'supports' => [
                'page-attributes',
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'revisions',
                // Required to expose meta fields in the REST API.
                'custom-fields',
            ],
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Register a custom taxonomy for Action page post types.
     */
    public function register_taxonomy(): void
    {

        $labels = [
            'name' => _x('Action Type', 'taxonomy general name', 'planet4-master-theme-backend'),
            'singular_name' => _x('Action Type', 'taxonomy singular name', 'planet4-master-theme-backend'),
            'search_items' => __('Search in Action Type', 'planet4-master-theme-backend'),
            'all_items' => __('All Action Types', 'planet4-master-theme-backend'),
            'most_used_items' => null,
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Action Type', 'planet4-master-theme-backend'),
            'update_item' => __('Update Action Type', 'planet4-master-theme-backend'),
            'add_new_item' => __('Add new Action Type', 'planet4-master-theme-backend'),
            'new_item_name' => __('New Action Type', 'planet4-master-theme-backend'),
            'menu_name' => __('Action Type', 'planet4-master-theme-backend'),
        ];

        $args = [
            'hierarchical' => true,
            'labels' => $labels,
            'rewrite' => [
                'slug' => self::TAXONOMY_SLUG,
            ],
            'show_in_rest' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
        ];

        register_taxonomy(self::TAXONOMY, [ self::TAXONOMY_PARAMETER, self::POST_TYPE ], $args);
        register_taxonomy_for_object_type(self::TAXONOMY, self::POST_TYPE);
    }

    /**
     * Register Action page post meta data.
     */
    public function register_post_meta(): void
    {
        $args = [
            'show_in_rest' => true,
            'type' => 'string',
            'single' => true,
        ];

        $options = get_option('planet4_options');

        register_post_meta(
            self::POST_TYPE,
            'action_button_text',
            array_merge(
                $args,
                [ 'default' => $options['take_action_covers_button_text'] ?? __('Take action', 'planet4-master-theme') ]
            )
        );

        register_post_meta(
            self::POST_TYPE,
            'action_button_accessibility_text',
            array_merge(
                $args,
                [ 'default' => __('Get Involved', 'planet4-master-theme') ]
            )
        );

        foreach (self::META_FIELDS as $field) {
            register_post_meta(self::POST_TYPE, $field, $args);
        }
    }

    /**
     * On load of permalinks page, add a action slug setting field.
     */
    public function p4_load_permalinks(): void
    {

        // Show action slug setting field, only if new IA is active.
        if (empty(planet4_get_option('new_ia'))) {
            return;
        }

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_POST['p4_action_posttype_slug'])) {
            update_option(
                'p4_action_posttype_slug',
                // phpcs:ignore WordPress.Security.NonceVerification.Missing
                sanitize_title_with_dashes($_POST['p4_action_posttype_slug'])
            );
        }

        // Add a settings field to the permalink page.
        add_settings_field(
            'p4_action_posttype_slug',
            __('Action post type slug', 'planet4-master-theme-backend'),
            [$this, 'add_action_slug_field'],
            'permalink',
            'optional'
        );
    }

    /**
     * Add Action slug text field on permalinks page.
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    public function add_action_slug_field(): void
    {

        $value = get_option('p4_action_posttype_slug');
        echo '<input type="text" value="' . esc_attr($value) . '" name="p4_action_posttype_slug" id="p4_action_posttype_slug" class="regular-text" /><p>' . esc_html__('The default Action post type slug is "action".', 'planet4-master-theme-backend') . '</p>';
    }
    // phpcs:enable Generic.Files.LineLength.MaxExceeded

    /**
     * Register P4 setting field to WP.
     */
    public function register_p4_setting_field(): void
    {
        register_setting(
            'writing',
            'p4_default_action_type',
            [
                'type' => 'number',
                'sanitize_callback' => 'esc_attr',
            ]
        );
    }

    /**
     * On load of writings page(wp-admin/options-writing.php), add a default action type setting field.
     */
    public function p4_load_writings(): void
    {
        // Show default action type setting field, only if new IA is active.
        if (empty(planet4_get_option('new_ia'))) {
            return;
        }

        // Add a 'Default Action Type' settings field to the writings page.
        add_settings_field(
            'p4_default_action_type',
            __('Default Action Type', 'planet4-master-theme-backend'),
            [ $this, 'add_default_action_type_field' ],
            'writing'
        );
    }

    /**
     * Add Default Action Type dropdown field on writing's page.
     */
    public function add_default_action_type_field(): void
    {
        $value = get_option('p4_default_action_type');
        wp_dropdown_categories(
            [
                'hide_empty' => 0,
                'taxonomy' => self::TAXONOMY,
                'selected' => $value,
                'name' => 'p4_default_action_type',
                'id' => 'p4_default_action_type',
            ]
        );
    }

    /**
     * Filter for term_link.
     *
     * @param string $link     The link value.
     * @param mixed  $term     The term passed to the filter (unused).
     * @param string $taxonomy Taxonomy of the given link.
     *
     * @return string The filtered permalink for this taxonomy.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_filter callback
     */
    public function filter_term_permalink(string $link, $term, string $taxonomy): string
    {
        if (self::TAXONOMY !== $taxonomy) {
            return $link;
        }

        return str_replace(self::TAXONOMY_SLUG . '/', '', $link);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Filter for post type rewrite rules.
     *
     * @param array $rules   Post rewrite rules passed by WordPress.
     *
     * @return array        The filtered post rewrite rules.
     */
    public function replace_taxonomy_terms_in_rewrite_rules(array $rules): array
    {
        // Get planet4 action type taxonomy terms.
        $term_slugs = $this->get_terms_slugs();

        if ($term_slugs) {
            $terms_slugs_regex = implode('|', $term_slugs);

            $new_rules = [];
            foreach ($rules as $match => $rule) {
                $new_match = str_replace('%' . self::TAXONOMY_PARAMETER . '%', "($terms_slugs_regex)", $match);
                $new_rule = str_replace('%' . self::TAXONOMY_PARAMETER . '%', self::TAXONOMY . '=', $rule);
                $new_rules[ $new_match ] = $new_rule;
            }

            return $new_rules;
        }

        return $rules;
    }

    /**
     * Add each taxonomy term as a root rewrite rule.
     * Filter hook for root_rewrite_rules.
     *
     * @param array $rules  Root rewrite rules passed by WordPress.
     *
     * @return array        The filtered root rewrite rules.
     */
    public function add_terms_rewrite_rules(array $rules): array
    {
        // Add a rewrite rule for each slug of this taxonomy type (e.g.: "petition", "event", etc.)
        // for action type pages.
        // e.g | petition/?$ | index.php?action-type=petition | .
        $terms_slugs = $this->get_terms_slugs();

        if ($terms_slugs) {
            foreach ($terms_slugs as $slug) {
                $rules[ $slug . '/?$' ] = 'index.php?' . self::TAXONOMY . '=' . $slug;
            }
        }

        return $rules;
    }

    /**
     * Get the slugs for all terms in this taxonomy.
     *
     * @return array Flat array containing the slug for every term.
     */
    private function get_terms_slugs(): array
    {
        // Get planet4 action type taxonomy terms.
        $terms = $this->get_all_terms();

        if (! is_wp_error($terms)) {
            $term_slugs = [];
            if (! empty($terms)) {
                foreach ($terms as $term) {
                    $term_slugs[] = $term->slug;
                }
            }

            return $term_slugs;
        }

        return [];
    }

    /**
     * Get all taxonomy's terms, despite if wpml plugin is activated.
     *
     * @return array|int|WP_Error
     */
    public function get_all_terms()
    {
        // Get taxonomy terms if wpml plugin is installed and activated.
        if (function_exists('is_plugin_active') && is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            return $this->get_multilingual_terms();
        }
        return $this->get_action_terms();
    }

    /**
     * Get all taxonomy's terms (for all languages available) if wpml is enabled.
     *
     * @return WP_Term[]
     */
    public function get_multilingual_terms(): array
    {
        $all_terms = [];
        $current_lang = apply_filters('wpml_current_language', null);
        $available_languages = apply_filters('wpml_active_languages', null, 'orderby=id&order=desc') ?? [];

        foreach ($available_languages as $lang) {
            do_action('wpml_switch_language', $lang['language_code']);
            $terms = get_terms(
                [
                    'fields' => 'all',
                    'hide_empty' => false,
                    'taxonomy' => self::TAXONOMY,
                ]
            );
            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }

            $all_terms = array_merge($all_terms, $terms);
        }

        do_action('wpml_switch_language', $current_lang);

        return $all_terms;
    }

    /**
     * Hook into quick edit action post type.
     *
     * @param int     $post_id Id of the saved post.
     * @param WP_Post $post    Post object.
     */
    public function save_taxonomy_action_type_on_quick_edit(int $post_id, WP_Post $post): void
    {
        // Ignore autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user's capabilities.
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $this->save_taxonomy_action_type($post);
    }

    /**
     * Hook into add/edit action post type.
     *
     * @param WP_Post $post Post object.
     */
    public function save_taxonomy_action_type_on_edit(WP_Post $post): void
    {
        $this->save_taxonomy_action_type($post);
    }

    /**
     * Add default term of the taxonomy to the action if the action has not any taxonomy's terms assigned to it.
     * Assign only the first term, if more than one terms are assigned to the action.
     *
     * @param WP_Post $post    Post object.
     */
    public function save_taxonomy_action_type(WP_Post $post): void
    {
        // Check if post type is Action.
        if (self::POST_TYPE !== $post->post_type) {
            return;
        }

        // Check if Action has a action type term assigned to it and if none assigned,
        // assign the default p4 action type term.
        $terms = wp_get_object_terms($post->ID, self::TAXONOMY);
        if (is_wp_error($terms)) {
            return;
        }

        $default_action_type = $this->get_default_action_type();

        // Assign default actiontype, if no term is assigned to action.
        if (empty($terms)) {
            if ($default_action_type instanceof \WP_Term) {
                wp_set_post_terms($post->ID, [ $default_action_type->term_id ], self::TAXONOMY);
            }
        } elseif (count($terms) > 1 && $terms[0] instanceof \WP_Term) {
            // Assign the first term, if more than one terms are assigned.
            wp_set_post_terms($post->ID, [ $terms[0]->term_id ], self::TAXONOMY, false);
        }
    }

    /**
     * Get default P4 action-type.
     *
     * @return WP_term|int|WP_Error
     */
    public function get_default_action_type()
    {
        $default_action_type = get_option('p4_default_action_type', 0);

        if (0 === $default_action_type) {
            // If default action type setting not found, use taxonomy's first term.
            $all_terms = $this->get_action_terms();
            $default_action_type = $all_terms[0] ?? 0;
        } else {
            $default_action_type = get_term($default_action_type, self::TAXONOMY);
        }

        return $default_action_type;
    }

    /**
     * Get taxonomy's terms.
     *
     * @return array|int|WP_Error
     */
    public function get_action_terms()
    {
        // Get planet4 action type taxonomy terms.
        return get_terms(
            [
                'fields' => 'all',
                'hide_empty' => false,
                'taxonomy' => self::TAXONOMY,
            ]
        );
    }

    /**
     * Flush and regenerate rewrite rules when a new action_type is created/edited/deleted.
     *
     * @param int    $term_id  Term ID.
     * @param int    $tt_id    Term taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_action callback
     */
    public function trigger_rewrite_rules(int $term_id, int $tt_id, string $taxonomy): void
    {
        if (self::TAXONOMY !== $taxonomy) {
            return;
        }

        flush_rewrite_rules();
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Add a filter element to the actions list that allows filtering by the custom(action type) taxonomy terms.
     * Action for restrict_manage_posts.
     *
     * @param string $post_type WordPress post type slug.
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    public function filter_actions_by_action_type(string $post_type): void
    {
        // Apply filter only for action post type.
        if (self::POST_TYPE !== $post_type) {
            return;
        }

        // Retrieve taxonomy terms.
        $terms = get_terms(self::TAXONOMY);

        // Display filter HTML.
        ?>
        <select name="<?php echo esc_attr(self::TAXONOMY); ?>" id="<?php echo esc_attr(self::TAXONOMY); ?>" class="postform">
            <option value=""><?php echo esc_html__('All Action Types', 'planet4-master-theme-backend'); ?></option>

            <?php
            foreach ($terms as $term) {
                printf(
                    '<option value="%1$s" %2$s>%3$s</option>',
                    esc_html($term->slug),
                    ( ( isset($_GET[ self::TAXONOMY ]) && ( $_GET[ self::TAXONOMY ] === $term->slug ) ) ? ' selected="selected"' : '' ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    esc_html($term->name)
                );
            }
            ?>
        </select>
        <?php
    }
    // phpcs:enable Generic.Files.LineLength.MaxExceeded

    /**
     * Get Default Action Type slug.
     *
     */
    public function get_default_action_type_slug(): string
    {
        // Check for "Default Action Type" setting, if added on "Writing Settings" page(wp-admin/options-writing.php).
        $term_id = get_option('p4_default_action_type');
        $term_object = get_term($term_id);
        $action_type_slug = self::TAXONOMY_SLUG; // In case no action type taxonomy added(fallback condition).
        if (! is_wp_error($term_object) && ! empty($term_object) && is_object($term_object)) {
            $action_type_slug = $term_object->slug;
        }

        return $action_type_slug;
    }

    /**
     * Replace action-type placeholder with the action_type term for actions permalinks.
     * Filter for post_type_link.
     *
     * @param string  $permalink The post's permalink.
     * @param WP_Post $post      The post in question.
     *
     * @return string   The filtered permalink.
     */
    public function filter_action_permalink(string $permalink, WP_Post $post): string
    {

        // Apply filter only for action post type.
        if (self::POST_TYPE !== $post->post_type) {
            return $permalink;
        }

        // Get action's taxonomy terms.
        $terms = wp_get_object_terms($post->ID, self::TAXONOMY);

        if (! is_wp_error($terms) && ! empty($terms) && is_object($terms[0])) {
            $taxonomy_slug = $terms[0]->slug;
            return str_replace('%' . self::TAXONOMY_PARAMETER . '%', $taxonomy_slug, $permalink);
        }

        $action_type_slug = $this->get_default_action_type_slug();

        // Replace the default action slug in permalink, if no action type selected.
        return str_replace('%' . self::TAXONOMY_PARAMETER . '%', $action_type_slug, $permalink);
    }
}
