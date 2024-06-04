<?php

/**
 * Blocks report Settings Controller
 */

namespace P4\MasterTheme\Controllers\Menu;

/**
 * Class BlocksReportController
 */
class BlocksReportController extends Controller
{
    public const P4BKS_REPORTS_SLUG_NAME = 'plugin_blocks_report';

    /**
     * Create menu/submenu entry.
     */
    public function create_admin_menu(): void
    {
        $current_user = wp_get_current_user();

        if (( !in_array('administrator', $current_user->roles, true) && !in_array('editor', $current_user->roles, true) ) || !current_user_can('edit_posts')) {
            return;
        }

        add_menu_page(
            __('Blocks', 'planet4-blocks-backend'),
            __('Blocks', 'planet4-blocks-backend'),
            'edit_posts',
            self::P4BKS_REPORTS_SLUG_NAME,
            null,
            'dashicons-layout'
        );
    }
}
