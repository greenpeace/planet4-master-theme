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
			->setId( (string) $params['SystemIdentifier'] )
			->setTitle( (string) $params['Title'] )
			->setCaption( (string) $params['Caption'] )
			->setCredit( isset( $params['Artist'] ) ? $params['Artist'] : '' )
			->setPathTr1( (string) $params['Path_TR1']['URI'] )
			->setOriginalLanguageTitle( isset( $params['original-language-title'] ) ? (string) $params['original-language-title'] : '' )
			->setOriginalLanguageDesc( isset( $params['original-language-description'] ) ? (string) $params['original-language-description'] : '' );
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
