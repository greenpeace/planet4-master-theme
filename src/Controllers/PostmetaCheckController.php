<?php

/**
 * Post meta duplicate records check class
 */

namespace P4\MasterTheme\Controllers;

use P4\MasterTheme\Commands\DuplicatedPostmeta;

/**
 * Class PostmetaCheckController
 */
class PostmetaCheckController extends Controller
{
    /**
     * Create menu/submenu entry.
     */
    public function create_admin_menu(): void
    {
        $current_user = wp_get_current_user();
        if (!in_array('administrator', $current_user->roles, true)) {
            return;
        }

        add_submenu_page(
            BlocksReportController::P4BKS_REPORTS_SLUG_NAME,
            __('Postmeta Check', 'planet4-blocks-backend'),
            __('Postmeta Check', 'planet4-blocks-backend'),
            'manage_options',
            'postmeta_report',
            [ $this, 'postmeta_check' ]
        );
    }

    /**
     * Handle form submit.
     *
     * @param mixed[] $data The form data.
     */
    public function handle_submit(array &$data): void
    {
        $remove_duplicate_postmeta = filter_input(INPUT_POST, 'delete_duplicate_postmeta', FILTER_SANITIZE_NUMBER_INT);
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !$remove_duplicate_postmeta) {
            return;
        }
        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        try {
            $deleted_rows = DuplicatedPostmeta::remove();
        } catch (\Error $e) {
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['message'] = __('Exception: ', 'planet4-blocks-backend') . $e->getMessage();
        }

        if ($deleted_rows) {
            // phpcs:disable Generic.Files.LineLength.MaxExceeded
            // translators: %d = The duplicate postmeta count.
            $data['message'] = sprintf(__('Remove %d duplicate postmeta records successfully.', 'planet4-blocks-backend'), $deleted_rows);
            // phpcs:enable Generic.Files.LineLength.MaxExceeded
        } else {
            $data['message'] = __('No whitelisted duplicate postmeta records found.', 'planet4-blocks-backend');
        }
    }

    /**
     * Render the admin page with duplicate postmeta details.
     */
    public function postmeta_check(): void
    {
        $data = [];

        $this->handle_submit($data);
        $data['duplicate_postmeta'] = DuplicatedPostmeta::detect();
        $data['postmeta_keys'] = DuplicatedPostmeta::META_KEY_LIST;

        $this->view->block('duplicate-postmeta-report', $data, 'twig', '');
    }
}
