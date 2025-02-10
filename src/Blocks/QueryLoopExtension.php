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
                $post_type = get_post_type($post_id);

                // Purge taxonomies terms and feeds URLs.
                $post_type_taxonomies = get_object_taxonomies($post_type);
                $taxonomy_data = get_taxonomy('action-type');
                // var_dump(json_encode($taxonomy_data));

                // echo $post_type;
                // var_dump(json_encode($post_type_taxonomies));
                // die();

                $blockQuery = $block->context['query'] ?? [];

                $is_new_ia = !empty(planet4_get_option('new_ia'));

                // $query['post_type'] = ['page'];


                if (!$is_new_ia) {
                    $query['post_parent'] = $blockQuery['parent'];
                } else {
                    // array_push($query['post_type'], 'p4_action');
                    // array_push($query['post_type']);
                    // $query['post_parent__in'] = [planet4_get_option('take_action_page')];
                    // $tax_query = array(
                    //     // 'relation' => 'AND',
                    //     array(
                    //         // 'taxonomy' => 'p4_action',
                    //         'taxonomy' => 'action-type',
                    //         // 'field' => 'action_type',
                    //         'field' => 'slug',
                    //         'terms' => 'action-type'
                    //     ),
                    // );

                    $query['tax_query'] = $tax_query;
                    // array_push($query['post_type'], 'p4_action');
                    // $query['post_type'] = ['p4_action'];
                    // $query['post_parent__in'] = [$blockQuery['parent'], planet4_get_option('take_action_page')];
                }

                // if (!empty($blockQuery['postIn'])) {
                //     $query['post__in'] = array_map('intval', (array) $blockQuery['postIn']);
                //     $query['orderby'] = 'post__in';
                // }

                // if (isset($blockQuery['hasPassword'])) {
                //     $query['has_password'] = (bool) $blockQuery['hasPassword'];
                // }



                $query = [
                    // 'post_status'    => 'publish',
                    // 'post_type' => array('page', 'p4_action'),
                    'post_type' => array('p4_action'),
                    'posts_per_page' => 100,
                    // 'post_parent__in' => [planet4_get_option('take_action_page')],

                ];

                $tax_query = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'action-type',
                        'field' => 'slug',
                        'terms' => 'petitions',
                    ),
                );

                $query = [
                    'post_type' => array('page', 'p4_action'),
                    'post_status'    => 'publish',
                    'posts_per_page' => 100,
                    // 'post_parent__in' => [planet4_get_option('take_action_page')],
                    'tax_query' => array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => 'action-type',
                            'field' => 'slug',
                            'terms' => 'petitions',
                        ),
                    ),
                ];


                // $query['tax_query'] = $tax_query;
                // $terms = get_the_terms(1594, 'action-type');
                // $terms = get_the_terms(1605, 'page');
                // var_dump(json_encode($terms));
                // [
                //     {
                //        "term_id":22,
                //        "name":"Petitions",
                //        "slug":"petitions",
                //        "term_group":0,
                //        "term_taxonomy_id":22,
                //        "taxonomy":"action-type",
                //        "description":"",
                //        "parent":0,
                //        "count":6,
                //        "filter":"raw"
                //     }
                // ]

                // var_dump($query);
                return $query;
            },
            10,
            2
        );
    }
}
