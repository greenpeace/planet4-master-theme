<?php

/**
 * Table displaying blocks usage
 *
 * @package P4BKS\Search
 */

namespace P4\MasterTheme\BlockReportSearch\Pattern;

use WP_Block_Patterns_Registry;
use P4\MasterTheme\Patterns\BlankPage;
use P4\MasterTheme\BlockReportSearch\Pattern\Query\Parameters;

/**
 * Pattern usage API
 */
class PatternUsageApi
{
    public const DEFAULT_POST_STATUS = [ 'publish' ];

    /**
     * @var PatternUsage
     */
    private $usage;

    /**
     * @var Parameters
     */
    private $params;

    /**
     * @var array[] Blocks.
     */
    private $items;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->usage = new PatternUsage();
        $this->params = ( new Parameters() )
            ->with_name($this->pattern_names())
            ->with_post_type($this->allowed_post_types())
            ->with_post_status(self::DEFAULT_POST_STATUS);
    }

    /**
     * Count patterns
     */
    public function get_count(): array
    {
        if (null === $this->items) {
            $this->fetch_items();
        }

        $names = $this->pattern_names();
        $patterns = array_fill_keys($names, [ 'total' => 0 ]);
        ksort($patterns);

        foreach ($this->items as $item) {
            $patterns[ $item['pattern_name'] ]['total']++;
        }

        return $patterns;
    }

    /**
     * Fetch parsed blocks
     */
    private function fetch_items(): void
    {
        $this->items = $this->usage->get_patterns(
            $this->params,
            [ 'use_struct' => false ]
        );
    }

    /**
     * Get all patterns names
     */
    private function pattern_names(): array
    {
        return array_filter(
            array_column(
                ( WP_Block_Patterns_Registry::get_instance() )->get_all_registered(),
                'name'
            ),
            fn ($name) => BlankPage::get_name() !== $name
        );
    }

    /**
     * Get post types that can contain patterns
     *
     * @return string[]
     */
    private function allowed_post_types(): array
    {
        return array_filter(
            get_post_types([ 'show_in_rest' => true ]),
            fn ($t) => post_type_supports($t, 'editor')
        );
    }
}
