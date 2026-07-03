<?php

namespace P4\MasterTheme\CustomPostType;

/**
 * Class ActionImporter
 */
class ActionImporter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', [ $this, 'add_options_page' ], 99);
    }

    /**
     * Add menu options page.
     */
    public function add_options_page(): void
    {
        add_submenu_page(
            'edit.php?post_type=p4_action',
            __('Import Action', 'planet4-master-theme-backend'),
            __('Import Action', 'planet4-master-theme-backend'),
            'manage_options',
            'import-action',
            [ $this, 'admin_page_display' ]
        );
    }

    public function admin_page_display(): void
    {
        echo '<h2>Import Action</h2>' . "\n"; ?>

        <form method="post">
            <?php wp_nonce_field('import_action'); ?>
            <table class="form-table">
                <tr>
                    <th><?php echo __('URL', 'planet4-master-theme-backend'); ?></th>
                    <td>
                        <input
                            type="url"
                            name="import_url"
                            class="regular-text"
                            required
                            placeholder="https://act.greenpeace.org/landing-page"
                        />
                        <p>Verify the results on submission. This feature has mostly been tested with Hubspot landing pages.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Import Action', 'planet4-master-theme-backend')); ?>
        </form>
        <?php
    }
}
