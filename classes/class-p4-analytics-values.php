<?php
/**
 * Data class for post editor analytics values.
 *
 * @package P4MT
 */

/**
 * Data class for post editor analytics values.
 */
final class P4_Analytics_Values {
	private const CACHE_KEY = 'analytics_global_projects';

	/**
	 * @var string[] List of global projects.
	 */
	private $global_projects;

	/**
	 * @var string[] List of local projects.
	 */
	private $local_projects;

	/**
	 * P4_Analytics_Values constructor.
	 *
	 * @param string[]      $global_projects A list of the global projects.
	 * @param string[]|null $local_projects A list of the local projects.
	 */
	private function __construct( array $global_projects, ?array $local_projects = null ) {
		$this->global_projects = $global_projects;
		$this->local_projects  = $local_projects;
	}

	/**
	 * Extract values from smartsheets.
	 *
	 * @param P4_Smartsheet      $global_smartsheet Smartsheet containing global projects data.
	 * @param P4_Smartsheet|null $local_smartsheet Smartsheet containing local projects data.
	 * @return static
	 */
	public static function from_smartsheets(
		P4_Smartsheet $global_smartsheet,
		P4_Smartsheet $local_smartsheet = null
	): self {
		$project_name_column = $global_smartsheet->get_column_index( 'Global Project Standard' );
		$approved_column     = $global_smartsheet->get_column_index( 'Standard Approved' );
		$tracking_id_column  = $global_smartsheet->get_column_index( 'Tracking ID' );

		$global_projects = $global_smartsheet->filter_by_column( $approved_column, true )->sort_on_column(
			$project_name_column
		)->export_columns(
			[
				$project_name_column => 'global_project_name',
				$tracking_id_column  => 'tracking_id',
			]
		);

		$local_projects = null;
		if ( null !== $local_smartsheet ) {
			// Fetch local (NRO) smartsheet data.
			$project_name_column = $local_smartsheet->get_column_index( 'Local Project Standard' );
			$approved_column     = $local_smartsheet->get_column_index( 'Local Sync' );

			$local_projects = $local_smartsheet->filter_by_column( $approved_column, true )->sort_on_column( $project_name_column )->export_columns(
				[
					$project_name_column => 'local_project_name',
				]
			);
		}

		return new self( $global_projects, $local_projects );
	}

	/**
	 * Extract values from cache array.
	 *
	 * @param array $cache_array The cache array.
	 * @return static The instance.
	 */
	public static function from_cache_array( array $cache_array ): self {
		return new self( $cache_array['global_projects'], $cache_array['local_projects'] );
	}

	/**
	 * Export values to cache array.
	 *
	 * @return array The data for cache.
	 */
	public function to_cache_array(): array {
		return [
			'global_projects' => $this->global_projects,
			'local_projects'  => $this->local_projects,
		];
	}

	/**
	 * Look in cache, then try fetch API if cache is obsolete, then fall back to hardcoded values.
	 *
	 * @return static The instance.
	 */
	public static function from_cache_or_api_or_hardcoded(): self {
		$cache = wp_cache_get( self::CACHE_KEY );

		if ( false !== $cache ) {
			return self::from_cache_array( $cache );
		}

		$api_key = planet4_get_option( 'smartsheet_api_key' ) ?? getenv( 'SMARTSHEET_API_KEY' );

		if ( ! $api_key ) {
			return self::from_hardcoded_values();
		}

		$smartsheet_client = P4_Smartsheet_Client::from_api_key( $api_key );

		$global_sheet_id = planet4_get_option( 'analytics_global_smartsheet_id' ) ?? $_ENV['ANALYTICS_GLOBAL_SMARTSHEET_ID'] ?? '4212503304529796';

		if ( ! $global_sheet_id ) {
			return self::from_hardcoded_values();
		}
		$global_sheet = $smartsheet_client->get_sheet( $global_sheet_id );

		if ( null === $global_sheet ) {
			return self::from_hardcoded_values();
		}

		$local_sheet    = null;
		$local_sheet_id = planet4_get_option( 'analytics_local_smartsheet_id' ) ?? $_ENV['ANALYTICS_LOCAL_SMARTSHEET_ID'] ?? null;

		if ( $local_sheet_id ) {
			$local_sheet = $local_sheet_id ? $smartsheet_client->get_sheet( $local_sheet_id ) : null;
		}

		$instance = self::from_smartsheets( $global_sheet, $local_sheet );

		wp_cache_add( self::CACHE_KEY, $instance->to_cache_array(), null, 300 );

		return $instance;
	}

	/**
	 * Get data from hardcoded list.
	 *
	 * @return static The instance.
	 */
	public static function from_hardcoded_values(): self {
		$global_projects = [
			[
				'global_project_name' => 'All Eyes on the Amazonia',
				'tracking_id'         => 'GP85',
			],
			[
				'global_project_name' => 'Amazon Reef',
				'tracking_id'         => 'ID003',
			],
			[
				'global_project_name' => 'Asia Energy Transition',
				'tracking_id'         => 'GP110',
			],
			[
				'global_project_name' => 'BrAndino: Hold the Line',
				'tracking_id'         => 'ID021',
			],
			[
				'global_project_name' => 'Break Free',
				'tracking_id'         => 'GP148',
			],
			[
				'global_project_name' => 'Climate Emergency',
				'tracking_id'         => 'ID022',
			],
			[
				'global_project_name' => 'Climate Emergency Response',
				'tracking_id'         => 'GP192',
			],
			[
				'global_project_name' => 'Climate Justice Liability',
				'tracking_id'         => 'GP25',
			],
			[
				'global_project_name' => 'Congo Basin Forests',
				'tracking_id'         => 'GP82',
			],
			[
				'global_project_name' => 'Corporate ICE/ Clean Air Now',
				'tracking_id'         => 'GP99',
			],
			[
				'global_project_name' => 'Covid-19 Response',
				'tracking_id'         => 'ID031',
			],
			[
				'global_project_name' => 'Cross-commodities markets campaign',
				'tracking_id'         => null,
			],
			[
				'global_project_name' => 'Ends of the Earth',
				'tracking_id'         => 'GP98',
			],
			[
				'global_project_name' => 'European Energy Transition',
				'tracking_id'         => 'GP44',
			],
			[
				'global_project_name' => 'Greenpeace Fires',
				'tracking_id'         => 'GP84',
			],
			[
				'global_project_name' => 'Indonesia Forests',
				'tracking_id'         => 'GP52',
			],
			[
				'global_project_name' => 'Local Campaign',
				'tracking_id'         => 'ID001',
			],
			[
				'global_project_name' => 'Meat & Dairy',
				'tracking_id'         => 'GP50',
			],
			[
				'global_project_name' => 'Ocean Sanctuaries',
				'tracking_id'         => 'GP29',
			],
			[
				'global_project_name' => 'Patagonia',
				'tracking_id'         => 'ID013',
			],
			[
				'global_project_name' => 'People vs. Oil',
				'tracking_id'         => 'GP191',
			],
			[
				'global_project_name' => 'Pipelines',
				'tracking_id'         => 'GP96',
			],
			[
				'global_project_name' => 'Plastics Free Future',
				'tracking_id'         => 'GP89',
			],
			[
				'global_project_name' => 'Reuse revolution',
				'tracking_id'         => null,
			],
			[
				'global_project_name' => 'Shifting the trillions',
				'tracking_id'         => 'GP147',
			],
			[
				'global_project_name' => 'Stolen Fish',
				'tracking_id'         => 'GP05',
			],
			[
				'global_project_name' => 'The Future of Europe project',
				'tracking_id'         => 'GP07',
			],
			[
				'global_project_name' => 'Urban Revolution',
				'tracking_id'         => 'GP90',
			],
		];

		return new self( $global_projects );
	}

	/**
	 * Get the global project options for the dropdown in the post editor.
	 *
	 * @return string[] The options.
	 */
	public function global_projects_options(): array {
		$names = array_map(
			function ( $project ) {
				return $project['global_project_name'];
			},
			$this->global_projects
		);

		return [ 'not set' => __( '- Select Global Project -', 'planet4-master-theme-backend' ) ] + array_combine( $names, $names );
	}

	/**
	 * Returns local(NRO) project smartsheet options.
	 *
	 * @return array
	 */
	public function local_projects_options(): array {
		$local_projects_options = [];
		if ( $this->local_projects ) {
			$names = array_map(
				function ( $project ) {
					return $project['local_project_name'];
				},
				$this->local_projects
			);

			$local_projects_options = array_combine( $names, $names );
		}

		return [ 'not set' => __( '- Select Local Project -', 'planet4-master-theme-backend' ) ] + $local_projects_options;
	}

	/**
	 * Look up a project by its name and get its ID.
	 *
	 * @param string $global_project_name The unique name of the project.
	 * @return string|null The ID if the project is found, else null.
	 */
	public function get_id_for_global_project( string $global_project_name ): ?string {
		$matching_project = null;
		foreach ( $this->global_projects as $global_project ) {
			if ( $global_project['global_project_name'] === $global_project_name ) {
				$matching_project = $global_project;
			}
		}
		if ( empty( $matching_project ) ) {
			return null;
		}

		return $matching_project['tracking_id'];
	}
}
