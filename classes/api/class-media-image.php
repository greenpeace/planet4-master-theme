<?php

namespace P4ML\Api;

/**
 * A class that represents an image from gpi media library.
 *
 * @package P4ML\Api
 */
class MediaImage implements \JsonSerializable {

	/**
	 * Media Library System Identifier.
	 *
	 * @var $id
	 */
	private $id;

	/**
	 * Image Title.
	 *
	 * @var $title
	 */
	private $title;

	/**
	 * Image Caption.
	 *
	 * @var $caption
	 */
	private $caption;

	/**
	 * Image Credit.
	 *
	 * @var $credit
	 */
	private $credit;

	/**
	 * @var
	 */
	private $path_tr1;

	/**
	 * @var
	 */
	private $path_tr2;

	/**
	 * The attachement id if the image is stored in WordPress db.
	 *
	 * @var $wordpress_id
	 */
	private $wordpress_id;

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 *
	 * @return MediaImage
	 */
	public function setId( $id ) {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param mixed $title
	 *
	 * @return MediaImage
	 */
	public function setTitle( $title ) {
		$this->title = $title;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCaption() {
		return $this->caption;
	}

	/**
	 * @param mixed $caption
	 *
	 * @return MediaImage
	 */
	public function setCaption( $caption ) {
		$this->caption = $caption;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCredit() {
		return $this->credit;
	}

	/**
	 * @param mixed $credit
	 */
	public function setCredit( $credit ) {
		$this->credit = $credit;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getPathTr1() {
		return $this->path_tr1;
	}

	/**
	 * @param mixed $path_tr1
	 *
	 * @return MediaImage
	 */
	public function setPathTr1( $path_tr1 ) {
		$this->path_tr1 = $this->sanitize_url($path_tr1);

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPathTr2() {
		return $this->path_tr2;
	}

	/**
	 * @param mixed $path_tr2
	 *
	 * @return MediaImage
	 */
	public function setPathTr2( $path_tr2 ) {
		$this->path_tr2 = $path_tr2;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getWordpressId() {
		return $this->wordpress_id;
	}

	/**
	 * @param mixed $wordpress_id
	 *
	 * @return MediaImage
	 */
	public function setWordpressId( $wordpress_id ) {
		$this->wordpress_id = $wordpress_id;

		return $this;
	}

	/**
	 * Implement jsonSerialiaze method
	 *
	 * @return array|mixed
	 */
	public function jsonSerialize() {
		return get_object_vars( $this );
	}

	/**
	 * Strip query params from a url.
	 *
	 * @param string $url Url.
	 *
	 * @return bool|string
	 */
	private function sanitize_url( $url ) {
		return substr( $url, 0, strrpos( $url, '?' ) );
	}
}
