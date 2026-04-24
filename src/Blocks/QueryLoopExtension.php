<?php

declare(strict_types=1);

namespace P4\MasterTheme\Blocks;

use WP_Block;

/**
 * Enhances the query loop block with custom filtering logic for specific blocks.
 * Supports manual inclusion of posts (`postIn`) and filtering password-protected content.
 * Also handles IA-mode-based query construction for action pages.
 */
class QueryLoopExtension
{
    public const ACTIONS_LIST_BLOCK = 'planet4-blocks/actions-list';
    public const POSTS_LIST_BLOCK = 'planet4-blocks/posts-list';

    /**
     * Tracks whether we are currently rendering inside a Posts List block.
     * Inner blocks (core/post-title etc.) do not carry query context, so a
     * static flag set before the outer core/query renders is the reliable way
     * to know we are inside a Posts List.
     */
    private static bool $inPostsList = false;

    /**
     * Register all necessary filters for both REST API and frontend query handling.
     */
    public static function register_hooks(): void
    {
        add_filter('rest_post_query', [self::class, 'register_editor_query'], 10, 2);
        add_filter('rest_page_query', [self::class, 'register_editor_query'], 10, 2);
        add_filter('rest_p4_action_query', [self::class, 'register_editor_query'], 10, 2);
        add_filter('query_loop_block_query_vars', [self::class, 'register_frontend_query'], 10, 2);
        add_filter('render_block_data', [self::class, 'track_posts_list_context'], 10, 1);
        add_filter('render_block', [self::class, 'add_data_attributes'], 10, 2);
    }

    /**
     * Modifies REST API query args for editor context.
     *
     * @param array $args The original query arguments.
     * @param \WP_REST_Request $request The incoming REST request.
     *
     * @return array Modified query arguments.
     */
    public static function register_editor_query(array $args, \WP_REST_Request $request): array
    {
        $params = [
            'postIn' => $request->get_param('postIn'),
            'block_name' => $request->get_param('block_name'),
            'exclude' => $request->get_param('exclude'),
        ];
        return self::apply_common_query_modifiers($args, $params);
    }

    /**
     * Modifies query args for frontend query loop blocks.
     *
     * @param array $query The original WP_Query args.
     * @param WP_Block $block The block instance, containing context.
     *
     * @return array Modified query arguments.
     */
    public static function register_frontend_query(array $query, WP_Block $block): array
    {
        $params = $block->context['query'] ?? [];
        return self::apply_common_query_modifiers($query, $params);
    }

    /**
     * Applies common filtering logic used by both frontend and editor queries.
     *
     * @param array $query The WP_Query arguments to modify.
     * @param array $params Parameters extracted from either block context or REST request.
     *
     * @return array Modified query arguments.
     */
    private static function apply_common_query_modifiers(array $query, array $params): array
    {
        // Ensure only published items without password are queried
        $query['post_status'] = 'publish';
        $query['has_password'] = false;

        // If the type of block can be identified:
        if (!empty($params['block_name'])) {
            if ($params['block_name'] === self::ACTIONS_LIST_BLOCK) {
                $query = self::build_action_list_query($query);
            }

            if ($params['block_name'] === self::POSTS_LIST_BLOCK) {
                $query['ignore_sticky_posts'] = true;
                $query['orderby'] = [
                    'post_date' => 'DESC',
                ];
            }
        }

        // If the Manual Override is used:
        if (!empty($params['postIn'])) {
            $query['post__in'] = array_map('intval', (array) $params['postIn']);
            $query['ignore_sticky_posts'] = true;
            $query['orderby'] = 'post__in';
        }

        // If the Manual Override is not used, remove the current post from the query:
        if (
            empty($params['postIn']) && isset($query['post__in']) &&
            !empty($query['post__in']) && !empty($params['exclude'])
        ) {
            $exclude = array_map('intval', (array) $params['exclude']);
            $query['post__in'] = array_values(array_diff($query['post__in'], $exclude));
        }

        return $query;
    }

    /**
     * Build a filtered post query based on IA mode and request parameters.
     *
     * @param array $query The base WP_Query arguments.
     * @param array $params Additional parameters, typically from a block context or REST request.
     *
     * @return array Modified query arguments.
     */
    private static function build_action_list_query(array $query): array
    {
        $is_new_ia = !empty(planet4_get_option('new_ia'));

        if (!$is_new_ia) {
            $query = self::build_old_ia_action_list_query($query);
        } else {
            $query = self::build_new_ia_action_list_query($query);
        }
        return $query;
    }

    /**
     * Builds the query for old IA configuration using the act_page as parent.
     *
     * @param array $query The current query args.
     *
     * @return array Modified query arguments.
     */
    private static function build_old_ia_action_list_query(array $query): array
    {
        $query['post_type'] = ['page'];
        $query['post_parent'] = !empty(planet4_get_option('act_page'))
            ? planet4_get_option('act_page')
            : -1;

        return $query;
    }

    /**
     * Builds the query for new IA configuration using both action and page types.
     *
     * @param array $query The current query args.
     *
     * @return array Modified query arguments.
     */
    private static function build_new_ia_action_list_query(array $query): array
    {
        global $wpdb;

        $query['post_type'] = ['page', 'p4_action'];

        $post_parent = !empty(planet4_get_option('take_action_page'))
            ? planet4_get_option('take_action_page')
            : -1;

        $post_ids = [];
        $post_ids = $wpdb->get_col($wpdb->prepare(
            "
            (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)
            UNION ALL
            (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %d)",
            'p4_action',
            'page',
            $post_parent,
        ));

        if (!empty($post_ids)) {
            $query['post__in'] = $post_ids;
        } else {
            $query['post__in'] = [0];
        }

        $query['orderby'] = [
            'menu_order' => 'ASC',
            'post_date' => 'DESC',
            'post_title' => 'ASC',
            'post__in' => 'ASC',
        ];

        return $query;
    }

    /**
     * Sets the static flag when a Posts List core/query block is about to render,
     * so that inner block renders (which lack query context) can detect it.
     *
     * @param array $parsed_block The parsed block data.
     *
     * @return array Unmodified parsed block data.
     */
    public static function track_posts_list_context(array $parsed_block): array
    {
        if (
            ($parsed_block['blockName'] ?? '') === 'core/query'
            && ($parsed_block['attrs']['namespace'] ?? '') === self::POSTS_LIST_BLOCK
        ) {
            self::$inPostsList = true;
        }

        return $parsed_block;
    }

    /**
     * Injects GA/Mixpanel tracking data attributes into Posts List block inner block links.
     *
     * @param string $content      The rendered block output.
     * @param array  $parsed_block The parsed block data.
     *
     * @return string Modified block output.
     */
    public static function add_data_attributes(string $content, array $parsed_block): string
    {
        $block_name = $parsed_block['blockName'] ?? '';
        $attrs = $parsed_block['attrs'] ?? [];

        // Clear the flag once the outer core/query finishes rendering.
        if (
            $block_name === 'core/query'
            && ($attrs['namespace'] ?? '') === self::POSTS_LIST_BLOCK
        ) {
            self::$inPostsList = false;
            return $content;
        }

        if (!self::$inPostsList) {
            return $content;
        }

        $ga_action = match (true) {
            $block_name === 'core/post-title' => 'Title',
            $block_name === 'core/post-featured-image' => 'Image',
            $block_name === 'core/post-author-name' => 'Author',
            $block_name === 'core/post-terms' && ($attrs['term'] ?? '') === 'post_tag' => 'Navigation Tag',
            $block_name === 'p4/taxonomy-breadcrumb' => 'Post Type Tag',
            $block_name === 'core/navigation-link'
                && str_contains($attrs['className'] ?? '', 'see-all-link') => 'See More Link',
            default => null,
        };

        if (null === $ga_action) {
            return $content;
        }

        $data_attrs = sprintf(
            'data-ga-category="Posts List" data-ga-action="%s" data-ga-label="n/a"',
            esc_attr($ga_action)
        );

        if ($block_name === 'core/post-featured-image') {
            return (string) preg_replace('/<img\b/', '<img ' . $data_attrs, $content);
        }

        if ($block_name === 'core/navigation-link') {
            return (string) preg_replace('/<span\b/', '<span ' . $data_attrs, $content);
        }

        return (string) preg_replace('/<a\b/', '<a ' . $data_attrs, $content);
    }
}
