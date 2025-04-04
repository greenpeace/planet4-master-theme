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
        $postInFilter = function ($args, $request) {
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

                // This applies only to Actions List block
                if ($blockQuery['isCustom']) {
                    $is_new_ia = !empty(planet4_get_option('new_ia'));

                    if (!$is_new_ia) {
                        $query['post_type'] = ['page'];
                        $query['post_parent'] = planet4_get_option('act_page');
                    } else {
                        $query['post_type'] = ['page', 'p4_action'];
                        $query['post_status'] = 'publish';
                        $query['posts_per_page'] = -1;

                        global $wpdb;
                        $post_ids = $wpdb->get_col($wpdb->prepare(
                            "
                            (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)
                            UNION ALL
                            (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %d)",
                            'p4_action2',
                            'page2',
                            planet4_get_option('take_action_page')
                        ));

                        if (!empty($post_ids)) {
                            $query['post__in'] = $post_ids;
                        } else {
                            $query['post__in'] = [0];
                        }

                        if (is_array($blockQuery['orderBy'])) {
                            $query['orderby'] = array_combine(
                                $blockQuery['orderBy'],
                                $blockQuery['order'] ?? array_fill(0, count($blockQuery['orderBy']), 'ASC')
                            );
                        } else {
                            $query['orderby'] = [
                                'menu_order' => 'ASC',
                                'post_date' => 'DESC',
                                'post_title' => 'ASC',
                            ];
                        }
                    }
                }
                if (!empty($blockQuery['postIn'])) {
                    $query['post__in'] = array_map('intval', (array) $blockQuery['postIn']);
                    $query['orderby'] = 'post__in';
                }
                if (isset($blockQuery['hasPassword'])) {
                    $query['has_password'] = (bool) $blockQuery['hasPassword'];
                }
                return $query;
            },
            10,
            2
        );
    }
}
