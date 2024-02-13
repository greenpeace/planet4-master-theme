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
            $url = $gp_package[2]['url'];
            if ('.git' === substr($url, -4)) {
                $url = substr($url, 0, -4);
            }
            if ('dev-' === substr($gp_package[1], 0, 4)) {
                $branch = substr($gp_package[1], 4);
            } else {
                $branch = $gp_package[1];
            }
            $branch_history_url = $url . '/commits/' . $branch;
            $commit_url = $url . '/commit/' . $gp_package[2]['reference'];

            echo '<h3>' . esc_html($gp_package[0]) . "</h3>\n";
            echo "<p>Version (tag/branch): <a href='" . esc_url($branch_history_url) . "'>"
                . esc_html($branch) . "</a></p>\n";
            echo "<p>Source repo: <a href='" . esc_url($gp_package[2]['url']) . "'>"
                . esc_html($gp_package[2]['url']) . "</a></p>\n";
            echo "<p>Source hash: <a href='" . esc_url($commit_url) . "'>"
                . esc_html($gp_package[2]['reference']) . "</a></p>\n";
        }
    }
}
