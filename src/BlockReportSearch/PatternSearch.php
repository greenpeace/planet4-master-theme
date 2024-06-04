<?php

/**
 * Pattern search
 *
 * @package P4BKS\Search
 */

namespace P4\MasterTheme\BlockReportSearch;

use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters as BlockSearchParameters;
use P4\MasterTheme\BlockReportSearch\Block\Sql\SqlQuery as BlockSqlQuery;
use P4\MasterTheme\BlockReportSearch\Pattern\PatternData;
use P4\MasterTheme\BlockReportSearch\Pattern\PatternUsage;
use P4\MasterTheme\BlockReportSearch\Pattern\Query\Parameters;
use P4\MasterTheme\SqlParameters;

/**
 * Search for posts containing specific patterns
 */
class PatternSearch
{
    public const DEFAULT_POST_TYPE = [ 'post', 'page' ];

    public const DEFAULT_POST_STATUS = [ 'publish', 'private', 'draft', 'pending', 'future' ];

    /**
     * @param Parameters $params Query parameters.
     * @param array      $opts   Search Options.
     * @return int[] list of posts IDs.
     */
    public function get_posts(Parameters $params, array $opts = []): array
    {
        $opts = array_merge(
            [
                'use_struct' => true,
                'use_class' => true,
                'use_templates' => true,
            ],
            $opts
        );

        $patterns = array_map(
            fn ($pattern) => PatternData::from_name($pattern),
            $params->name()
        );

        return array_unique(
            array_filter(
                array_merge(
                    $opts['use_class'] ? $this->query_by_pattern_classname($params, ...$patterns) : [],
                    $opts['use_struct'] ? $this->query_by_pattern_blocks($params, ...$patterns) : [],
                    $opts['use_templates'] ? $this->query_by_pattern_template($params, ...$patterns) : []
                )
            )
        );
    }

    /**
     * Search templates by namespace to reduce query count.
     *
     * @param Parameters  $params          Search parameters.
     * @param PatternData ...$pattern_data Pattern data.
     * @return int[] List of posts IDs.
     */
    private function query_by_pattern_template(
        Parameters $params,
        PatternData ...$pattern_data
    ): array {
        // Query posts with all blocks of tree.
        $templates = PatternUsage::patterns_templates_lookup_table();
        $block_query = new BlockSqlQuery();

        $pattern_names = array_map(fn($p) => $p->name, $pattern_data);
        $template_names = array_map(fn($n) => $templates[ $n ], $pattern_names);
        $template_ns = array_filter(array_unique(array_map(fn($n) => explode('/', $n)[0] ?? null, $template_names)));

        $post_ids = [];
        foreach ($template_ns as $ns) {
            $block_params = BlockSearchParameters::from_array(
                [
                    'namespace' => $ns,
                    'post_status' => $params->post_status() ?? self::DEFAULT_POST_STATUS,
                    'post_type' => $params->post_type() ?? self::DEFAULT_POST_TYPE,
                ]
            );

            $post_ids = array_merge($post_ids, $block_query->get_posts($block_params));
        }

        return array_unique($post_ids);
    }

    /**
     * @param Parameters  $params          Search parameters.
     * @param PatternData ...$pattern_data Pattern data.
     * @return int[] List of posts IDs.
     */
    private function query_by_pattern_blocks(
        Parameters $params,
        PatternData ...$pattern_data
    ): array {
        // Query posts with all blocks of tree.
        $block_query = new BlockSqlQuery();

        $post_ids = [];
        foreach ($pattern_data as $pattern) {
            $block_params = array_map(
                fn ($block_name) => BlockSearchParameters::from_array(
                    [
                        'name' => $block_name,
                        'post_status' => $params->post_status() ?? self::DEFAULT_POST_STATUS,
                        'post_type' => $params->post_type() ?? self::DEFAULT_POST_TYPE,
                    ]
                ),
                $pattern->block_list
            );

            $post_ids = array_merge($post_ids, $block_query->get_posts(...$block_params));
        }

        return array_unique($post_ids);
    }

    /**
     * Query posts by pattern classname.
     *
     * @param Parameters  $params          Search parameters.
     * @param PatternData ...$pattern_data Pattern data.
     * @return int[] List of posts IDs.
     */
    private function query_by_pattern_classname(
        Parameters $params,
        PatternData ...$pattern_data
    ): array {
        $classes = array_map(
            fn ($p) => $p->classname,
            $pattern_data
        );
        if (empty($classes)) {
            return [];
        }

        global $wpdb;

        $like = array_map(fn ($c) => "post_content LIKE '%%$c%%'", $classes);
        $like = implode(' OR ', $like);

        $sql_params = new SqlParameters();
        $query = 'SELECT ID
			FROM ' . $sql_params->identifier($wpdb->posts) . '
			WHERE post_status IN ' . $sql_params->string_list(
                $params->post_status() ?? self::DEFAULT_POST_STATUS
            ) . '
			AND post_type IN ' . $sql_params->string_list(
                $params->post_type() ?? self::DEFAULT_POST_TYPE
            ) . '
			AND ( ' . $like . ' )';

        $results = $wpdb->get_results(
			$wpdb->prepare( $query, $sql_params->get_values() ) // phpcs:ignore
        );

        if (! is_array($results)) {
            return [];
        }

        return array_map(
            fn ($r) => (int) $r->ID,
            $results
        );
    }
}
