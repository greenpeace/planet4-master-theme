<?php

/**
 * Reusable Blocks class
 */

namespace P4\MasterTheme\Controllers;

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
     * Admin page markup.
     *
     */
    public function admin_page_display(): void
    {
        echo '
            <h1>Reusable Blocks</h1>
            <p>
                Managing Reusable Blocks (now called "Synced" Patterns) has been moved in a new place under
                <a href="site-editor.php?path=/patterns">Appearance &gt; Patterns</a>.
            </p>
        ';
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
            __('All Reusable blocks', 'planet4-master-theme-backend'),
            __('All Reusable blocks', 'planet4-master-theme-backend'),
            'edit_posts',
            'edit.php?post_type=' . self::POST_TYPE,
            [ $this, 'admin_page_display' ]
        );
    }
}
