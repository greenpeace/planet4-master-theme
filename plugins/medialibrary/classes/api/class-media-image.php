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
	 * Image restrictions.
	 *
	 * @var $restrictions
	 */
	private $restrictions;

	/**
	 * GPML image path.
	 *
	 * @var $path_tr1
	 */
	private $path_tr1;

	/**
	 * GPML image path.
	 *
	 * @var $path_tr2
	 */
	private $path_tr2;

	/**
	 * The attachement id if the image is stored in WordPress db.
	 *
	 * @var $wordpress_id
	 */
	private $wordpress_id;

	/**
	 * The Original language title of attachement.
	 *
	 * @var $original_language_title
	 */
	private $original_language_title;

	/**
	 * The Original language description of attachement.
	 *
	 * @var $original_language_description
	 */
	private $original_language_description;

	/**
	 * Retrieves a attachement ID.
	 *
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Update attachement ID.
	 *
	 * @param mixed $id The media attachment ID.
	 *
	 * @return MediaImage
	 */
	public function set_id( $id ) {
		$this->id = $id;

		return $this;
	}

	/**
	 * Retrieves a attachement title.
	 *
	 * @return mixed
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Update attachement title.
	 *
	 * @param mixed $title The media attachment title.
	 *
	 * @return MediaImage
	 */
	public function set_title( $title ) {
		$this->title = $title;

		return $this;
	}

	/**
	 * Retrieves a attachement caption.
	 *
	 * @return mixed
	 */
	public function get_caption() {
		return $this->caption;
	}

	/**
	 * Update attachement caption.
	 *
	 * @param mixed $caption The media attachment caption.
	 *
	 * @return MediaImage
	 */
	public function set_caption( $caption ) {
		$this->caption = $caption;

		return $this;
	}

	/**
	 * Retrieves a attachement credit.
	 *
	 * @return mixed
	 */
	public function get_credit() {
		return $this->credit;
	}

	/**
	 * Update attachement credit.
	 *
	 * @param mixed $credit The media attachment credit.
	 * @return mixed
	 */
	public function set_credit( $credit ) {
		$this->credit = $credit;
		return $this;
	}

	/**
	 * Retrieves a attachement restrictions.
	 *
	 * @return mixed
	 */
	public function get_media_restrictions() {
		return $this->restrictions;
	}

	/**
	 * Update attachement restrictions.
	 *
	 * @param mixed $restrictions The media attachment restrictions.
	 * @return mixed
	 */
	public function set_media_restrictions( $restrictions ) {
		$this->restrictions = $restrictions;
		return $this;
	}


	/**
	 * Retrieves a attachement path.
	 *
	 * @return mixed
	 */
	public function get_path_tr1() {
		return $this->path_tr1;
	}

	/**
	 * Update attachement tr1 path.
	 *
	 * @param mixed $path_tr1 The media attachment tr1 path.
	 *
	 * @return MediaImage
	 */
	public function set_path_tr1( $path_tr1 ) {
		$this->path_tr1 = $this->sanitize_url( $path_tr1 );

		return $this;
	}

	/**
	 * Retrieves a attachement path.
	 *
	 * @return mixed
	 */
	public function get_path_tr2() {
		return $this->path_tr2;
	}

	/**
	 * Update attachement tr2 path.
	 *
	 * @param mixed $path_tr2 The media attachment tr1 path.
	 *
	 * @return MediaImage
	 */
	public function set_path_tr2( $path_tr2 ) {
		$this->path_tr2 = $path_tr2;

		return $this;
	}

	/**
	 * Retrieves a attachements original language title.
	 *
	 * @return mixed
	 */
	public function get_original_language_title() {
		return $this->original_language_title;
	}

	/**
	 * Update attachements original language title.
	 *
	 * @param mixed $org_lang_title The media attachment original language title.
	 *
	 * @return MediaImage
	 */
	public function set_original_language_title( $org_lang_title ) {
		$this->original_language_title = $org_lang_title;

		return $this;
	}

	/**
	 * Retrieves a attachements original language description.
	 *
	 * @return mixed
	 */
	public function get_original_language_desc() {
		return $this->original_language_description;
	}

	/**
	 * Update attachements original language description.
	 *
	 * @param mixed $org_lang_desc The media attachment original language description.
	 *
	 * @return MediaImage
	 */
	public function set_original_language_desc( $org_lang_desc ) {
		$this->original_language_description = $org_lang_desc;

		return $this;
	}

	/**
	 * Retrieves a attachement WordPress ID.
	 *
	 * @return mixed
	 */
	public function get_wordpress_id() {
		return $this->wordpress_id;
	}

	/**
	 * Update attachements WordPress ID.
	 *
	 * @param mixed $wordpress_id The media attachment WordPress ID.
	 *
	 * @return MediaImage
	 */
	public function set_wordpress_id( $wordpress_id ) {
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
