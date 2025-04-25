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
    public static function registerHooks(): void
    {
        self::registerEditorQuery();
        self::registerFrontendQuery();
    }

    public static function registerEditorQuery(): void
    {

        // This is used to handle Actions List block with multiple Post Types
        register_post_type('p4_multipost', [
            'label' => 'P4 Query Loop Interceptor',
            'description' => 'This post type is used to filter filter queries since arrays are not supported natively.',
            'public' => false,
            'show_ui' => false,
            'show_in_rest' => true,
            'capability_type' => 'post',
            'supports' => [],
            'query_var' => false,
        ]);

        add_filter('rest_post_query', [self::class, 'postInFilter'], 10, 2);
        add_filter('rest_page_query', [self::class, 'postInFilter'], 10, 2);
        add_filter('rest_p4_action_query', [self::class, 'postInFilter'], 10, 2);
        add_filter('rest_p4_multipost_query', function ($query, $request) {
            return self::sanitizePostTypes($query, $request->get_params());
        }, 10, 2);
    }

    public static function registerFrontendQuery(): void
    {
        add_filter(
            'query_loop_block_query_vars',
            function ($query, $block) {
                if ($block->context['query']['postType'] === 'p4_multipost') {
                    return self::sanitizePostTypes(
                        $query,
                        $block->context['query'],
                    );
                }

                return $query;
            },
            10,
            2
        );
    }

    /**
     * Filter the query to include the postIn and hasPassword parameters
     *
     * @param array $args The query
     * @param array $request The request
     *
     * @return array The new parsed query
     */
    public static function postInFilter(array $args, array $request): array
    {
        $postIn = $request->get_param('postIn');
        if (!empty($postIn)) {
            $args['post__in'] = array_map('intval', (array) $postIn);
            $args['orderby'] = 'post__in';
        }
        if ($request->has_param('hasPassword')) {
            $hasPassword = $request->get_param('hasPassword');
            $args['has_password'] = $hasPassword !== false && $hasPassword !== 'false';
        }
        return $args;
    }

    /**
     * Sanitize the query received by the editor and frontend
     *
     * @param array $query The query
     * @param array $params The query or request params
     *
     * @return array The new parsed query
     */
    public static function sanitizePostTypes(array $query, array $params = []): array
    {
        $is_new_ia = !empty(planet4_get_option('new_ia'));
        $query['post_status'] = 'publish';

        if (!$is_new_ia) {
            $query['post_type'] = ['page'];
            $query['post_parent'] = planet4_get_option('act_page');

            if (!empty($params['postIn'])) {
                $query['post__in'] = array_map('intval', (array) $params['postIn']);
            }
        } else {
            $query['post_type'] = ['page', 'p4_action'];

            global $wpdb;
            $post_ids = $wpdb->get_col($wpdb->prepare(
                "
                (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)
                UNION ALL
                (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %d)",
                'p4_action',
                'page',
                planet4_get_option('take_action_page')
            ));

            if (!empty($post_ids)) {
                $query['post__in'] = $post_ids;
            } else {
                $query['post__in'] = [0];
            }

            if (!empty($params['hasPassword'])) {
                $query['has_password'] = $params['hasPassword'] !== false && $params['hasPassword'] !== 'false';
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
