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

	private const MAX_ROWS = 1000;

	/**
	 * SpreadsheetTable constructor.
	 */
	public function __construct() {
		register_block_type(
			'planet4-blocks/spreadsheet',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'url' => [
						'type'    => 'string',
						'default' => '',
					],
				],
			]
		);
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $fields ): array {
		// Enqueue script for table filter.
		wp_enqueue_script( 'spreadsheet-table', P4GBKS_PLUGIN_URL . 'public/js/spreadsheet.js', [ 'jquery' ], '0.2', true );

		try {
			$id = self::extract_sheet_id( $fields['url'] );
		} catch ( \InvalidArgumentException $exception ) {
			$fields['error'] = true;
		}

		if ( isset( $id ) ) {

			$url = "https://docs.google.com/spreadsheets/d/e/${id}/pub?output=csv";

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
			$handle = fopen( $url, 'rb' );

			if ( false !== $handle ) {
				$rows = [];
				while (
					// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
					( $data = fgetcsv( $handle, 1000, ',' ) ) !== false
					// phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops
					&& count( $rows ) <= self::MAX_ROWS
				) {
					$rows[] = $data;
				}
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				fclose( $handle );
				$fields['rows'] = $rows;
			}
		}

		return [ 'fields' => $fields ];
	}

	/**
	 * Get the ID from a google sheets publication url.
	 *
	 * @param string $url The url of the sheet's publication.
	 * @return string the ID found in the sheet's publication url.
	 * @throws \InvalidArgumentException The url is not a valid google sheets url.
	 */
	private static function extract_sheet_id( string $url ): string {
		$google_sheets_pattern = '/docs\.google\.com\/spreadsheets\/d\/e\/([\w-]+)/';

		$matches = [];
		preg_match( $google_sheets_pattern, $url, $matches );

		$id = $matches[1] ?? null;
		if ( ! $id ) {
			throw new \InvalidArgumentException( 'The url provided is not a valid public google sheets url.' );
		}

		return $id;
	}
}
