<?php
/**
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

/**
 * Fetch a google spreadsheet from the provided url and display it as a sortable and filterable table.
 */
class Spreadsheet extends Base_Block {
	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	public const BLOCK_NAME = 'spreadsheet';

	private const MAX_ROWS = 10000;

	private const CACHE_LIFETIME = 30;

	/**
	 * SpreadsheetTable constructor.
	 */
	public function __construct() {
		register_block_type(
			self::BLOCK_NAMESPACE_PREFIX . '/' . self::BLOCK_NAME,
			[
				'editor_script' => 'planet4-blocks',
				'attributes'    => [
					'url'           => [
						'type'    => 'string',
						'default' => '',
					],
					'css_variables' => self::CSS_VARIABLES_ATTRIBUTE,
				],
			]
		);
	}

	/**
	 * Required by the `Base_Block` class.
	 *
	 * @param array $fields Unused, required by the abstract function.
	 */
	public function prepare_data( $fields ): array {
		return [];
	}

	/**
	 * Fetch a Google sheet by its ID.
	 *
	 * @param string|null $sheet_id The ID of the Google sheet.
	 * @param bool        $skip_cache Should the sheet be fetched from cache.
	 * @return array|null The sheet or null if nothing was found.
	 */
	public static function get_sheet( ?string $sheet_id, bool $skip_cache ): ?array {
		if ( ! $sheet_id ) {
			return null;
		}

		$cache_key = "spreadsheet_${sheet_id}";

		if ( ! $skip_cache ) {
			$from_cache = wp_cache_get( $cache_key );

			if ( false !== $from_cache ) {
				return $from_cache;
			}
		}

		$url = "https://docs.google.com/spreadsheets/d/e/${sheet_id}/pub?output=csv";

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$handle = fopen( $url, 'rb' );

		if ( false === $handle ) {
			return null;
		}

		$rows = [];
		while (
			( $data = fgetcsv( $handle, 1000, ',' ) ) !== false // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops,WordPress.CodeAnalysis.AssignmentInCondition
		) {
			if ( count( $rows ) > self::MAX_ROWS ) {
				break;
			}

			$rows[] = $data;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $handle );

		if ( 0 === count( $rows ) ) {
			$sheet = null;
		} else {
			$sheet = [
				'header' => $rows[0],
				'rows'   => array_slice( $rows, 1 ),
			];
		}

		wp_cache_add( $cache_key, $sheet, null, self::CACHE_LIFETIME );

		return $sheet;
	}
}
