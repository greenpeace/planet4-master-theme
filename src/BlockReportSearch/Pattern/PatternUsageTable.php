<?php

/**
 * Table displaying patterns usage
 */

namespace P4\MasterTheme\BlockReportSearch\Pattern;

use InvalidArgumentException;
use WP_List_Table;
use WP_Block_Patterns_Registry;
use P4\MasterTheme\Patterns\BlankPage;
use P4\MasterTheme\BlockReportSearch\RowActions;
use P4\MasterTheme\BlockReportSearch\Pattern\Query\Parameters;

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . '/wp-admin/includes/class-wp-list-table.php';
}
// phpcs:enable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Show pattern usage, using native WordPress table
 */
class PatternUsageTable extends WP_List_Table
{
    public const ACTION_NAME = 'pattern_usage';

    public const DEFAULT_GROUP_BY = 'pattern_name';

    public const DEFAULT_POST_STATUS = [ 'publish', 'private', 'draft', 'pending', 'future' ];

    public const PLURAL = 'patterns';

    private PatternUsage $pattern_usage;

    private WP_Block_Patterns_Registry $pattern_registry;

    private Parameters $search_params;

    /**
     * @var string Group column.
     */
    private string $group_by = self::DEFAULT_GROUP_BY;

    /**
     * @var string[]
     */
    private array $allowed_groups = [ 'pattern_name', 'post_id', 'post_title' ];

    /**
     * @var string[]|null Columns name => title.
     */
    private ?array $columns = null;

    /**
     * @var string|null Latest row content displayed.
     */
    private ?string $latest_row = null;

    /**
     * @var string[]|null Pattern names.
     */
    private ?array $pattern_names = null;


    /**
     * @param array $args Args.
     * @throws InvalidArgumentException Throws on missing parameter.
     * @see WP_List_Table::__construct()
     */
    public function __construct(array $args = [])
    {
        $args['plural'] = self::PLURAL;
        parent::__construct($args);

        $this->pattern_usage = $args['pattern_usage'] ?? null;
        $this->pattern_registry = $args['pattern_registry'] ?? null;

        if (! ( $this->pattern_usage instanceof PatternUsage )) {
            throw new InvalidArgumentException(
                'Table requires a PatternUsage instance.'
            );
        }
        if (! ( $this->pattern_registry instanceof WP_Block_Patterns_Registry )) {
            throw new InvalidArgumentException(
                'Table requires a WP_Block_Patterns_Registry instance.'
            );
        }

        $this->pattern_names = array_filter(
            array_column(
                $this->pattern_registry->get_all_registered(),
                'name'
            ),
            fn ($name) => BlankPage::get_name() !== $name
        );
    }

    /**
     * @param Parameters|null $search_params Search parameters.
     * @param string|null     $group_by      Group.
     */
    public function prepare_items(
        ?Parameters $search_params = null,
        ?string $group_by = null
    ): void {
        if (in_array($group_by, $this->allowed_groups, true)) {
            $this->group_by = $group_by;
        }

        $this->search_params = $search_params
            ->with_post_type($this->allowed_post_types())
            ->with_post_status(self::DEFAULT_POST_STATUS);

        $items = $this->get_items();
        usort($items, fn ($a, $b) => $a[ $this->group_by ] <=> $b[ $this->group_by ]);

        // Pagination handling.
        $total_items = count($items);
        $per_page = 50;
        $current_page = $this->get_pagenum();
        $this->items = array_slice($items, ( ( $current_page - 1 ) * $per_page ), $per_page);
        $this->set_pagination_args(
            [
                'total_items' => $total_items,
                'per_page' => $per_page,
                'total_pages' => ceil($total_items / $per_page),
            ]
        );

        $this->_column_headers = $this->get_column_headers();
    }

    /**
     * Get patterns to display
     */
    private function get_items(): array
    {
        $search_params = clone $this->search_params;
        if (empty($search_params->name())) {
            $search_params = $search_params->with_name(
                $this->pattern_names
            );
        }

        return $this->pattern_usage->get_patterns($search_params);
    }

    /**
     * Allowed post types to search for
     */
    private function allowed_post_types(): array
    {
        return array_filter(
            get_post_types([ 'show_in_rest' => true ]),
            fn ($t) => post_type_supports($t, 'editor')
        );
    }

    /**
     * Pattern select
     */
    private function patternname_dropdown(): void
    {
        sort($this->pattern_names);
        $filter = $this->search_params->name() ?? [];

        echo '<select name="name" id="filter-by-name">';
        echo '<option value="">'
            . esc_html(__('- All patterns -', 'planet4-blocks-backend'))
            . '</option>';
        foreach ($this->pattern_names as $name) {
            echo sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr($name),
                esc_attr(in_array($name, $filter, true) ? 'selected' : ''),
                esc_html($name)
            );
        }
        echo '</select>';
    }

    /**
     * Add filters to table.
     *
     * @param string $which Tablenav identifier.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function extra_tablenav($which): void
    {
        echo '<div class="actions">';
        $this->patternname_dropdown();
        submit_button(
            __('Filter', 'planet4-blocks-backend'),
            '',
            'filter_action',
            false,
            [ 'id' => 'pattern-query-submit' ]
        );
        echo '</div>';
    }

    /**
     * Add pagination information to table.
     *
     * @param string $which Tablenav identifier.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function pagination($which): void
    {
        parent::pagination('top');
    }
    // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Show only top tablenav (duplicate form post bug)
     *
     * @param string $which Tablenav identifier.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function display_tablenav($which): void
    {
        if ('bottom' === $which) {
            echo '<div class="tablenav bottom">';
            parent::pagination($which);
            echo '</div>';
            return;
        }
        parent::display_tablenav($which);
    }

    /**
     * Add action links to a row
     *
     * @param array  $item        Item.
     * @param string $column_name Current column name.
     * @param string $primary     Primary column name.
     *
	 * phpcs:disable WordPress.WP.I18n.TextDomainMismatch, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint, SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
     */
    protected function handle_row_actions($item, $column_name, $primary)
    {
        return $this->row_actions(
            ( new RowActions() )->get_post_actions($item, $column_name, $primary)
        );
    }
    // phpcs:enable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

    /**
     * Columns list for table.
     */
    public function get_columns(): ?array
    {
        if (null !== $this->columns) {
            return $this->columns;
        }

        $default_columns = [
            'pattern_name' => 'Pattern',
            'post_title' => 'Title',
            'match_type' => 'Match',
            'pattern_occ' => 'Count',
            'post_date' => 'Created',
            'post_modified' => 'Modified',
            'post_id' => 'ID',
            'post_status' => 'Status',
        ];

        $this->columns = array_merge(
            [ $this->group_by => $default_columns[ $this->group_by ] ],
            $default_columns
        );

        return $this->columns;
    }

    /**
     * Available grouping as views.
     */
    protected function get_views(): array
    {
        $link_tpl = '<a href="%s">%s</a>';
        $active_link_tpl = '<a class="current" href="%s">%s</a>';
        return [
            'pattern_name' => sprintf(
                'pattern_name' === $this->group_by ? $active_link_tpl : $link_tpl,
                add_query_arg('group', 'pattern_name'),
                __('Group by pattern name', 'planet4-blocks-backend')
            ),
            'post_title' => sprintf(
                'post_title' === $this->group_by ? $active_link_tpl : $link_tpl,
                add_query_arg('group', 'post_title'),
                __('Group by post title', 'planet4-blocks-backend')
            ),
            'post_id' => sprintf(
                'post_id' === $this->group_by ? $active_link_tpl : $link_tpl,
                add_query_arg('group', 'post_id'),
                __('Group by post ID', 'planet4-blocks-backend')
            ),
        ];
    }

    /**
     * All, hidden and sortable columns.
     */
    private function get_column_headers(): array
    {
        return [
            $this->get_columns(),
            [],
            [ 'post_title', 'post_date', 'post_modified' ],
        ];
    }

    /**
     * Post title display.
     *
     * @param array $item Item.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function column_post_title($item): string
    {
        $content = $item['post_title'] ?? null;
        if (empty($content)) {
            return '';
        }

        $title_tpl = '%2$s';
        $link_tpl = '<a href="%s" title="%s">%s</a>';
        $page_uri = get_page_uri($item['post_id']);

        return sprintf(
            empty($page_uri) ? $title_tpl : $link_tpl,
            $page_uri,
            esc_attr($content),
            ( strlen($content) > 45 ? substr($content, 0, 45) . '...' : $content )
        );
    }

    /**
     * Full row display, edited for grouping functionality.
     *
     * @param array $item Item.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function single_row($item): void
    {
        $cols = $this->get_columns();
        $colspan = count($cols);
        $first_col = array_key_first($cols);

        if ($this->latest_row !== $item[ $first_col ]) {
            echo '<tr>';
            echo sprintf(
                '<th colspan="%s"><strong>%s</strong></th>',
                esc_attr($colspan),
                esc_html($item[ $first_col ])
            );
            echo '</tr>';
        }

        $this->latest_row = $item[ $first_col ];
        $item[ $first_col ] = '';
        parent::single_row($item);
    }

    /**
     * Default column value representation.
     *
     * @param array  $item Item.
     * @param string $column_name Column name.
     *
     * @return mixed
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function column_default($item, $column_name)
    {
        return $item[ $column_name ] ?? '';
    }
    // phpcs:enable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

    /**
     * Table URL
     */
    public static function url(): string
    {
        return admin_url('admin.php?page=plugin_patterns_report');
    }

    /**
     * Add redirection for filter action
     */
    public static function set_hooks(): void
    {
        add_action(
            'admin_action_' . self::ACTION_NAME,
            function (): void {
                $nonce = $_GET['_wpnonce'] ?? null;
                if (! \wp_verify_nonce($nonce, 'bulk-' . self::PLURAL)) {
                    \wp_safe_redirect(self::url());
                    exit;
                }

                $redirect_query = remove_query_arg(
                    [ '_wp_http_referer', '_wpnonce', 'action', 'filter_action' ],
                    \wp_parse_url($_SERVER['REQUEST_URI'], \PHP_URL_QUERY)
                );
                \parse_str($redirect_query, $args);
                $args = array_filter(
                    $args,
                    fn($e) => ! empty($e) && '0' !== $e
                );

                \wp_safe_redirect(add_query_arg($args, self::url()));
                exit;
            },
            10
        );
    }
}
