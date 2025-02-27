<?php

namespace P4\MasterTheme;

use WP_Post;

/**
 * Adds export related UI.
 */
class Exporter
{
    /**
     * AutoLoad Hooks
     * */
    public function __construct()
    {
        add_action('admin_action_export_data', [ $this, 'single_post_export_data' ]);
        add_filter('post_row_actions', [ $this, 'single_post_export' ], 10, 2);
        add_filter('page_row_actions', [ $this, 'single_post_export' ], 10, 2);
        add_action('admin_footer', [ $this, 'single_post_export_bulk' ]);
        add_action('load-edit.php', [ $this, 'single_post_export_bulk_action' ]);
        add_action('admin_head', [ $this, 'add_import_button' ]);
    }

    /**
     * Main function
     */
    public function single_post_export_data(): void
    {
        $post_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT)
            ?? filter_input(INPUT_POST, 'post', FILTER_SANITIZE_NUMBER_INT);
        if (! empty($post_id) && 'export_data' === sanitize_text_field($_GET['action'])) {
            include get_template_directory() . '/exporter.php';
        } else {
            wp_die('No post to export has been supplied!');
        }
    }

    /**
     * Export multiple data
     */
    public function single_post_export_bulk(): void
    {
        if (!current_user_can('edit_posts')) {
            return;
        }
        $text = __('Export', 'planet4-master-theme-backend');
        do_action('enqueue_bulk_export_script', $text);
    }

    /**
     * Export Bulk Action
     */
    public function single_post_export_bulk_action(): ?bool
    {
        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();
        $allowed_actions = [ 'export' ];
        if (! in_array($action, $allowed_actions, true)) {
            return false;
        }

        $validated_posts = array_filter(
			$_REQUEST['post'], // phpcs:ignore
            function ($element) {
                return filter_var($element, FILTER_VALIDATE_INT);
            }
        );

        switch ($action) {
            case 'export':
                $sendback = 'admin.php?action=export_data&post=' . join(',', $validated_posts);
                break;

            default:
                return false;
        }
        wp_safe_redirect($sendback);
        exit();
    }

    /**
     * Add Export Link
     *
     * @param array   $actions array.
     * @param WP_Post $post object.
     * @return array  $actions array.
     */
    public function single_post_export(array $actions, WP_Post $post): array
    {
        if (current_user_can('edit_posts')) {
            $export_url = esc_url(admin_url('admin.php?action=export_data&amp;post=' . $post->ID));
            $actions['export'] = '<a href="' . $export_url . '" title="'
                . __('Export', 'planet4-master-theme-backend') . '" rel="permalink">'
                . __('Export', 'planet4-master-theme-backend') . '</a>';
        }

        return $actions;
    }

    /**
     * Add Import Button
     */
    public function add_import_button(): void
    {
        $label = __('Import Greenpeace Media', 'planet4-master-theme-backend');
        do_action('enqueue_media_import_button_script', $label);
    }
}
