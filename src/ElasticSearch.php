<?php

namespace P4\MasterTheme;

use UnexpectedValueException;
use ElasticPress\ElasticSearch as ElasticPressCient;

/**
 * Class P4\MasterTheme\ElasticSearch
 */
class ElasticSearch extends Search
{
    /**
     * Applies user selected filters to the search if there are any and gets the filtered posts.
     *
     * @param array $args Query args.
     *
     * @throws UnexpectedValueException When filter type is not recognized.
     */
    public function set_filters_args(array &$args): void
    {
        parent::set_filters_args($args);

        if (!$this->filters) {
            return;
        }

        foreach ($this->filters as $type => $filter_type) {
            foreach ($filter_type as $filter) {
                switch ($type) {
                    case 'cat':
                    case 'tag':
                    case 'ptype':
                    case 'atype':
                        break;
                    case 'ctype':
                        switch ($filter['id']) {
                            case 0:
                            case 1:
                            case 2:
                                break;
                            case 3:
                            case 4:
                                add_filter(
                                    'ep_formatted_args',
                                    function ($formatted_args) use ($args) {
                                        // Make sure it is a Post.
                                        if (! empty($args['post_type'])) {
                                            $formatted_args['post_filter']['bool']['must'][] = [
                                                'terms' => [
                                                    'post_type' => array_values((array) $args['post_type']),
                                                ],
                                            ];
                                        }
                                        return $formatted_args;
                                    },
                                    10,
                                    1
                                );
                                break;
                            case 5:
                            case 6:
                                break;
                            default:
                                throw new UnexpectedValueException('Unexpected content type!');
                        }
                        break;
                    default:
                        throw new UnexpectedValueException('Unexpected filter!');
                }
            }
        }
    }

    /**
     * Applies user selected filters to the search if there are any and gets the filtered posts.
     *
     * @param array $args The array with the arguments that will be passed to WP_Query.
     */
    public function set_engines_args(array &$args): void
    {
        $args['ep_integrate'] = true;
        simple_value_filter('epwr_scale', planet4_get_option('epwr_scale', '28d'));
        simple_value_filter('epwr_decay', planet4_get_option('epwr_decay', 0.5));
        simple_value_filter('epwr_offset', planet4_get_option('epwr_offset', '365d'));

        add_filter('ep_formatted_args', [ $this, 'ensure_function_score_exists' ], 18, 1);
        add_filter('ep_formatted_args', [ $this, 'set_full_text_search' ], 19, 1);
        add_filter('ep_formatted_args', [ $this, 'set_results_weight' ], 20, 1);

        add_filter('ep_formatted_args', [ $this, 'add_mime_type_filter' ], 21, 1);

        if (wp_doing_ajax()) {
            return;
        }

        add_filter('ep_formatted_args', [ $this, 'add_aggregations' ], 999, 1);
    }

    /**
     * Ensure function_score entry exists in the query arguments.
     *
     * @param array $formatted_args  The formatted arguments.
     *
     * @return array Formatted arguments with function_score.
     */
    public function ensure_function_score_exists(array $formatted_args): array
    {
        if (! isset($formatted_args['query']['function_score'])) {
            $existing_query = $formatted_args['query'];
            unset($formatted_args['query']);
            $formatted_args['query']['function_score']['query'] = $existing_query;
        }

        return $formatted_args;
    }

    /**
     * Apply full-text search.
     *
     * @param mixed $formatted_args Assoc array with the args that ES expects.
     *
     * @return mixed
     */
    public function set_full_text_search($formatted_args)
    {
        if (isset($formatted_args['query']['function_score']['query']['bool'])) {
            // Create/change the bool query from should to must.
            $formatted_args['query']['function_score']['query']['bool']['must'] = $formatted_args['query']['function_score']['query']['bool']['should'];
            // Add the operator AND to the new bool query.
            $formatted_args['query']['function_score']['query']['bool']['must'][0]['multi_match']['operator'] = 'AND';
            // Erase the old should query.
            unset($formatted_args['query']['function_score']['query']['bool']['should']);
            // Erase the phrase matching (to make sure we get results that include both 'courageous' AND 'act' instead of only those with 'courageous act'.
            unset($formatted_args['query']['function_score']['query']['bool']['must'][0]['multi_match']['type']);
        }

        return $formatted_args;
    }

    /**
     * Apply custom weight to search results.
     *
     * @param mixed $formatted_args Assoc array with the args that ES expects.
     *
     * @return mixed
     */
    public function set_results_weight($formatted_args)
    {
        if (! isset($formatted_args['query']['function_score']['functions'])) {
            $formatted_args['query']['function_score']['functions'] = [];
        }

        /**
         * Use any combination of filters here, any matched filter will adjust
         * the weighted results according to the scoring settings set below.
         */
        array_push(
            $formatted_args['query']['function_score']['functions'],
            [
                'filter' => [
                    'match' => [
                        'post_type' => 'page',
                    ],
                ],
                'weight' => self::DEFAULT_PAGE_WEIGHT,
            ],
        );

        // Specify how the computed scores are combined.
        $formatted_args['query']['function_score']['score_mode'] = 'sum';

        return $formatted_args;
    }

    /**
     * Add some ES aggregations so we can get terms numbers for the complete result set.
     *
     * We need to add the `with_post_filter` to this otherwise it will show numbers for the result set without that
     * filter. Actually we should not be using `with_post_filter` in the first place, as the only point of that is
     * that it allows you to perform aggregations on the unfiltered set, which we don't want.
     * However due to the complexity of the current setup (we use ElasticPress which creates the ES query based on
     * the WP query, and we apply modifications to both queries) this is not easy to change.
     *
     * @param  array $formatted_args The query that is going to ES.
     * @return array The same query, but with added aggregations.
     */
    public function add_aggregations(array $formatted_args): array
    {
        $formatted_args['aggs'] = [
            'with_post_filter' => [
                'filter' => $formatted_args['post_filter'],
                'aggs' => [
                    'post_type' => [
                        'terms' => [
                            'field' => 'post_type.raw',
                        ],
                    ],
                    'post_parent' => [
                        'terms' => [
                            'field' => 'post_parent',
                        ],
                    ],
                    'categories' => [
                        'terms' => [
                            'field' => 'terms.category.term_id',
                        ],
                    ],
                    'tags' => [
                        'terms' => [
                            'field' => 'terms.post_tag.term_id',
                        ],
                    ],
                    'p4-page-type' => [
                        'terms' => [
                            'field' => 'terms.p4-page-type.term_id',
                        ],
                    ],
                    ActionPage::TAXONOMY => [
                        'terms' => [
                            'field' => 'terms.' . ActionPage::TAXONOMY . '.term_id',
                        ],
                    ],
                ],
            ],
        ];

        return $formatted_args;
    }

    /**
     * Remove items that are an attachment and have a different mime type.
     *
     * @param array $formatted_args The args that are going to ES.
     * @return array Same args with added filter.
     */
    public function add_mime_type_filter(array $formatted_args): array
    {
        $formatted_args['post_filter']['bool']['must'][] = [
            'bool' => [
                'should' => [
                    [
                        'bool' => [
                            'must_not' => [
                                'terms' => [
                                    'post_type.raw' => [ 'attachment' ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'terms' => [
                            'post_mime_type' => self::DOCUMENT_TYPES,
                        ],
                    ],
                ],
            ],
        ];

        return $formatted_args;
    }

    /**
     * Debug function to validate a query through elastic server.
     * This will return a valid/invalid indication,
     * and the Lucene query generated from the arguments given.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-validate.html
     *
     * @param array $query Query arguments.
     *
     * @return array Server response.
     * @throws \Exception On request error.
     */
    public static function validate_query(array $query): array
    {
        $client = ElasticPressCient::factory();
        $request = $client->remote_request(
            '_validate/query?rewrite=true',
            [
                'method' => 'POST',
                'body' => wp_json_encode([ 'query' => $query ]),
            ],
        );

        if (is_wp_error($request)) {
            throw new \Exception('Request error');
        }

        return json_decode(wp_remote_retrieve_body($request), true);
    }
}
