<?php

namespace P4ML\Api;

/**
 * A helper class to instantianate a new MediaImage object.
 *
 * @package P4ML\Api
 */
class MediaImageMapper {

	public function getFromArray( array $params ) {
		return ( new MediaImage() )
			->setId( (string) $params['SystemIdentifier'] )
			->setTitle( (string) $params['Title'] )
			->setCaption( (string) $params['Caption'] )
			->setCredit( isset( $params['Artist'] ) ? $params['Artist'] : '' )
			->setPathTr1( (string) $params['Path_TR1']['URI'] );
	}

	public function getAllFromArray( array $encs ) {
		$data = [];
		foreach ( $encs as $enc ) {
			$data[] = $this->getFromArray( $enc );
		}

		return $data;
	}
}