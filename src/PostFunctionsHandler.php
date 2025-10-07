<?php

namespace P4\MasterTheme;

use WP_Post;

/**
 * Class PostFunctionsHandler
 */
class PostFunctionsHandler
{
    /**
     * PostFunctionsHandler constructor.
     */
    public function __construct()
    {
        add_action('save_post', [$this, 'save_global_project_id'], 10, 1);
        add_action('save_post', [$this, 'p4_auto_generate_excerpt'], 10, 2);
        add_action('save_post', [$this, 'set_featured_image'], 10, 2);
        add_action('save_post', [$this, 'save_taxonomy_page_type'], 10, 2);
        add_action('post_updated', [$this, 'clean_post_cache'], 10, 3);
    }

    /**
     * Sets as featured image of the post the first image found attached in the post's content (if any).
     *
     * @param int     $post_id The ID of the current Post.
     * @param WP_Post $post The current Post.
     */
    public function set_featured_image(int $post_id, WP_Post $post): void
    {
        $types = Search\Filters\ContentTypes::get_all();
        // Ignore autosave, check user's capabilities and post type.
        if (
            defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
            || !current_user_can('edit_post', $post_id)
            || !in_array($post->post_type, array_keys($types))
        ) {
            return;
        }

        // Check if user has set the featured image manually.
        $user_set_featured_image = get_post_meta($post_id, '_thumbnail_id', true);

        // Apply this behavior only if there is not already a featured image.
        if ($user_set_featured_image) {
            return;
        }

        // Find all matches of <img> html tags within the post's content
        // and get the id of the image from the elements class name.
        preg_match_all('/<img.+wp-image-(\d+).*>/i', $post->post_content, $matches);
        if (!isset($matches[1][0]) || !is_numeric($matches[1][0])) {
            return;
        }

        set_post_thumbnail($post_id, $matches[1][0]);
    }

    /**
     * Auto generate excerpt for post.
     *
     * @param int     $post_id Id of the saved post.
     * @param WP_Post $post Post object.
     */
    public function p4_auto_generate_excerpt(int $post_id, WP_Post $post): void
    {
        if ('' !== $post->post_excerpt || 'post' !== $post->post_type) {
            return;
        }

        // Unhook save_post function so it doesn't loop infinitely.
        remove_action('save_post', [$this, 'p4_auto_generate_excerpt'], 10);

        // Generate excerpt text.
        $post_excerpt = strip_shortcodes($post->post_content);

        preg_match('/<p>(.*?)<\/p>/', $post_excerpt, $match_paragraph);

        $post_excerpt = $match_paragraph[1] ?? $post_excerpt;
        $post_excerpt = apply_filters('the_content', $post_excerpt);
        $post_excerpt = str_replace(']]>', ']]&gt;', $post_excerpt);
        $excerpt_length = apply_filters('excerpt_length', 30);
        $excerpt_more = apply_filters('excerpt_more', '&hellip;');
        $post_excerpt = wp_trim_words($post_excerpt, $excerpt_length, $excerpt_more);

        // Update the post, which calls save_post again.
        wp_update_post(
            [
                'ID' => $post_id,
                'post_excerpt' => $post_excerpt,
            ]
        );

        // re-hook save_post function.
        add_action('save_post', [$this, 'p4_auto_generate_excerpt'], 10, 2);
    }

    /**
     * Look up the ID of the global campaign and save it on the Post/Page.
     *
     * @param int     $post_id The ID of the current Post.
     */
    public function save_global_project_id(int $post_id): void
    {
        // Ignore autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        // Check user's capabilities.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        $p4_campaign_name = get_post_meta($post_id, 'p4_campaign_name', true);
        $old_project_id = get_post_meta($post_id, 'p4_global_project_tracking_id', true);
        $project_id = AnalyticsValues::from_cache_or_api_or_hardcoded()->get_id_for_global_project($p4_campaign_name);
        // phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
        if ('not set' !== $project_id && $old_project_id !== $project_id) {
            update_post_meta($post_id, 'p4_global_project_tracking_id', $project_id);
        }
    }

    /**
     * Clean post cache.
     *
     * @param int     $post_id The ID of the current Post.
     * @param WP_Post $post_after The current Post.
     * @param WP_Post $post_before Whether this is an existing post being updated or not.
     */
    public function clean_post_cache(int $post_id, WP_Post $post_after, WP_Post $post_before): void
    {
        // Ignore autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $this->clean_boxout_posts_cache($post_id, $post_after, $post_before);

        clean_post_cache($post_id);
    }

    /**
     * Flush Take Action Boxout (TAB) posts cache, if the TAB act page status changes.
     *
     * @param int     $post_id The ID of the current Post.
     * @param WP_Post $post_after The current Post.
     * @param WP_Post $post_before Whether this is an existing post being updated or not.
     */
    private function clean_boxout_posts_cache(int $post_id, WP_Post $post_after, WP_Post $post_before): void
    {
        $parent_act_id = (int) planet4_get_option('act_page', -1);
        if ('page' !== $post_after->post_type || $parent_act_id !== $post_after->post_parent) {
            return;
        }

        // Flush cache only when a page status changes from publish to any non-public status & vice versa.
        if (
            ($post_before->post_status === $post_after->post_status) ||
            ('publish' !== $post_before->post_status && 'publish' !== $post_after->post_status)
        ) {
            return;
        }

        global $wpdb, $nginx_purger;

        // Search for those posts, who use TAB($post_id) from "Take Action Page Selector" dropdown.
        // phpcs:disable
        $sql          = 'SELECT post_id FROM %1$s WHERE meta_key = \'p4_take_action_page\' AND meta_value = %2$d';
        $prepared_sql = $wpdb->prepare($sql, $wpdb->postmeta, $post_id);
        $boxout_posts = $wpdb->get_col($prepared_sql);
        // phpcs:enable

        // Search for those posts, who use TAB($post_id) as a block inside block editor.
        $take_action_boxout_block = '%<!-- wp:planet4-blocks/take-action-boxout {"take_action_page":'
            . $post_id . '} /-->%';
        // phpcs:disable
        $sql          = 'SELECT ID FROM %1$s WHERE post_type = \'post\' AND post_status = \'publish\' AND post_content LIKE \'%2$s\'';
        $prepared_sql = $wpdb->prepare($sql, $wpdb->posts, $take_action_boxout_block);
        $result       = $wpdb->get_col($prepared_sql);
        // phpcs:enable

        $boxout_posts = array_merge($boxout_posts, $result);

        // Flush TAB posts cache.
        $boxout_posts = array_unique($boxout_posts);
        foreach ($boxout_posts as $tab_post_id) {
            clean_post_cache($tab_post_id);
            $tab_post_url = get_permalink($tab_post_id);
            $nginx_purger->purge_url(user_trailingslashit($tab_post_url));
        }
    }

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
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Allow p4-page-type to be set from edit post and quick edit pages.
        // Make sure there's input.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if (
            isset($_POST[CustomTaxonomy::TAXONOMY]) && 'post' === $post->post_type &&
            filter_var(wp_unslash($_POST[CustomTaxonomy::TAXONOMY]), FILTER_VALIDATE_INT)
        ) {
            $selected = get_term_by('id', intval($_POST[CustomTaxonomy::TAXONOMY]), CustomTaxonomy::TAXONOMY);
            // phpcs:enable
            if (false !== $selected && !is_wp_error($selected)) {
                // Save post type.
                wp_set_post_terms($post_id, [$selected->term_id], CustomTaxonomy::TAXONOMY);
            }
        }

        // Check if post type is POST.
        // Check if post has a p4 page type term assigned to it and if none if assigned,
        // assign the default p4 page type term.
        if ('post' !== $post->post_type) {
            return;
        }

        // Check if post has an assigned term to it.
        $terms = wp_get_object_terms($post_id, CustomTaxonomy::TAXONOMY);
        if (is_wp_error($terms)) {
            return;
        }

        $planet4_default_post_type = CustomTaxonomy::get_planet4_default_post_type();

        // Assign default p4-pagetype, if no term is assigned to post.
        if (empty($terms)) {
            if ($planet4_default_post_type instanceof \WP_Term) {
                wp_set_post_terms($post_id, [$planet4_default_post_type->term_id], CustomTaxonomy::TAXONOMY);
            }
            // Assign the first term, if more than one terms are assigned.
        } elseif (count($terms) > 1 && $terms[0] instanceof \WP_Term) {
            wp_set_post_terms($post_id, [$terms[0]->term_id], CustomTaxonomy::TAXONOMY);
        }
    }
}
