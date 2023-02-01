<?php

namespace P4\MasterTheme;

use WP_Post;
use WP_Term;

/**
 * Class CustomTaxonomy
 */
class CustomTaxonomy
{
    public const TAXONOMY = 'p4-page-type';
    public const TAXONOMY_PARAMETER = 'p4_page_type';
    public const TAXONOMY_SLUG = 'page-type';

    /**
     * Reading time option field name
     *
     */
    public const READING_TIME_FIELD = 'reading_time';

    /**
     * CustomTaxonomy constructor.
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Register actions for WordPress hooks and filters.
     */
    private function hooks(): void
    {
        add_action('init', [ $this, 'register_taxonomy' ], 2);
        add_action('created_term', [ $this, 'trigger_rewrite_rules' ], 10, 3);
        add_action('edited_term', [ $this, 'trigger_rewrite_rules' ], 10, 3);
        add_action('delete_term', [ $this, 'trigger_rewrite_rules' ], 10, 3);
        add_action('save_post', [ $this, 'save_taxonomy_page_type' ], 10, 2);
        add_filter('available_permalink_structure_tags', [ $this, 'add_taxonomy_as_permalink_structure' ], 10, 1);

        // Rewrites the permalink to a post belonging to this taxonomy.
        add_filter('post_link', [ $this, 'filter_permalink' ], 10, 2);

        // Rewrites the permalink to this taxonomy's page.
        add_filter('term_link', [ $this, 'filter_term_permalink' ], 10, 3);
        add_filter('post_rewrite_rules', [ $this, 'replace_taxonomy_terms_in_rewrite_rules' ], 10, 1);
        add_filter('root_rewrite_rules', [ $this, 'add_terms_rewrite_rules' ], 10, 1);

        // Provides a filter element for the taxonomy in the posts list.
        add_action('restrict_manage_posts', [ $this, 'filter_posts_by_page_type' ], 10, 1);

        // Reading time option.
        add_action(self::TAXONOMY . '_add_form_fields', [ $this, 'add_taxonomy_form_fields' ], 10);
        add_action(self::TAXONOMY . '_edit_form_fields', [ $this, 'add_taxonomy_form_fields' ], 10);
        add_action('edited_' . self::TAXONOMY, [ $this, 'save_taxonomy_meta' ], 10);
        add_action('created_' . self::TAXONOMY, [ $this, 'save_taxonomy_meta' ], 10);
        add_action('manage_edit-' . self::TAXONOMY . '_columns', [ $this, 'add_taxonomy_column' ], 10, 3);
        add_action('manage_' . self::TAXONOMY . '_custom_column', [ $this, 'add_taxonomy_column_content' ], 10, 3);
    }

    /**
     * Add p4_page_type structure in available permalink tags for Settings -> Permalinks page.
     * available_permalink_structure_tags filter.
     *
     * @param array $tags   Permalink tags that are displayed in Settings -> Permalinks.
     *
     * @return mixed
     */
    public function add_taxonomy_as_permalink_structure(array $tags)
    {
        $tags[ self::TAXONOMY_PARAMETER ] = __('P4 page type (A p4 page type term.)', 'planet4-master-theme-backend');

        return $tags;
    }

    /**
     * Add a dropdown to choose planet4 post type.
     *
     * @param WP_Post $post The WordPress that will be filtered/edited.
     */
    public function create_taxonomy_metabox_markup(WP_Post $post): void
    {
        $attached_type = get_the_terms($post, self::TAXONOMY);
        $current_type = ( is_array($attached_type) ) ? $attached_type[0]->term_id : - 1;
        $all_types = $this->get_terms();
        if (-1 === $current_type) {
            // Assign default p4-pagetype for new POST.
            $default_p4_pagetype = $this->get_default_p4_pagetype();
            $current_type = $default_p4_pagetype->slug;
        }

        wp_nonce_field('p4-save-page-type', 'p4-page-type-nonce');
        ?>
        <select name="<?php echo esc_attr(self::TAXONOMY); ?>">
            <?php foreach ($all_types as $term) : ?>
                <option <?php selected($current_type, $term->term_id); ?>
                        value="<?php echo esc_attr($term->term_id); ?>">
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Replace p4_page_type placeholder with the p4_page_type term for posts permalinks.
     * Filter for post_link.
     *
     * @param string  $permalink The post's permalink.
     * @param WP_Post $post      The post in question.
     *
     * @return string   The filtered permalink.
     */
    public function filter_permalink(string $permalink, WP_Post $post): string
    {

        if (strpos($permalink, '%' . self::TAXONOMY_PARAMETER . '%') === false) {
            return $permalink;
        }

        // Get post's taxonomy terms.
        $terms = wp_get_object_terms($post->ID, self::TAXONOMY);
        $all_terms = $this->get_terms();

        // Assign story slug if the taxonomy does not have any terms.
        $taxonomy_slug = 'story';
        if (! is_wp_error($terms) && ! empty($terms) && is_object($terms[0])) {
            $taxonomy_slug = $terms[0]->slug;
        } elseif (! is_wp_error($terms) && empty($terms)) {
            if (! is_wp_error($all_terms) && ! empty($all_terms) && is_object($all_terms[0])) {
                $taxonomy_slug = $all_terms[0]->slug;
            }
        }

        return str_replace('%' . self::TAXONOMY_PARAMETER . '%', $taxonomy_slug, $permalink);
    }

    /**
     * Get taxonomy's terms.
     *
     * @return array|int|WP_Error
     */
    public function get_terms()
    {
        // Get planet4 page type taxonomy terms.
        return get_terms(
            [
                'fields' => 'all',
                'hide_empty' => false,
                'taxonomy' => self::TAXONOMY,
            ]
        );
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
        return $this->get_terms();
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
        $available_languages = apply_filters('wpml_active_languages', null, 'orderby=id&order=desc');

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
     * Get default P4 pagetype.
     *
     * @return WP_term|int|WP_Error
     */
    public function get_default_p4_pagetype()
    {
        $options = get_option('planet4_options');
        $default_p4_pagetype = $options['default_p4_pagetype'] ?? 0;

        if (0 === $default_p4_pagetype) {
            // If default p4-pagetype setting not found, use taxonomy's first term.
            $all_terms = $this->get_terms();
            $default_p4_pagetype = $all_terms[0] ?? 0;
        } else {
            $default_p4_pagetype = get_term($default_p4_pagetype, self::TAXONOMY);
        }

        return $default_p4_pagetype;
    }

    /**
     * Register a custom taxonomy for planet4 post types.
     */
    public function register_taxonomy(): void
    {

        $p4_page_type = [
            'name' => _x('Post Types', 'taxonomy general name', 'planet4-master-theme-backend'),
            'singular_name' => _x('Post Type', 'taxonomy singular name', 'planet4-master-theme-backend'),
            'search_items' => __('Search in Post Type', 'planet4-master-theme-backend'),
            'all_items' => __('All Post Types', 'planet4-master-theme-backend'),
            'most_used_items' => null,
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Post Type', 'planet4-master-theme-backend'),
            'update_item' => __('Update Post Type', 'planet4-master-theme-backend'),
            'add_new_item' => __('Add new Post Type', 'planet4-master-theme-backend'),
            'new_item_name' => __('New Post Type', 'planet4-master-theme-backend'),
            'menu_name' => __('Post Types', 'planet4-master-theme-backend'),
        ];

        $args = [
            'hierarchical' => false,
            'labels' => $p4_page_type,
            'rewrite' => [
                'slug' => self::TAXONOMY_SLUG,
            ],
            'show_in_rest' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'meta_box_cb' => [ $this, 'create_taxonomy_metabox_markup' ],
        ];

        register_taxonomy(self::TAXONOMY, [ self::TAXONOMY_PARAMETER, 'post' ], $args);
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
     * Get the slugs for all terms in this taxonomy.
     *
     * @return array Flat array containing the slug for every term.
     */
    private function get_terms_slugs(): array
    {
        // Get planet4 page type taxonomy terms.
        $terms = $this->get_all_terms();

        if (! is_wp_error($terms)) {
            $term_slugs = [];
            if (! empty($terms)) {
                foreach ($terms as $term) {
                    $term_slugs[] = $term->slug;
                }
            } elseif (empty($terms)) {
                // Add story slug also if the taxonomy does not have any terms.
                $term_slugs[] = 'story';
            }

            return $term_slugs;
        }

        return [];
    }

    /**
     * Filter for post_rewrite_rules.
     *
     * @param array $rules   Post rewrite rules passed by WordPress.
     *
     * @return array        The filtered post rewrite rules.
     */
    public function replace_taxonomy_terms_in_rewrite_rules(array $rules): array
    {
        // Get planet4 page type taxonomy terms.
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
        // Add a rewrite rule for each slug of this taxonomy type (e.g.: "publication", "story", etc.)
        // for p4 page type pages.
        // e.g | story/?$ | index.php?p4-page-type=story | .
        $terms_slugs = $this->get_terms_slugs();

        if ($terms_slugs) {
            foreach ($terms_slugs as $slug) {
                $rules[ $slug . '/?$' ] = 'index.php?' . self::TAXONOMY . '=' . $slug;
            }
        }

        return $rules;
    }

    /**
     * Regenerate and flush rewrite rules when a new p4_page_type is created/edited/deleted.
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
     * Add first term of the taxonomy to the post if the post has not any taxonomy's terms assigned to it.
     * Assign only the first term, if more than one terms are assigned to the post.
     *
     * @param int     $post_id Id of the saved post.
     * @param WP_Post $post    Post object.
     */
    public function save_taxonomy_page_type(int $post_id, WP_Post $post): void
    {

        // Ignore autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user's capabilities.
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // Allow p4-page-type to be set from edit post and quick edit pages.
        // Make sure there's input.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
        if (
            isset($_POST[ self::TAXONOMY ]) && 'post' === $post->post_type &&
            filter_var(wp_unslash($_POST[ self::TAXONOMY ]), FILTER_VALIDATE_INT)
        ) {
            $selected = get_term_by('id', intval($_POST[ self::TAXONOMY ]), self::TAXONOMY);
			// phpcs:enable
            if (false !== $selected && ! is_wp_error($selected)) {
                // Save post type.
                wp_set_post_terms($post_id, [ $selected->term_id ], self::TAXONOMY);
            }
        }

        // Check if post type is POST.
        // Check if post has a p4 page type term assigned to it and if none if assigned,
        // assign the default p4 page type term.
        if ('post' !== $post->post_type) {
            return;
        }

        // Check if post has an assigned term to it.
        $terms = wp_get_object_terms($post_id, self::TAXONOMY);
        if (is_wp_error($terms)) {
            return;
        }

        $default_p4_pagetype = $this->get_default_p4_pagetype();

        // Assign default p4-pagetype, if no term is assigned to post.
        if (empty($terms)) {
            if ($default_p4_pagetype instanceof \WP_Term) {
                wp_set_post_terms($post_id, [ $default_p4_pagetype->term_id ], self::TAXONOMY);
            }
        // Assign the first term, if more than one terms are assigned.
        } elseif (count($terms) > 1 && $terms[0] instanceof \WP_Term) {
            wp_set_post_terms($post_id, [ $terms[0]->term_id ], self::TAXONOMY);
        }
    }

    /**
     * Adds a filter element to the posts list that allows filtering by the custom taxonomy terms.
     * Action for restrict_manage_posts.
     *
     * @param string $post_type WordPress post type slug.
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    public function filter_posts_by_page_type(string $post_type): void
    {
        // Apply this only to a specific post type.
        if ('post' !== $post_type) {
            return;
        }

        // Retrieve taxonomy terms.
        $terms = get_terms(self::TAXONOMY);

        // Display filter HTML.
        ?>
        <select name="<?php echo esc_attr(self::TAXONOMY); ?>" id="<?php echo esc_attr(self::TAXONOMY); ?>" class="postform">
            <option value=""><?php echo esc_html__('All Post Types', 'planet4-master-theme-backend'); ?></option>

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
     * Adds a taxonomy column.
     *
     * @param array $columns The columns.
     *
     * @return array
     */
    public function add_taxonomy_column(array $columns): array
    {
        $columns[ self::READING_TIME_FIELD ] = __('Reading time', 'planet4-master-theme-backend');
        return $columns;
    }

    /**
     * Adds a taxonomy column content.
     *
     * @param string $string      Blank string.
     * @param string $column_name Name of the column.
     * @param int    $term_id     Term ID.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_action callback
     */
    public function add_taxonomy_column_content(string $string, string $column_name, int $term_id): void
    {
        if (self::READING_TIME_FIELD !== $column_name) {
            return;
        }

        $use_reading_time = get_term_meta($term_id, self::READING_TIME_FIELD, true);
        echo esc_html(
            $use_reading_time
                ? __('Yes', 'planet4-master-theme-backend')
                : __('No', 'planet4-master-theme-backend')
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Add "Reading time" option to p4-page-type taxonomy.
     *
     * @param WP_Term $term Current taxonomy term object.
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    public function add_taxonomy_form_fields(WP_Term $term): void
    {
        $use_reading_time = $term instanceof WP_Term
            ? get_term_meta($term->term_id, self::READING_TIME_FIELD, true)
            : false;
        $checked = $use_reading_time ? ' checked' : '';

        printf(
            '<tr class="form-field form-required term-name-wrap">
			<th scope="row"><label for="' . esc_html(self::READING_TIME_FIELD) . '">%s</label></th>
			<td><p class="description">
				<input name="' . esc_html(self::READING_TIME_FIELD) . '"
					id="' . esc_html(self::READING_TIME_FIELD) . '"
					type="checkbox" ' . esc_attr($checked) . '
					value="on" />
				%s<br/>
				<small>%s <a href="admin.php?page=planet4_settings_defaults_content">Planet 4 > Defaults content</a>.</small>
			</p></td>
		</tr>',
            esc_html(__('Reading time', 'planet4-master-theme-backend')),
            esc_html(__('Display an estimated reading time on this content.', 'planet4-master-theme-backend')),
            esc_html(__('You can configure the estimated reading speed in', 'planet4-master-theme-backend'))
        );
    }
    // phpcs:enable Generic.Files.LineLength.MaxExceeded

    /**
     * Save a p4-page-type reading time.
     *
     * @param int $term_id The term identifier.
     */
    public function save_taxonomy_meta(int $term_id): void
    {
        switch ($_POST['action'] ?? '') {
            case 'add-tag':
                check_admin_referer('add-tag', '_wpnonce_add-tag');
                break;
            case 'editedtag':
                check_admin_referer('update-tag_' . $_POST['tag_ID']);
                break;
            default:
                return;
        }

        $use_reading_time = ! empty($_POST[ self::READING_TIME_FIELD ]);
        update_term_meta($term_id, self::READING_TIME_FIELD, $use_reading_time);
    }
}
