<?php

namespace P4ML\Api;

/**
 * A helper class to instantianate a new MediaImage object.
 *
 * @package P4ML\Api
 */
class MediaImageMapper {

	/**
	 * Create a MediaImage object from an array containing the required params.
	 *
	 * @param array $params Parameters for instantiating a MediaImage object.
	 *
	 * @return MediaImage
	 */
	public function get_from_array( array $params ) {
		return ( new MediaImage() )
			->set_id( (string) $params['SystemIdentifier'] )
			->set_title( (string) $params['Title'] )
			->set_caption( (string) $params['Caption'] )
			->set_credit( $params['Artist'] ?? '' )
			->set_path_tr1( (string) $params['Path_TR1']['URI'] )
			->set_original_language_title( $params['original-language-title'] ?? '' )
			->set_original_language_desc( $params['original-language-description'] ?? '' );
	}

	/**
	 * Create a MediaImage objects array from an array containing the required params for the MediaImage objects.
	 *
	 * @param array $parameters Array containing arrays of MediaImage parameters.
	 *
	 * @return array
	 */
	public function get_all_from_array( array $parameters ) {
		$data = [];
		foreach ( $parameters as $image_params ) {
			$data[] = $this->get_from_array( $image_params );
		}

		return $data;
	}
}
