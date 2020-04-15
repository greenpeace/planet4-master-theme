<?php
/**
 * A data object for SmartSheet API responses.
 *
 * @package P4MT
 */

/**
 * A data object for SmartSheet API responses.
 */
final class P4_Smartsheet {
	/**
	 * @var array The columns of the sheet.
	 */
	private $columns;

	/**
	 * @var array The current rows of the sheet.
	 */
	private $rows;

	/**
	 * P4_Smartsheet constructor.
	 *
	 * @param array $columns The columns of the sheet.
	 * @param array $rows The rows of the sheet.
	 */
	private function __construct( array $columns, array $rows ) {
		$this->columns = $columns;
		$this->rows    = $rows;
	}

	/**
	 * Create an instance from data from the SmartSheet API.
	 *
	 * @param array $data The data from the SmartSheet API.
	 * @return static The instance.
	 * @throws InvalidArgumentException If the data doesn't have the correct keys.
	 */
	public static function from_api_response( array $data ): self {
		if ( ! isset( $data['columns'], $data['rows'] ) ) {
			throw new InvalidArgumentException( 'Cannot create from API data as it does not have rows.' );
		}
		return new self( $data['columns'], $data['rows'] );
	}

	/**
	 * Remove all rows that don't match the value in a specific column.
	 *
	 * @param int   $column_index Index of the column to use for filtering.
	 * @param mixed $column_value The column value to use for filtering.
	 * @return P4_Smartsheet
	 */
	public function filter_by_column( int $column_index, $column_value ): self {
		$rows = array_filter(
			$this->rows,
			function ( $row ) use ( $column_index, $column_value ) {
				return $row['cells'][ $column_index ]['value'] === $column_value;
			}
		);

		return new self( $this->columns, $rows );
	}


	/**
	 * Sort the rows by a column.
	 *
	 * @param int $column_index The column to sort on.
	 * @return P4_Smartsheet
	 */
	public function sort_on_column( int $column_index ): self {
		$rows = $this->rows;
		usort(
			$rows,
			function ( $row1, $row2 ) use ( $column_index ) {
				return $row1['cells'][ $column_index ] <=> $row2['cells'][ $column_index ];
			}
		);
		return new self( $this->columns, $rows );
	}

	/**
	 * Get an array of all the values for a certain column.
	 *
	 * @param int $column_index The index of the column to get values from.
	 * @return mixed[] The values in that column.
	 */
	public function get_column_values( int $column_index ): array {
		return array_map(
			function ( $row ) use ( $column_index ) {
				return $row['cells'][ $column_index ]['value'];
			},
			$this->rows
		);
	}

	/**
	 * Export a subset of the columns defined in $columns.
	 *
	 * @param array $columns The columns to be exported where the key is the column name and the value is the exported name.
	 * @return array The exported columns with the indexes provided in $columns.
	 */
	public function export_columns( array $columns ): array {
		return array_map(
			function ( $row ) use ( $columns ) {
				$cell_index = 0;

				return array_reduce(
					$row['cells'],
					function ( $carry, $cell ) use ( $columns, &$cell_index ) {
						if ( array_key_exists( $cell_index, $columns ) ) {
							$export_name = $columns[ $cell_index ];

							$carry[ $export_name ] = $cell['value'] ?? null;
						}
						++$cell_index;

						return $carry;
					},
					[]
				);
			},
			$this->rows
		);
	}

	/**
	 * Find the index of a column by its title.
	 *
	 * @param string $column_title The title to look up.
	 * @return int|null The id of the column, or null if no column was found.
	 */
	public function get_column_index( string $column_title ): ?int {
		foreach ( $this->columns as $column ) {
			if ( $column['title'] === $column_title ) {
				return $column['index'];
			}
		}
		return null;
	}
}
