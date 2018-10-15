<?php

namespace P4ML\Controllers;

if ( ! class_exists( 'MediaLibraryApi_Controller' ) ) {

	/**
	 * Class MediaLibraryApi_Controller
	 *
	 * @package P4ML\Controllers
	 */
	class MediaLibraryApi_Controller {

		const ML_BASE_URL     = 'https://media.greenpeace.org';
		const ML_AUTH_URL     = self::ML_BASE_URL . '/API/Authentication/v1.0/Login';
		const ML_SEARCH_URL   = self::ML_BASE_URL . '/API/search/v3.0/search';
		const ML_CALL_TIMEOUT = 10;            // Seconds after which the api call will timeout if not responded.
		const MEDIAS_PER_PAGE = 20;

		const ERROR   = 0;
		const WARNING = 1;
		const NOTICE  = 2;
		const SUCCESS = 3;

		/** @var string $ml_auth_token */
		protected $ml_auth_token;
		/** @var string $search_query */
		protected $search_query;
		/** @var array|bool|null $posts */
		protected $selected_sort;
		/** @var array $api_param */
		protected $api_param;
		/** @var int $current_page */
		public $current_page;
		/** @var array $messages */
		protected $messages = [];

		/**
		 * MediaLibraryApi_Controller constructor.
		 */
		public function __construct() {

			$p4ml_settings       = get_option( 'p4ml_main_settings' );
			$this->ml_auth_token = $this->authenticate( $p4ml_settings['p4ml_api_username'], $p4ml_settings['p4ml_api_password'] );
			$this->initialize();
		}

		/**
		 * Initialize api query param.
		 */
		protected function initialize() {

			$this->api_param = [
				'query'        => '(Mediatype:Image)',
				'fields'       => 'Title,Caption,Artist,ArtistShortID,Path_TR1,Path_TR1_COMP_SMALL,Path_TR7,Path_TR4,Path_TR1_COMP,Path_TR2,Path_TR3,SystemIdentifier,original-language-title,original-language-description,original-language,restrictions',
				'countperpage' => self::MEDIAS_PER_PAGE,
				'format'       => 'json',
				'token'        => $this->ml_auth_token,
			];
		}

		/**
		 * Authenticate & generate ML API token.
		 *
		 * @param string $p4ml_login_id The media library loginID to be used in order to authenticate for ML API.
		 * @param string $p4ml_password The media library password to be used in order to authenticate for ML API.
		 *
		 * @return string The string of API token fetch from ML.
		 */
		public function authenticate( $p4ml_login_id, $p4ml_password ) {

			$url           = self::ML_AUTH_URL;
			$ml_auth_token = '';

			if ( isset( $p4ml_login_id ) && $p4ml_login_id && isset( $p4ml_password ) && $p4ml_password ) {
				// Check if the authentication API call is cached.
				$ml_auth_token = get_transient( 'ml_auth_token' );
				if ( false === $ml_auth_token ) {

					// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
					$response = wp_safe_remote_post( $url, [
						'body'      => [
							'Login'    => $p4ml_login_id,
							'Password' => $p4ml_password,
							'format'   => 'json',
						],
						'timeout'   => self::ML_CALL_TIMEOUT,
						'sslverify' => false,

					] );

					// Authentication failure.
					if ( is_wp_error( $response ) ) {
						$response = $response->get_error_message() . ' ' . $response->get_error_code();

					} elseif ( is_array( $response ) && \WP_Http::ACCEPTED !== $response['response']['code'] ) {
						$response = $response['response']['message'] . ' ' . $response['response']['code'];
					}

					if ( is_array( $response ) && $response['body'] ) {
						// Communication with ML API is authenticated.
						$body          = json_decode( $response['body'], true );
						$ml_auth_token = $body['APIResponse']['Token'];
						// Time period in seconds to keep the ml_auth_token before refreshing. Typically 1 hour.
						if ( isset( $body['APIResponse']['TimeoutPeriodMinutes'] ) ) {
							$expiration = (int) ( $body['APIResponse']['TimeoutPeriodMinutes'] ) * 60;
						} else {
							$expiration = 60 * 60; // Default expirations in 1hr.
						}
						set_transient( 'ml_auth_token', $ml_auth_token, $expiration );
					} else {
						$this->error( $response );
					}
				}
			} else {
				$this->warning( __( 'Plugin Settings are not configured well!', 'planet4-medialibrary' ) );
			}

			return $ml_auth_token;
		}

		/**
		 * Fetch data from GP media library using search API.
		 *
		 * @param array $params The query parameters to be added in the url.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function get_results( $params = [] ) {
			$response_data = [
				'result'        => [],
				'status_code'   => '',
				'error_message' => '',
			];
			$media_list    = [];

			if ( isset( $params['search_text'] ) && '' !== $params['search_text'] ) {
				$this->search_query       = $params['search_text'];
				$this->api_param['query'] = '(text:' . $params['search_text'] . ') and (Mediatype:Image)';
			}

			$url = self::ML_SEARCH_URL;
			$url = add_query_arg( $this->api_param, $url );

			if ( isset( $params['pagenumber'] ) && 0 < $params['pagenumber'] ) {
				$this->current_page        = $params['pagenumber'];
				$page_number['pagenumber'] = $params['pagenumber'];
				$url                       = add_query_arg( $page_number, $url );
			}

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_remote_get( $url, [
				'timeout'   => self::ML_CALL_TIMEOUT,
				'sslverify' => false,
			] );

			if ( is_wp_error( $response ) ) {
				$response_data['status_code']   = $response->get_error_code();
				$response_data['error_message'] = $response->get_error_message();

			} elseif ( is_array( $response ) ) {
				if ( \WP_Http::OK !== $response['response']['code'] ) {                     // Authentication failed.
					$response_data['error_message'] = $response['response']['message'];
				} elseif ( $response['body'] ) {
					$image_data = json_decode( $response['body'], true );
					if ( isset( $image_data['APIResponse']['Items'] ) ) {
						foreach ( $image_data['APIResponse']['Items'] as $key => $details ) {
							$media_list[ $key ] = $this->get_media_details( $details );
						}
					}
					$response_data['result'] = $media_list;
				}
				$response_data['status_code'] = $response['response']['code'];
			}

			return $response_data;
		}

		/**
		 * Returns media details, from media library response.
		 *
		 * @param array $details The media library single media array.
		 *
		 * @return array
		 */
		public function get_media_details( $details ) {

			$media_details = [];

			if ( is_array( $details ) && $details ) {
				$media_details['image_title']   = $details['Title'];
				$media_details['image_caption'] = $details['Caption'];
				$media_details['image_credit']  = $details['Artist'];
				$media_details['gpml_image_id'] = $details['SystemIdentifier'];

				if ( $details['Path_TR7']['URI'] ) {
					$media_details['image_url'] = $details['Path_TR7']['URI'];
				} elseif ( $details['Path_TR1_COMP_SMALL']['URI'] ) {
					$media_details['image_url'] = $details['Path_TR1_COMP_SMALL']['URI'];
				} elseif ( $details['Path_TR1']['URI'] ) {
					$media_details['image_url'] = $details['Path_TR1']['URI'];
				} elseif ( $details['Path_TR4']['URI'] ) {
					$media_details['image_url'] = $details['Path_TR4']['URI'];
				} elseif ( $details['Path_TR1_COMP']['URI'] ) {
					$media_details['image_url'] = $details['Path_TR1_COMP']['URI'];
				} elseif ( $details['Path_TR2']['URI'] ) {
					$media_details['image_url'] = $details['Path_TR2']['URI'];
				} elseif ( $details['Path_TR3']['URI'] ) {
					$media_details['image_url'] = $details['Path_TR3']['URI'];
				}

				$media_details['ori_lang_title'] = $details['original-language-title'];
				$media_details['ori_lang_desc']  = $details['original-language-description'];
				$media_details['restrictions']   = $details['restrictions'];

				// Filter file name for extra url params.
				$media_details['image_url'] = str_replace( strstr( $media_details['image_url'], '?' ), '', $media_details['image_url'] );
			}

			return $media_details;
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

		/**
		 * Query the api for a single image.
		 *
		 * @param string $image_id Image id.
		 *
		 * @return array|string
		 */
		public function get_single_image( $image_id ) {
			$media_list = [];

			$this->api_param['query'] = 'SystemIdentifier:' . $image_id;

			$url = self::ML_SEARCH_URL;
			$url = add_query_arg( $this->api_param, $url );

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_remote_get( $url, [
				'timeout'   => self::ML_CALL_TIMEOUT,
				'sslverify' => false,
			] );

			if ( is_wp_error( $response ) ) {
				return $response->get_error_message() . ' ' . $response->get_error_code();

			} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];         // Authentication failed.
			}

			if ( is_array( $response ) && $response['body'] ) {
				$image_data = json_decode( $response['body'], true );
				if ( isset( $image_data['APIResponse']['Items'] ) ) {
					foreach ( $image_data['APIResponse']['Items'] as $key => $details ) {
						$media_list[ $key ] = ( $details );
					}
				}
			} else {
				return $response['APIResponse']['Code'];
			}

			return $media_list;
		}
	}
}
