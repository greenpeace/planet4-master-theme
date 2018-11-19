<?php

use Timber\User as TimberUser;

if ( ! class_exists( 'P4_User' ) ) {

	/**
	 * Class P4_User extends Timber\User.
	 *
	 * Ref: https://timber.github.io/docs/reference/timber-user/
	 */
	class P4_User extends TimberUser {

		/** @var bool $is_fake */
		public $is_fake = false;

		/**
		 * P4_User constructor.
		 *
		 * @param object|int|bool $uid The P4_User id.
		 * @param string          $author_override The author override display name.
		 */
		public function __construct( $uid = false, $author_override = '' ) {
			if ( is_int( $uid ) ) {
				parent::__construct( $uid );
			} else {
				$this->display_name = $author_override;
				$this->is_fake      = true;
			}
		}

		/**
		 * The P4_User profile page url.
		 *
		 * @return string
		 */
		public function link() : string {
			if ( $this->is_fake ) {
				return '#';
			} else {
				return parent::link();
			}
		}

		/**
		 * The relative path of the P4_User profile page.
		 *
		 * @return string
		 */
		public function path() : string {
			if ( $this->is_fake ) {
				return '#';
			} else {
				return parent::path();
			}
		}

		/**
		 * Author display name.
		 *
		 * @return null|string
		 */
		public function name() {
			if ( $this->is_fake ) {
				return $this->display_name;
			} else {
				return parent::name();
			}
		}

		/**
		 * Author description.
		 *
		 * @return null|string
		 */
		public function description() {
			if ( $this->is_fake ) {
				return null;
			} else {
				return $this->description;
			}
		}

		/**
		 * Stringifies the P4_User object.
		 *
		 * @return null|string
		 */
		public function __toString() {
			if ( $this->is_fake ) {
				return $this->name();
			} else {
				return parent::__toString();
			}
		}
	}
}
