<?php

namespace P4ML\Controllers\Tab;

use P4ML\Views\View;

if ( ! class_exists( 'Controller' ) ) {

	/**
	 * Class Controller
	 *
	 * This class will control all the main functions of the plugin.
	 * @package P4ML\Controllers\Tab
	 */
	abstract class Controller {

		const ERROR   = 0;
		const WARNING = 1;
		const NOTICE  = 2;
		const SUCCESS = 3;

		/** @var View $view */
		protected $view;
		/** @var array $messages */
		protected $messages = [];


		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 *
		 * @param View $view The view object.
		 */
		public function __construct( View $view ) {
			$this->view = $view;
		}

		/**
		 * Hooks the method that Creates the menu item for the current controller.
		 */
		public function load() {
		}

		/**
		 * Display an escaped error message inside the admin panel.
		 *
		 * @param string $msg   The message to display.
		 * @param string $title The title of the message.
		 */
		public function error( $msg, $title = '' ) {
			if ( is_string( $msg ) ) {
				array_push($this->messages, [
					'msg'     => esc_html( $msg ),
					'title'   => $title ? esc_html( $title ) : esc_html__( 'Error', 'planet4-medialibrary' ),
					'type'    => self::ERROR,
					'classes' => 'p4ml_error_message',
				] );
			}
		}

		/**
		 * Display an escaped warning message inside the admin panel.
		 *
		 * @param string $msg   The message to display.
		 * @param string $title The title of the message.
		 */
		public function warning( $msg, $title = '' ) {
			if ( is_string( $msg ) ) {
				array_push($this->messages, [
					'msg'     => esc_html( $msg ),
					'title'   => $title ? esc_html( $title ) : esc_html__( 'Warning', 'planet4-medialibrary' ),
					'type'    => self::WARNING,
					'classes' => 'p4ml_warning_message',
				] );
			}
		}

		/**
		 * Display an escaped notice message inside the admin panel.
		 *
		 * @param string $msg   The message to display.
		 * @param string $title The title of the message.
		 */
		public function notice( $msg, $title = '' ) {
			if ( is_string( $msg ) ) {
				array_push($this->messages, [
					'msg'     => esc_html( $msg ),
					'title'   => $title ? esc_html( $title ) : esc_html__( 'Notice', 'planet4-medialibrary' ),
					'type'    => self::NOTICE,
					'classes' => 'p4ml_notice_message',
				] );
			}
		}

		/**
		 * Display an escaped success message inside the admin panel.
		 *
		 * @param string $msg   The message to display.
		 * @param string $title The title of the message.
		 */
		public function success( $msg, $title = '' ) {
			if ( is_string( $msg ) ) {
				array_push($this->messages, [
					'msg'     => esc_html( $msg ),
					'title'   => $title ? esc_html( $title ) : esc_html__( 'Success', 'planet4-medialibrary' ),
					'type'    => self::SUCCESS,
					'classes' => 'p4ml_success_message',
				] );
			}
		}
	}
}
