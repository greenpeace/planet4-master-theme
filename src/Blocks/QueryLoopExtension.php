<?php

declare(strict_types=1);

namespace P4\MasterTheme\Blocks;

/**
 * Handle Posts/Actions List block manual override query with custom `postIn` filter.
 * `include` is not used because it generates some issues with getEntityRecords filters.
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
            return $args;
        };

        add_filter('rest_post_query', $postInFilter, 10, 2);
        add_filter('rest_p4_action_query', $postInFilter, 10, 2);
    }

    public static function registerFrontendQuery(): void
    {
        add_filter(
            'query_loop_block_query_vars',
            function ($query, $block) {
                $blockQuery = $block->context['query'] ?? [];
                if (!empty($blockQuery['postIn'])) {
                    $query['post__in'] = array_map('intval', (array) $blockQuery['postIn']);
                    $query['orderby'] = 'post__in';
                }
                return $query;
            },
            10,
            2
        );
    }
}
