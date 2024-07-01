<?php

/**
 * Reusable Blocks class
 */

namespace P4\MasterTheme\Controllers\Menu;

/**
 * Class Reusable_Blocks_Controller
 */
class ReusableBlocksController extends Controller
{
    /**
     * Post type name.
     */
    private const POST_TYPE = 'wp_block';

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
            __('All Reusable blocks', 'planet4-blocks-backend'),
            __('All Reusable blocks', 'planet4-blocks-backend'),
            'edit_posts',
            'edit.php?post_type=' . self::POST_TYPE
        );
    }
}
