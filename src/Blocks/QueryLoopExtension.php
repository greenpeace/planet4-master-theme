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

    /**
     * Register all necessary filters for both REST API and frontend query handling.
     */
    public static function registerHooks(): void
    {
        add_filter('rest_post_query', [self::class, 'registerEditorQuery'], 10, 2);
        add_filter('rest_page_query', [self::class, 'registerEditorQuery'], 10, 2);
        add_filter('rest_p4_action_query', [self::class, 'registerEditorQuery'], 10, 2);
        add_filter('query_loop_block_query_vars', [self::class, 'registerFrontendQuery'], 10, 2);
    }

    /**
     * Modifies REST API query args for editor context.
     *
     * @param array $args The original query arguments.
     * @param \WP_REST_Request $request The incoming REST request.
     *
     * @return array Modified query arguments.
     */
    public static function registerEditorQuery(array $args, \WP_REST_Request $request): array
    {
        $postIn = $request->get_param('postIn');
        $block_name = $request->get_param('block_name');

        if ($block_name === self::ACTIONS_LIST_BLOCK) {
            return self::buildActionListQuery($args, $request->get_params());
        }
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
     * Modifies query args for frontend query loop blocks.
     *
     * @param array $query The original WP_Query args.
     * @param WP_Block $block The block instance, containing context.
     *
     * @return array Modified query arguments.
     */
    public static function registerFrontendQuery(array $query, WP_Block $block): array
    {
        $blockQuery = $block->context['query'] ?? [];

        if ($blockQuery['block_name'] === self::ACTIONS_LIST_BLOCK) {
            return self::buildActionListQuery($query, $block->context['query'],);
        }
        if (!empty($blockQuery['postIn'])) {
            $query['post__in'] = array_map('intval', (array) $blockQuery['postIn']);
            $query['orderby'] = 'post__in';
            $query['ignore_sticky_posts'] = true;
        }
        if (isset($blockQuery['hasPassword'])) {
            $query['has_password'] = (bool) $blockQuery['hasPassword'];
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
    private static function buildActionListQuery(array $query, array $params = []): array
    {
        $is_new_ia = !empty(planet4_get_option('new_ia'));
        $query['post_status'] = 'publish';

        if (!$is_new_ia) {
            $query = self::buildOldIaActionListQuery($query, $params);
        } else {
            $query = self::buildNewIaActionListQuery($query, $params);
        }
        return $query;
    }

    /**
     * Builds the query for old IA configuration using the act_page as parent.
     *
     * @param array $query The current query args.
     * @param array $params Parameters that may contain 'postIn'.
     *
     * @return array Modified query arguments.
     */
    private static function buildOldIaActionListQuery(array $query, array $params = []): array
    {
        $query['post_type'] = ['page'];
        $query['post_parent'] = !empty(planet4_get_option('act_page'))
            ? planet4_get_option('act_page')
            : -1;

        if (!empty($params['postIn'])) {
            $query['post__in'] = array_map('intval', (array) $params['postIn']);
        }
        return $query;
    }

    /**
     * Builds the query for new IA configuration using both action and page types.
     *
     * @param array $query The current query args.
     * @param array $params Parameters that may include 'postIn' and 'hasPassword'.
     *
     * @return array Modified query arguments.
     */
    private static function buildNewIaActionListQuery(array $query, array $params = []): array
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

        if (!empty($params['postIn'])) {
            $query['post__in'] = array_map('intval', (array) $params['postIn']);
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
        return $query;
    }
}
