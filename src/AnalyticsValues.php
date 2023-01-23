<?php

namespace P4\MasterTheme;

/**
 * Data class for post editor analytics values.
 */
final class AnalyticsValues
{
    private const CACHE_KEY = 'analytics_global_projects';

    /**
     * Hardcoded list of Global projects, in case of google sheet unavailability.
     */
    public const GLOBAL_PROJECTS = [
        [
            'global_project_name' => 'All Eyes on the Amazon',
            'tracking_id' => 'GP85',
        ],
        [
            'global_project_name' => 'Amazon Reef',
            'tracking_id' => 'ID003',
        ],
        [
            'global_project_name' => 'Asia Energy Transition',
            'tracking_id' => 'GP110',
        ],
        [
            'global_project_name' => 'BrAndino: Hold the Line',
            'tracking_id' => 'ID021',
        ],
        [
            'global_project_name' => 'Break Free',
            'tracking_id' => 'GP148',
        ],
        [
            'global_project_name' => 'Climate Emergency',
            'tracking_id' => 'ID022',
        ],
        [
            'global_project_name' => 'Climate Emergency Response',
            'tracking_id' => 'GP192',
        ],
        [
            'global_project_name' => 'Climate Justice Liability',
            'tracking_id' => 'GP25',
        ],
        [
            'global_project_name' => 'Congo Basin Forests',
            'tracking_id' => 'GP82',
        ],
        [
            'global_project_name' => 'Corporate ICE/ Clean Air Now',
            'tracking_id' => 'GP99',
        ],
        [
            'global_project_name' => 'Covid-19 Response',
            'tracking_id' => 'ID031',
        ],
        [
            'global_project_name' => 'Cross-commodities markets campaign',
            'tracking_id' => null,
        ],
        [
            'global_project_name' => 'Ends of the Earth',
            'tracking_id' => 'GP98',
        ],
        [
            'global_project_name' => 'European Energy Transition',
            'tracking_id' => 'GP44',
        ],
        [
            'global_project_name' => 'Greenpeace Fires',
            'tracking_id' => 'GP84',
        ],
        [
            'global_project_name' => 'Indonesia Forests',
            'tracking_id' => 'GP52',
        ],
        [
            'global_project_name' => 'Local Campaign',
            'tracking_id' => 'ID001',
        ],
        [
            'global_project_name' => 'Meat & Dairy',
            'tracking_id' => 'GP50',
        ],
        [
            'global_project_name' => 'Ocean Sanctuaries',
            'tracking_id' => 'GP29',
        ],
        [
            'global_project_name' => 'Patagonia',
            'tracking_id' => 'ID013',
        ],
        [
            'global_project_name' => 'People vs. Oil',
            'tracking_id' => 'GP191',
        ],
        [
            'global_project_name' => 'Pipelines',
            'tracking_id' => 'GP96',
        ],
        [
            'global_project_name' => 'Plastics Free Future',
            'tracking_id' => 'GP89',
        ],
        [
            'global_project_name' => 'Reuse revolution',
            'tracking_id' => null,
        ],
        [
            'global_project_name' => 'Shifting the trillions',
            'tracking_id' => 'GP147',
        ],
        [
            'global_project_name' => 'Stolen Fish',
            'tracking_id' => 'GP05',
        ],
        [
            'global_project_name' => 'The Future of Europe project',
            'tracking_id' => 'GP07',
        ],
        [
            'global_project_name' => 'Urban Revolution',
            'tracking_id' => 'GP90',
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

        $instance = self::using_google();

        wp_cache_add(
            self::CACHE_KEY,
            ! $instance ? null : $instance->to_cache_array(),
            null,
            300
        );

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
        $global_sheet = $google_client->get_sheet($global_sheet_id);
        if (! $global_sheet) {
            return null;
        }

        $local_sheet_id = planet4_get_option('analytics_local_google_sheet_id') ?? $_ENV['analytics_local_google_sheet_id'] ?? null;

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
     * @return string[] The options.
     */
    public function global_projects_options(): array
    {
        $names = array_map(
            function ($project) {
                return $project['global_project_name'];
            },
            $this->global_projects
        );

        return [ 'not set' => __('- Select Global Project -', 'planet4-master-theme-backend') ] + array_combine($names, $names);
    }

    /**
     * Returns local(NRO) project spreadsheet options.
     *
     * @return array
     */
    public function local_projects_options(): array
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

        return [ 'not set' => __('- Select Local Project -', 'planet4-master-theme-backend') ] + $local_projects_options;
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
}
