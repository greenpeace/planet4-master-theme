<?php
/**
 * Table displaying blocks usage
 *
 * @package P4BKS\Controllers
 */

namespace P4GBKS\Search\Block;

use InvalidArgumentException;
use WP_List_Table;
use WP_Block_Type_Registry;
use P4GBKS\Search\Block\Query\Parameters;
use P4GBKS\Controllers\Menu\Blocks_Usage_Controller;

/**
 * Block usage API
 */
class BlockUsageApi {

	public const DEFAULT_POST_STATUS = [ 'publish' ];

	/**
	 * @var BlockUsage
	 */
	private $usage;

	/**
	 * @var Parameters
	 */
	private $params;

	/**
	 * @var array[] Blocks.
	 */
	private $items;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->usage  = new BlockUsage();
		$this->params = ( new Parameters() )
			->with_post_status( self::DEFAULT_POST_STATUS );
	}

	/**
	 * Count blocks by type and style
	 *
	 * If style is not specified, an empty key 'n/a' is used.
	 */
	public function get_count(): array {
		if ( null === $this->items ) {
			$this->fetch_items();
		}

		$types  = array_unique(
			array_column( $this->items, 'block_type' )
		);
		$blocks = array_fill_keys(
			$types,
			[
				'total'  => 0,
				'styles' => [],
			]
		);
		ksort( $blocks );

		foreach ( $this->items as $item ) {
			$styles = empty( $item['block_styles'] ) ? [ 'n/a' ] : $item['block_styles'];
			foreach ( $styles as $style ) {
				$type = $item['block_type'];
				if ( ! isset( $blocks[ $type ]['styles'][ $style ] ) ) {
					$blocks[ $type ]['styles'][ $style ] = 0;
				}
				$blocks[ $type ]['styles'][ $style ]++;
				$blocks[ $type ]['total']++;
			}
			ksort( $blocks[ $type ]['styles'] );
		}

		return $blocks;
	}

	/**
	 * Fetch parsed blocks
	 */
	private function fetch_items(): void {
		$this->items = $this->usage->get_blocks( $this->params );
	}
}
