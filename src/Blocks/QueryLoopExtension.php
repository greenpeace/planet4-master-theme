<?php

declare(strict_types=1);

namespace P4\MasterTheme\Blocks;

/**
 * Handle Posts/Actions List block manual override query with custom `postIn` filter.
 * `include` is not used because it generates some issues with getEntityRecords filters.
 * Handle filtering password-potected posts.
 */
class QueryLoopExtension
{
    public const ACTIONS_LIST_BLOCK = 'planet4-blocks/actions-list';
    public const POSTS_LIST_BLOCK = 'planet4-blocks/posts-list';

    public static function registerHooks(): void
    {
        self::registerEditorQuery();
        self::registerFrontendQuery();
    }

    public static function registerEditorQuery(): void
    {
        $postInFilter = function ($args, $request) {
            $postIn = $request->get_param('postIn');
            $block_name = $request->get_param('block_name');

            if ($block_name === self::ACTIONS_LIST_BLOCK) {
                return self::buildActionListQuery($args, $request->get_params());
            }
            if ($block_name === self::POSTS_LIST_BLOCK) {
                $args['post_status'] = 'publish'; // Ensure only published posts are queried
                $args['has_password'] = false; // Exclude password-protected posts
            }
            if (!empty($postIn)) {
                $args['post__in'] = array_map('intval', (array) $postIn);
                $args['orderby'] = 'post__in';
            }
            return $args;
        };

        add_filter('rest_post_query', $postInFilter, 10, 2);
        add_filter('rest_page_query', $postInFilter, 10, 2);
        add_filter('rest_p4_action_query', $postInFilter, 10, 2);
    }

    public static function registerFrontendQuery(): void
    {
        add_filter(
            'query_loop_block_query_vars',
            function ($query, $block) {
                $blockQuery = $block->context['query'] ?? [];

                if ($blockQuery['block_name'] === self::ACTIONS_LIST_BLOCK) {
                    return self::buildActionListQuery($query, $block->context['query'],);
                }
                if ($blockQuery['block_name'] === self::POSTS_LIST_BLOCK) {
                    $query['post_status'] = 'publish'; // Ensure only published posts are queried
                    $query['has_password'] = false; // Exclude password-protected posts
                }
                if (!empty($blockQuery['postIn'])) {
                    $query['post__in'] = array_map('intval', (array) $blockQuery['postIn']);
                    $query['orderby'] = 'post__in';
                    $query['ignore_sticky_posts'] = true;
                }
                return $query;
            },
            10,
            2
        );
    }

     /**
     * Build a filtered post query based on IA mode and request parameters.
     *
     * @param array $query  The base WP_Query arguments.
     * @param array $params Additional parameters, typically from a block context or REST request.
     *
     * @return array Modified query arguments.
     */
    private static function buildActionListQuery(array $query, array $params = []): array
    {
        $is_new_ia = !empty(planet4_get_option('new_ia'));
        $query['post_status'] = 'publish'; // Ensure only published posts are queried
        $query['has_password'] = false; // Exclude password-protected posts

        if (!$is_new_ia) {
            $query['post_type'] = ['page'];
            $query['post_parent'] = !empty(planet4_get_option('act_page'))
                ? planet4_get_option('act_page')
                : -1;

            if (!empty($params['postIn'])) {
                $query['post__in'] = array_map('intval', (array) $params['postIn']);
            }
        } else {
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

            if (!empty($params['postIn'])) {
                $query['post__in'] = array_map('intval', (array) $params['postIn']);
            }

            $query['orderby'] = [
                'menu_order' => 'ASC',
                'post_date' => 'DESC',
                'post_title' => 'ASC',
                'post__in' => 'ASC',
            ];
        }
        return $query;
    }
}
