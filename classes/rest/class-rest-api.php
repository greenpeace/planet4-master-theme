<?php
/**
 * @package P4GBKS\Rest
 */

namespace P4GBKS\Rest;

use P4GBKS\Blocks\Base_Block;
use P4GBKS\Blocks\NotImplemented;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;
use P4GBKS\Blocks\Spreadsheet;
use P4GBKS\Blocks\Articles;
use P4GBKS\Blocks\ENForm;
use P4GBKS\Blocks\SplitTwoColumns;
use P4GBKS\Blocks\Happypoint;
use P4GBKS\Blocks\Gallery;
use P4GBKS\Blocks\Covers;
use P4GBKS\Blocks\SocialMedia;
use P4\MasterTheme\AnalyticsValues;

/**
 * This class is just a place for add_endpoints to live.
 */
class Rest_Api {
	private const REST_NAMESPACE = 'planet4/v1';

	/**
	 * Add custom endpoints.
	 */
	public static function add_endpoints(): void {
		add_action( 'rest_api_init', [ __CLASS__, 'endpoints' ] );
	}

	/**
	 * Register custom rest API endpoints.
	 */
	public static function endpoints(): void {
		/**
		 * A lightweight endpoint to get all posts with only id and title.
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/published',
			[
				[
					'permission_callback' => [ Published::class, 'permission' ],
					'methods'             => Published::methods(),
					'callback'            => static function ( $request ) {
						$api = new Published( $request );
						return $api->response();
					},
				],
			]
		);

		/**
		 * Save meta to the preview of the current user.
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/save-preview-meta',
			[
				[
					'permission_callback' => static function () {
						return current_user_can( 'edit_posts' );
					},
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => static function ( $request ) {
						/**
						 * @var WP_REST_Request $request
						 */
						$post_id = $request['post_id'];

						$post = get_post( $post_id );

						if ( ! $post ) {
							return new \WP_REST_Response(
								'No such post exists.',
								400
							);
						}

						if ( ! current_user_can( 'edit_post', $post_id ) ) {
							return new \WP_REST_Response(
								'You do not have permission to edit this post.',
								403
							);

						}

						$old_autosave = wp_get_post_autosave( $post_id, get_current_user_id() );

						if ( ! $old_autosave ) {
							// No existing autosave, so let's create one. Should only happen once for each user.
							// @see \P4_Loader::do_not_delete_autosave The filter that ensures that.
							$revision_id = _wp_put_post_revision( $post, true );
						} else {
							$revision_id = $old_autosave->ID;
						}

						foreach ( $request['meta'] as $key => $value ) {
							update_metadata( 'post', $revision_id, $key, $value );
						}

						return rest_ensure_response( 'Saved all meta to the autosave revision.' );
					},
				],
			]
		);
		/**
		 * Endpoint to retrieve a Spreadsheet data and cache it.
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/get-spreadsheet-data',
			[
				[
					'permission_callback' => static function () {
						return true;
					},
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function () {
						$sheet_id = filter_input(
							INPUT_GET,
							'sheet_id',
							FILTER_VALIDATE_REGEXP,
							[
								'options' => [
									'regexp' => '/[\w\d\-]+/',
								],
							]
						);

						$sheet_data = Spreadsheet::get_sheet( $sheet_id, false );

						return rest_ensure_response( $sheet_data );
					},
				],
			]
		);

		/**
		 * Endpoint to retrieve the posts for the Articles block
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/get-posts',
			[
				[
					'permission_callback' => static function () {
						return true;
					},
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function ( $fields ) {
						$to_return = Articles::get_posts( $fields );
						return rest_ensure_response( $to_return );
					},
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/update_block/(?P<blockname>[a-z0-9-/]*)',
			[
				[
					'permission_callback' => static function () {
						return current_user_can( 'edit_pages' );
					},
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => static function ( WP_REST_Request $request ) {
						$blocks     = [
							Base_Block::NAMESPACE . '/' . SplitTwoColumns::BLOCK_NAME => SplitTwoColumns::class,
						];
						$block_name = $request->get_param( 'blockname' );
						/**
						 * @var Base_Block $block_class
						 */
						$block_class = $blocks[ $block_name ] ?? null;

						if ( null === $block_class ) {
							return new WP_Error( 'error', 'Unknown block ' . ( $block_name ?? 'unspecified' ) );
						}

						try {
							$response = $block_class::update_data( $request->get_params() );
						} catch ( NotImplemented $exception ) {
							return new WP_Error( 'error', $exception->getMessage() );
						}

						return rest_ensure_response( $response );
					},
				],
			]
		);

		/**
		 * Endpoint to retrieve the data for the Happypoint block
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/get-happypoint-data',
			[
				[
					'permission_callback' => static function () {
						return true;
					},
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function ( $fields ) {
						$to_return = Happypoint::get_data( $fields['id'] );
						return rest_ensure_response( $to_return );
					},
				],
			]
		);

		/**
		 * Endpoint to retrieve the images for the Gallery block
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/get-gallery-images',
			[
				[
					'permission_callback' => static function () {
						return true;
					},
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function ( $fields ) {
						$images = Gallery::get_images( $fields );
						return rest_ensure_response( $images );
					},
				],
			]
		);

		/**
		 * Endpoint to retrieve the covers for the Covers block
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/get-covers',
			[
				[
					'permission_callback' => static function () {
						return true;
					},
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function ( $fields ) {
						$covers = Covers::get_covers( $fields );
						return rest_ensure_response( $covers );
					},
				],
			]
		);

		/**
		 * Endpoint to get the code for Instagram embeds in the Social Media block.
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/get-instagram-embed',
			[
				[
					'permission_callback' => static function () {
						return true;
					},
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function ( $fields ) {
						$url        = $fields['url'] ?? '';
						$embed_code = SocialMedia::get_fb_oembed_html( $url, 'instagram' );
						return rest_ensure_response( $embed_code );
					},
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'add-theme',
			[
				[
					'permission_callback' => static function () {
						return is_user_logged_in();
					},
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => static function ( WP_REST_Request $request ) {
						$payload = $request->get_json_params();

						$current_themes = json_decode( get_option( 'planet4_themes' ), true ) ?? [];

						$current_themes[ $payload['name'] ] = $payload['theme'];
						update_option( 'planet4_themes', wp_json_encode( $current_themes ) );

						return new \WP_REST_Response( 'Theme added', 200 );
					},
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'delete-theme',
			[
				[
					'permission_callback' => static function () {
						return is_user_logged_in();
					},
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => static function ( WP_REST_Request $request ) {
						$payload = $request->get_json_params();

						$current_themes = json_decode( get_option( 'planet4_themes' ), true ) ?? [];
						unset( $current_themes[ $payload['name'] ] );
						update_option( 'planet4_themes', wp_json_encode( $current_themes ) );

						return new \WP_REST_Response( 'Theme deleted', 200 );
					},
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'themes',
			[
				[
					'permission_callback' => static function () {
						return true;
					},
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function ( WP_REST_Request $request ) {
						return new \WP_REST_Response( json_decode( get_option( 'planet4_themes', '[]' ) ), 200 );
					},
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/get-en-session-token',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function () {
						$token = ENForm::get_session_token();
						return rest_ensure_response( [ 'token' => $token ] );
					},
					'permission_callback' => static function () {
						return true;
					},
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/enform/(?P<en_page_id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => static function ( WP_REST_Request $request ) {
						return self::send_enform( $request );
					},
					'permission_callback' => static function () {
						return true;
					},
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/analytics-values',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => static function () {
						return current_user_can( 'edit_posts' );
					},
					'callback'            => static function ( $request ) {
						$analytics_values = AnalyticsValues::from_cache_or_api_or_hardcoded();

						$global_options = $analytics_values->global_projects_options();
						$local_options  = $analytics_values->local_projects_options();
						$basket_options = $analytics_values->basket_options();

						return rest_ensure_response(
							[
								[
									'global_projects' => array_map(
										fn ( $k, $v ) => [
											'label' => $v,
											'value' => $k,
										],
										array_keys( $global_options ),
										array_values( $global_options )
									),
									'local_projects'  => array_map(
										fn ( $k, $v ) => [
											'label' => $v,
											'value' => $k,
										],
										array_keys( $local_options ),
										array_values( $local_options )
									),
									'baskets'         => array_map(
										fn ( $k, $v ) => [
											'label' => $v,
											'value' => $k,
										],
										array_keys( $basket_options ),
										array_values( $basket_options )
									),
								],
							]
						);
					},
				],
			],
		);
	}

	/**
	 * Send form to EN instance.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	private static function send_enform( WP_REST_Request $request ) {
		$form       = $request->get_json_params();
		$token      = ENForm::get_session_token();
		$en_page_id = (int) $request['en_page_id'] ?? null;
		if ( ! $en_page_id ) {
			self::log_message( 'Invalid EN page ID', [ 'page_id' => $en_page_id ] );
			return new WP_Error(
				'no_en_page_id',
				'Invalid EN page ID',
				[ 'status' => 404 ]
			);
		}

		$form     = apply_filters( 'planet4_enform_data', $form, $en_page_id );
		$request  = [
			'url'  => 'https://e-activist.com/ens/service/page/' . $en_page_id . '/process',
			'args' => [
				'headers' => [
					'content-type'   => 'application/json',
					'ens-auth-token' => $token,
				],
				'body'    => wp_json_encode( $form ),
			],
		];
		$response = wp_remote_post( $request['url'], $request['args'] );

		if ( is_wp_error( $response ) ) {
			self::log_message(
				'Error submitting EN form',
				[
					'en_api_request' => $request,
					'wp_error'       => $response->get_all_error_data(),
				]
			);

			return $response;
		}

		$response_code = $response['response']['code'] ?? 0;
		if ( 200 !== $response_code ) {
			self::log_message(
				'Error submitting EN form',
				[
					'en_api_request'  => $request,
					'en_api_response' => $response ?? [],
				]
			);

			return new WP_Error(
				'submit_error',
				'Error submitting EN form',
				[
					'status'   => $response['response']['code'],
					'response' => $response['response'],
				]
			);
		}

		return rest_ensure_response( [] );
	}

	/**
	 * Log API response to Sentry.
	 *
	 * @param string $message Message.
	 * @param array  $data    Data to log.
	 */
	private static function log_message( string $message, array $data = [] ): void {
		if ( ! function_exists( '\\Sentry\\withScope' ) ) {
			return;
		}

		\Sentry\withScope(
			function ( \Sentry\State\Scope $scope ) use ( $message, $data ): void {
				foreach ( $data as $key => $val ) {
					$scope->setContext( $key, $val );
				}
				\Sentry\captureMessage( $message );
			}
		);
	}
}
