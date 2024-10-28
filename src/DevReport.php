<?php

namespace P4\MasterTheme;

/**
 * Class DevReport
 */
class DevReport
{
    /**
     * Option key, and option page slug
     *
     */
    private string $key = 'planet4_dev_report';

    /**
     * Constructor
     */
    public function __construct()
    {

        // Set our title.
        $this->title = 'Development';
        $this->hooks();
    }

    /**
     * Register our setting to WP.
     */
    public function init(): void
    {
        register_setting($this->key, $this->key);
    }

    /**
     * Initiate our hooks
     */
    public function hooks(): void
    {
        add_action('admin_init', [ $this, 'init' ]);
        add_action('admin_menu', [ $this, 'add_options_page' ], 99);
    }

    /**
     * Add menu options page.
     */
    public function add_options_page(): void
    {
        add_submenu_page(
            'planet4_settings_navigation',
            $this->title,
            $this->title,
            'manage_options',
            $this->key,
            [ $this, 'admin_page_display' ]
        );
    }

    /**
     * Admin page markup. Mostly handled by CMB2.
     */
    public function admin_page_display(): void
    {
        echo '<h1>P4 Dev report</h1>' . "\n";
        $gp_packages = get_option('greenpeace_packages');

        if (!$gp_packages) {
            return;
        }

        foreach ($gp_packages as $gp_package) {
            $pack0 = isset($gp_package[0]) ? $gp_package[0] : null;
            $pack1 = isset($gp_package[1]) ? $gp_package[1] : null;
            $url = isset($gp_package[2]['url']) ? $gp_package[2]['url'] : null;
            $reference = isset($gp_package[2]['reference']) ? $gp_package[2]['reference'] : null;

            if ($url && '.git' === substr($url, -4)) {
                $new_url = substr($url, 0, -4);
            }

            if ($pack1 && 'dev-' === substr($pack1, 0, 4)) {
                $branch = substr($pack1, 4);
            } else {
                $branch = $pack1;
            }

            $branch_history_url = $new_url && $branch ? $new_url . '/commits/' . $branch : null;
            $commit_url = $new_url && $reference ? $new_url . '/commit/' . $reference : null;

            if ($pack0) {
                echo '<h3>' . esc_html($pack0) . "</h3>\n";
            }

            if ($branch && $branch_history_url) {
                echo "<p>Version (tag/branch): <a href='" . esc_url($branch_history_url) . "'>"
                . esc_html($branch) . "</a></p>\n";
            }

            if ($url) {
                echo "<p>Source repo: <a href='" . esc_url($url) . "'>"
                . esc_html($url) . "</a></p>\n";
            }

            if (!$commit_url || !$reference) {
                continue;
            }

            echo "<p>Source hash: <a href='" . esc_url($commit_url) . "'>"
            . esc_html($reference) . "</a></p>\n";
        }
    }
}
