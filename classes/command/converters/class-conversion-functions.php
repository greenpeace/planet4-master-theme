<?php
/**
 * Trait that holds various functions to transform shortcake planet4-plugin-blocks blocks attributes to
 * planet4-plugin-gutenberg-blocks gutenberg blocks attributes.
 *
 * @package P4GBKS\Command\Converters
 */

namespace P4GBKS\Command\Converters;

/**
 * Used to implement custom attributes conversion functions that could be shared between different convertors.
 *
 * @package P4GBKS\Command\Converters
 */
trait Conversion_Functions {

	/**
	 * Convert focal points from 'center center' to {"x": 0.5, "y": 0.5}
	 *
	 * @param string $focus_image Focus image attribute from shortcake_happy_point block.
	 *
	 * @return object
	 */
	protected function convert_focus_image( $focus_image ) {
		switch ( $focus_image ) {
			case 'left top':
				return (object) [
					'x' => 0,
					'y' => 0,
				];
			case 'center top':
				return (object) [
					'x' => 0.5,
					'y' => 0,
				];
			case 'right top':
				return (object) [
					'x' => 1,
					'y' => 0,
				];
			case 'left center':
				return (object) [
					'x' => 0,
					'y' => 0.5,
				];
			case 'center center':
				return (object) [
					'x' => 0.5,
					'y' => 0.5,
				];
			case 'right center':
				return (object) [
					'x' => 1,
					'y' => 0.5,
				];
			case 'left bottom':
				return (object) [
					'x' => 0,
					'y' => 1,
				];
			case 'center bottom':
				return (object) [
					'x' => 0.5,
					'y' => 1,
				];
			case 'right bottom':
				return (object) [
					'x' => 1,
					'y' => 1,
				];
		}

		return (object) [
			'x' => 0.5,
			'y' => 0.5,
		];
	}

	/**
	 * Convert focal points from 'center center' to '50% 50%'
	 *
	 * @param string $focus_image Focus image attribute from shortcake_carousel_header block.
	 *
	 * @return string
	 */
	protected function convert_focus_image_percentages( $focus_image ) {
		switch ( $focus_image ) {
			case 'left top':
				return '0% 0%';
			case 'center top':
				return '50% 0%';
			case 'right top':
				return '100% 0%';
			case 'left center':
				return '0% 50%';
			case 'center center':
				return '50% 50%';
			case 'right center':
				return '100% 50%';
			case 'left bottom':
				return '0% 100%';
			case 'center bottom':
				return '50% 100%';
			case 'right bottom':
				return '100% 100%';
		}

		return '50% 50%';
	}

	/**
	 * Convert gallery focal points
	 * From
	 * '{'442':'left top','443':'left top','444':'left top','445':'left top'}'
	 *
	 * To
	 * '{'442':'50% 50%','443':'50% 50%','444':'50% 50%','445':'50% 50%'}'
	 *
	 * @param string $focus_image Focus image attribute from shortcake_gallery block.
	 *
	 * @return string
	 */
	protected function convert_gallery_focus_points( $focus_image ) {

		$focus_points = json_decode( str_replace( "'", '"', $focus_image ) );

		if ( ! is_object( $focus_points ) ) {
			return '';
		}

		foreach ( $focus_points as $img_id => &$focal_points ) {

			$focal_points = self::convert_focus_image_percentages( $focal_points );
		}

		return json_encode( $focus_points ); // phpcs:ignore
	}

	/**
	 * @param string $view Old newcover's block covers_view attribute.
	 *
	 * @return string
	 */
	public function convert_covers_view( $view ) {
		switch ( $view ) {
			case '0':
				return '1';
			case '3':
				return '2';
			case '1':
				return '3';
		}

		return $view;
	}
}
