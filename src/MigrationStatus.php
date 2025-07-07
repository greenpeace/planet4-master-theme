<?php

namespace P4\MasterTheme;

/**
 * Class MigrationStatus
 */
class MigrationStatus
{
    /**
     * Option key, and option page slug
     *
     */
    private string $key = 'planet4_migration_status';

    private array $p4_migrations = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $p4_migrations = get_option('planet4_migrations');

        if (!$p4_migrations) {
            return;
        }
        $this->p4_migrations = $p4_migrations;

        // Set our title.
        $this->title = 'Data migrations';
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
        echo '<h2>Data migrations</h2>' . "\n";

        echo '<div class="migration-status">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Duration (s)</th>
                            <th>Status</th>
                            <th>Logs</th>
                        </tr>
                    </thead>
                    <tbody>';

        // Change migration order in Array to show recent first.
        $p4_migrations = array_reverse($this->p4_migrations, true);
        foreach ($p4_migrations as $key => $p4_migration) {
            $migration_status = $p4_migration['success'] ? 'Success' : 'Failed';
            $migration_status_class = $p4_migration['success'] ? 'success' : 'error';
            $migration_logs = implode('\n', $p4_migration['logs']);

            echo '<tr>
                    <td>' . $key . '</td>
                    <td>' . str_replace('P4\MasterTheme\Migrations\\', '', $p4_migration['id']) . '</td>
                    <td>
                        <div class="datetime">' . $p4_migration['start_time']->date . ' ' .
                $p4_migration['start_time']->timezone . '</div>
                    </td>
                    <td>
                        <div class="datetime">' . $p4_migration['end_time']->date . ' ' .
                $p4_migration['end_time']->timezone . '</div>
                    </td>
                    <td>' . $this->calculateTimeDiff($p4_migration['start_time'], $p4_migration['end_time']) . '</td>
                    <td class="' . $migration_status_class . '">' . $migration_status . '</td>
                    <td>
                        <div class="array-item">' . $migration_logs . '</div>
                    </td>
                </tr>';
        }

        echo '</tbody>
            </table>
        </div>';
    }

    /**
     * Calculates the precise time difference between two datetime Objects in seconds with microsecond precision.
     *
     * @param DateTime $start_time The starting datetime.
     * @param DateTime $end_time The ending datetime.
     * @return float The time difference in seconds with microsecond precision
     */
    public function calculateTimeDiff(\DateTime $start_time, \DateTime $end_time): float
    {
        // Calculate the difference.
        $interval = $start_time->diff($end_time);

        // Convert into seconds.
        $microseconds = ($end_time->format('u') - $start_time->format('u')) / 1000000;
        return $interval->s + $microseconds; // Total seconds.
    }
}
