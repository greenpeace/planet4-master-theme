<?php

/**
 * Blocks Usage class
 */

namespace P4\MasterTheme\Controllers;

use P4\MasterTheme\SqlParameters;
use P4\MasterTheme\BlockReportSearch\Block\BlockUsage;
use P4\MasterTheme\BlockReportSearch\Block\BlockUsageTable;
use P4\MasterTheme\BlockReportSearch\Block\BlockUsageApi;
use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters as BlockParameters;
use P4\MasterTheme\BlockReportSearch\Pattern\Query\Parameters as PatternParameters;
use P4\MasterTheme\BlockReportSearch\Pattern\PatternUsage;
use P4\MasterTheme\BlockReportSearch\Pattern\PatternUsageTable;
use P4\MasterTheme\BlockReportSearch\Pattern\PatternUsageApi;
use WP_Block_Type_Registry;
use WP_Block_Patterns_Registry;
use P4\MasterTheme\View\View;

/**
 * Class BlocksUsageController
 */
class BlocksUsageController extends Controller
{
    /**
     * Blocks_Usage_Controller constructor.
     *
     * @param View $view The view object.
     */
    public function __construct(View $view)
    {
        parent::__construct($view);
        $this->hooks();
    }

    /**
     * Class hooks.
     */
    private function hooks(): void
    {
        add_action('rest_api_init', [ $this, 'plugin_blocks_report_register_rest_route' ]);
        BlockUsageTable::set_hooks();
        PatternUsageTable::set_hooks();
    }

    /**
     * Register API route for report of blocks usage in pages/posts.
     */
    public function plugin_blocks_report_register_rest_route(): void
    {
        register_rest_route(
            'plugin_blocks/v3',
            '/plugin_blocks_report/',
            [
                'methods' => 'GET',
                'callback' => [ $this, 'plugin_blocks_report_rest_api' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Generates blocks/pages report.
     */
    public function plugin_blocks_report_rest_api(): ?array
    {
        global $wpdb;

        $use_cache = ! current_user_can('manage_options');
        $cache_key = 'plugin_blocks/v3/plugin_blocks_report';
        if ($use_cache) {
            $report = wp_cache_get($cache_key, 'api', false, $found);
            if ($found) {
                return $report;
            }
        }

        $types = \get_post_types(
            [
                'public' => true,
                'exclude_from_search' => false,
            ]
        );

        // Get posts types counts.
        $params = new SqlParameters();
        $sql = 'SELECT post_type, count(ID) AS post_count
            FROM ' . $params->identifier($wpdb->posts) . '
            WHERE post_status = ' . $params->string('publish') . '
                AND post_type IN ' . $params->string_list($types) . '
            GROUP BY post_type';
        $results = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->prepare($sql, $params->get_values()),
            \ARRAY_A
        );

        $post_types = array_combine(
            array_column($results, 'post_type'),
            array_map('intval', array_column($results, 'post_count'))
        );

        // Group results.
        $block_api = new BlockUsageApi();
        $pattern_api = new PatternUsageApi();
        $report = [
            'block_types' => $block_api->get_count(),
            'block_patterns' => $pattern_api->get_count(),
            'post_types' => $post_types,
        ];
        wp_cache_set($cache_key, $report, 'api', 60 * 5);

        return $report;
    }

    /**
     * Create menu/submenu entry.
     */
    public function create_admin_menu(): void
    {
        $current_user = wp_get_current_user();

        if (
            ( !in_array('administrator', $current_user->roles, true) &&
            !in_array('editor', $current_user->roles, true) ) ||
            !current_user_can('edit_posts')
        ) {
            return;
        }

        add_submenu_page(
            BlocksReportController::P4BKS_REPORTS_SLUG_NAME,
            __('Report', 'planet4-blocks-backend'),
            __('Report', 'planet4-blocks-backend'),
            'edit_posts',
            'plugin_blocks_report',
            [ $this, 'plugin_blocks_report' ]
        );

        add_submenu_page(
            BlocksReportController::P4BKS_REPORTS_SLUG_NAME,
            __('Pattern Report', 'planet4-blocks-backend'),
            __('Pattern Report', 'planet4-blocks-backend'),
            'edit_posts',
            'plugin_patterns_report',
            [ $this, 'plugin_patterns_report' ]
        );
    }

    /**
     * Block usage report page.
     */
    public function plugin_blocks_report(): void
    {
        // Nonce verify.
        if (isset($_REQUEST['filter_action'])) {
            check_admin_referer('bulk-' . BlockUsageTable::PLURAL);
        }

        // Create table.
        $args = [
            'block_usage' => new BlockUsage(),
            'block_registry' => WP_Block_Type_Registry::get_instance(),
        ];
        $table = new BlockUsageTable($args);

        // Prepare data.
        $special_filter = isset($_REQUEST['unregistered']) ? 'unregistered'
            : ( isset($_REQUEST['unallowed']) ? 'unallowed' : null );
        $table->prepare_items(
            BlockParameters::from_request($_REQUEST),
            $_REQUEST['group'] ?? null,
            $special_filter
        );

        // Display data.
        echo '<div class="wrap">
            <h1 class="wp-heading-inline">Block usage</h1>
            <hr class="wp-header-end">';

        echo '<form id="blocks-report" method="get">';
        $table->views();
        $table->search_box('Search in block attributes', 'blocks-report');
        $table->display();
        echo '<input type="hidden" name="action"
            value="' . esc_attr(BlockUsageTable::ACTION_NAME) . '"/>';
        echo '</form>';
        echo '</div>';
    }

    /**
     * Pattern usage report page.
     */
    public function plugin_patterns_report(): void
    {
        // Nonce verify.
        if (isset($_REQUEST['filter_action'])) {
            check_admin_referer('bulk-' . PatternUsageTable::PLURAL);
        }

        // Create table.
        $args = [
            'pattern_usage' => new PatternUsage(),
            'pattern_registry' => WP_Block_Patterns_Registry::get_instance(),
        ];
        $table = new PatternUsageTable($args);

        // Prepare data.
        $table->prepare_items(
            PatternParameters::from_request($_REQUEST),
            $_REQUEST['group'] ?? null
        );

        // Display data.
        echo '<div class="wrap">
            <h1 class="wp-heading-inline">Pattern usage</h1>
            <hr class="wp-header-end">';

        echo '<form id="patterns-report" method="get">';
        $table->views();
        $table->display();
        echo '<input type="hidden" name="action"
            value="' . esc_attr(PatternUsageTable::ACTION_NAME) . '"/>';
        echo '</form>';
        echo '</div>';
    }
}
