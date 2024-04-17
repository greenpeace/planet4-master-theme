<?php

namespace P4\MasterTheme;

/**
 * Data class for post editor analytics values.
 */
final class AnalyticsValues
{
    private const CACHE_KEY = 'analytics_global_projects';
    private const GLOBAL_PROJECT_SHEET_TAB_NAME = 'Global Projects Standards';

    /**
     * Hardcoded list of Global projects, in case of google sheet unavailability.
     */
    public const GLOBAL_PROJECTS = [
        [
            'global_project_name' => 'Aguos con El Clima',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Alternative Futures',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Amazon',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Beyond Seafood',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'COP27 Project',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Climate Justice Liability',
            'tracking_id' => 'GP25',
        ],
        [
            'global_project_name' => 'Climate and Justice',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Collective Climate Action',
            'tracking_id' => 'GP199',
        ],
        [
            'global_project_name' => 'Common Power',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'FSO Safer',
            'tracking_id' => 'ID046',
        ],
        [
            'global_project_name' => 'False Solutions China',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Food, Forest and Nature',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Fossil Free Revolution',
            'tracking_id' => 'ID038',
        ],
        [
            'global_project_name' => 'Green Ideas Project',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Mobility for All',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Money For Change',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Plastic Free Future',
            'tracking_id' => 'GP89',
        ],
        [
            'global_project_name' => 'Protect the Oceans',
            'tracking_id' => 'GP185',
        ],
        [
            'global_project_name' => 'Say No to Gas',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Stolen Fish',
            'tracking_id' => 'GP05',
        ],
        [
            'global_project_name' => 'Stop Deep Sea Mining',
            'tracking_id' => 'ID000',
        ],
        [
            'global_project_name' => 'Ummah for Earth',
            'tracking_id' => 'GP184',
        ],
        [
            'global_project_name' => 'We Are Nature',
            'tracking_id' => 'ID000',
        ],
    ];

    /**
     * @var string[] List of global projects.
     */
    private array $global_projects;

    /**
     * @var string[]|null List of local projects.
     */
    private ?array $local_projects;

    /**
     * AnalyticsValues constructor.
     *
     * @param string[]      $global_projects A list of the global projects.
     * @param string[]|null $local_projects A list of the local projects.
     */
    private function __construct(array $global_projects, ?array $local_projects = null)
    {
        $this->global_projects = empty($global_projects)
            ? self::GLOBAL_PROJECTS
            : $global_projects;
        $this->local_projects = $local_projects;
    }

    /**
     * Extract values from cache array.
     *
     * @param array $cache_array The cache array.
     * @return static The instance.
     */
    public static function from_cache_array(array $cache_array): self
    {
        return new self($cache_array['global_projects'], $cache_array['local_projects']);
    }

    /**
     * Export values to cache array.
     *
     * @return array The data for cache.
     */
    public function to_cache_array(): array
    {
        return [
            'global_projects' => $this->global_projects,
            'local_projects' => $this->local_projects,
        ];
    }

    /**
     * Look in cache, then try fetch API if cache is obsolete, then fall back to hardcoded values.
     *
     * @return static The instance.
     */
    public static function from_cache_or_api_or_hardcoded(): self
    {
        $found = false;
        $cache = wp_cache_get(self::CACHE_KEY, '', false, $found);

        if ($found) {
            if (null === $cache) {
                return self::from_hardcoded_values();
            }

            return self::from_cache_array($cache);
        }

        try {
            $instance = self::using_google();
            wp_cache_add(
                self::CACHE_KEY,
                ! $instance ? null : $instance->to_cache_array(),
                null,
                300
            );
        } catch (\Throwable $e) {
            if (function_exists('\Sentry\captureException')) {
                \Sentry\captureException($e);
            }
            $instance = null;
        }

        return $instance ?? self::from_hardcoded_values();
    }

    /**
     * Fetch using Google Sheets.
     *
     * @return static|null The instance if possible.
     */
    private static function using_google(): ?self
    {
        if (! defined('GOOGLE_SHEETS_KEY')) {
            return null;
        }

        $google_client = GoogleDocsClient::from_account_config(GOOGLE_SHEETS_KEY);
        if (! $google_client) {
            return null;
        }
        $global_sheet_id = $_ENV['ANALYTICS_GLOBAL_GOOGLE_SHEET_ID'] ?? '1pDAj0jR7WWzUOzBwviFeMGjV8-ocyrE21HYB75oVfOc';

        if (! $global_sheet_id) {
            return null;
        }

        $global_sheet = $google_client->get_sheet($global_sheet_id, self::GLOBAL_PROJECT_SHEET_TAB_NAME);

        if (! $global_sheet) {
            return null;
        }

        $local_sheet_id = planet4_get_option('analytics_local_google_sheet_id')
            ?? $_ENV['analytics_local_google_sheet_id'] ?? null;

        $local_sheet = ! $local_sheet_id ? null : $google_client->get_sheet($local_sheet_id);

        return self::from_spreadsheets($global_sheet, $local_sheet, true);
    }

    /**
     * Extract values from from_spreadsheets.
     *
     * @param Spreadsheet      $global_spreadsheet Spreadsheet containing global projects data.
     * @param Spreadsheet|null $local_spreadsheet  Spreadsheet containing local projects data.
     * @param bool             $uses_google Google sheets have different boolean columns.
     *
     * @return static
     */
    public static function from_spreadsheets(
        Spreadsheet $global_spreadsheet,
        ?Spreadsheet $local_spreadsheet = null,
        bool $uses_google = false
    ): self {
        $project_name_column = $global_spreadsheet->get_column_index('Global Project Standard');
        $approved_column = $global_spreadsheet->get_column_index('Standard Approved');
        $tracking_id_column = $global_spreadsheet->get_column_index('Tracking ID');

        $column_names = [
            $project_name_column => 'global_project_name',
            $tracking_id_column => 'tracking_id',
        ];

        $global_projects = $global_spreadsheet
            ->filter_by_column($approved_column, $uses_google ? 'yes' : true)
            ->sort_on_column($project_name_column)
            ->export_columns($column_names);

        $local_projects = null;
        if (null !== $local_spreadsheet) {
            // Fetch local (NRO) spreadsheet data.
            $project_name_column = $local_spreadsheet->get_column_index('Local Project Standard');
            $approved_column = $local_spreadsheet->get_column_index('Local Sync');

            $local_projects = $local_spreadsheet
                ->filter_by_column($approved_column, $uses_google ? 'yes' : true)
                ->sort_on_column($project_name_column)
                ->export_columns([ $project_name_column => 'local_project_name' ]);
        }

        return new self($global_projects, $local_projects);
    }

    /**
     * Get data from hardcoded list.
     *
     * @return static The instance.
     */
    public static function from_hardcoded_values(): self
    {
        return new self(self::GLOBAL_PROJECTS);
    }

    /**
     * Get the global project options for the dropdown in the post editor.
     *
     * @param int $post_id The post id.
     * @return string[] The options.
     */
    public function global_projects_options(int $post_id): array
    {
        $names = array_map(
            function ($project) {
                return $project['global_project_name'];
            },
            $this->global_projects
        );

        $global_options = [
            'not set' => __('- Select Global Project -', 'planet4-master-theme-backend'),
        ] + array_combine($names, $names);

        if ($post_id) {
            $global_options = self::maybe_add_current_post_value(
                $global_options,
                'p4_campaign_name',
                $post_id
            );
        }

        return $global_options;
    }

    /**
     * Returns local(NRO) project spreadsheet options.
     *
     * @param int $post_id The post id.
     */
    public function local_projects_options(int $post_id): array
    {
        $local_projects_options = [];
        if ($this->local_projects) {
            $names = array_map(
                function ($project) {
                    return $project['local_project_name'];
                },
                $this->local_projects
            );

            $local_projects_options = array_combine($names, $names);
        }

        $local_projects_options = [
            'not set' => __('- Select Local Project -', 'planet4-master-theme-backend'),
        ] + $local_projects_options;

        if ($post_id) {
            $local_projects_options = self::maybe_add_current_post_value(
                $local_projects_options,
                'p4_local_project',
                $post_id
            );
        }

        return $local_projects_options;
    }

    /**
     * Analytics baskets
     */
    public function basket_options(): array
    {
        return [
            'not set' => __('- Select Basket -', 'planet4-master-theme-backend'),
            'Forests' => 'Forests',
            'Oceans' => 'Oceans',
            'Good Life' => 'Good Life',
            'Food' => 'Food',
            'Climate &amp; Energy' => 'Climate & Energy',
            'Oil' => 'Oil',
            'Plastics' => 'Plastics',
            'Political &amp; Business' => 'Political & Business',
        ];
    }

    /**
     * Look up a project by its name and get its ID.
     *
     * @param string $global_project_name The unique name of the project.
     * @return string|null The ID if the project is found, else null.
     */
    public function get_id_for_global_project(string $global_project_name): ?string
    {
        $matching_project = null;
        foreach ($this->global_projects as $global_project) {
            if ($global_project['global_project_name'] !== $global_project_name) {
                continue;
            }

            $matching_project = $global_project;
        }
        if (empty($matching_project)) {
            return null;
        }

        return $matching_project['tracking_id'];
    }

    /**
     * If the post has a value that is not in the sheet, keep it in the dropdown so that metaboxes save doesn't set
     * it to another value, but mark it with `[DEPRECATED]` prefix.
     *
     * @param array  $options_array The list of supported global/local projects.
     * @param string $field_name The meta field name.
     * @param int $post_id The post id.
     * @return array The list with maybe the current post value.
     */
    private static function maybe_add_current_post_value(array $options_array, string $field_name, int $post_id): array
    {
        if (! $post_id) {
            return $options_array;
        }

        $current_post_meta_value = get_post_meta($post_id, $field_name, true);

        if (
            $current_post_meta_value
            && ! ( array_key_exists(
                $current_post_meta_value,
                $options_array
            ) )
        ) {
            $options_array = [
                    $current_post_meta_value => __('[DEPRECATED] ', 'planet4-master-theme-backend')
                        . $current_post_meta_value,
                ] + $options_array;
        }

        return $options_array;
    }
}
