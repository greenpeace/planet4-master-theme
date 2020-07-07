<?php

namespace P4\MasterTheme\ImageArchive;

class ImageSize implements \JsonSerializable {
	private $url;
	private $width;
	private $height;

	/**
	 * @param $data
	 *
	 * @return self[]
	 */
	public static function all_from_api_response( $data ): array {
		$keys = [
			'Path_TR7',
			'Path_TR1_COMP_SMALL',
			'Path_TR1',
			'Path_TR4',
			'Path_TR1_COMP',
			'Path_TR2',
			'Path_TR3',
		];

		return array_reduce( $keys,
			static function ( $carry, $key ) use ( $data ) {
				if ( ! empty( $data[ $key ]['URI'] ) ) {

					$size = new self();

					$size->url    = $data[ $key ]['URI'];
					$size->width  = (int) $data[ $key ]['Width'];
					$size->height = (int) $data[ $key ]['Height'];

					$carry[] = $size;
				}

				return $carry;
			},
			[] );
	}

	public function jsonSerialize(): array {
		return [
			'url'    => $this->url,
			'width'  => $this->width,
			'height' => $this->height,
		];
	}

	/**
	 * @return mixed
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * @return mixed
	 */
	public function get_width() {
		return $this->width;
	}
}
