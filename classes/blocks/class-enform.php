<?php
/**
 * ENForm block class
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

use P4GBKS\Controllers\Ensapi_Controller as Ensapi;
use WP_Block_Type_Registry;

/**
 * Class ENForm
 *
 * @package P4GBKS\Blocks
 */
class ENForm extends Base_Block {
	/**
	 * @const string BLOCK_NAME
	 */
	protected const BLOCK_NAME = 'enform';

	/**
	 * Page types for EN forms.
	 *
	 * @const array ENFORM_PAGE_TYPES
	 */
	public const ENFORM_PAGE_TYPES = [ 'PET', 'EMS' ];

	/**
	 * Custom meta field where fields configuration is saved to.
	 */
	private const FIELDS_META = 'p4enform_fields';

	/**
	 * Block attributes.
	 *
	 * @var array $attributes Block attributes definition.
	 */
	private static $attributes = [
		'en_page_id'                    => [ 'type' => 'integer' ],
		'enform_goal'                   => [ 'type' => 'string' ],
		'en_form_style'                 => [ 'type' => 'string' ],
		'enform_style'                  => [ 'type' => 'string' ],
		'title'                         => [ 'type' => 'string' ],
		'description'                   => [ 'type' => 'string' ],
		'campaign_logo'                 => [ 'type' => 'boolean' ],
		'content_title'                 => [ 'type' => 'string' ],
		'content_title_size'            => [
			'type'    => 'string',
			'default' => 'h1',
		],
		'content_description'           => [ 'type' => 'string' ],
		'button_text'                   => [ 'type' => 'string' ],
		'text_below_button'             => [ 'type' => 'string' ],
		'thankyou_title'                => [ 'type' => 'string' ],
		'thankyou_subtitle'             => [ 'type' => 'string' ],
		'thankyou_donate_message'       => [ 'type' => 'string' ],
		'thankyou_social_media_message' => [ 'type' => 'string' ],
		'donate_button_checkbox'        => [ 'type' => 'boolean' ],
		'thankyou_url'                  => [ 'type' => 'string' ],
		'custom_donate_url'             => [ 'type' => 'string' ],
		'background'                    => [ 'type' => 'integer' ],
		'en_form_id'                    => [ 'type' => 'integer' ],
		'en_form_fields'                => [ 'type' => 'array' ],
	];

	/**
	 * ENForm constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_enform_block' ] );
	}

	/**
	 * Register block.
	 */
	public function register_enform_block() {
		$name = self::get_full_block_name() . '-beta';
		if ( WP_Block_Type_Registry::get_instance()->is_registered( $name ) ) {
			return;
		}

		// Registering meta field to make it appear in REST API.
		\register_post_meta(
			'p4en_form',
			self::FIELDS_META,
			[
				'type'         => 'object',
				'properties'   => [ 'id' => [ 'type' => 'integer' ] ],
				'show_in_rest' => true,
				'single'       => true,
			]
		);

		\register_block_type(
			self::get_full_block_name() . '-beta',
			[
				'attributes'      => static::$attributes,
				'render_callback' => function ( $attributes ) {
					$json = \wp_json_encode(
						[ 'attributes' => $this->update_data( $attributes ) ]
					);
					if ( empty( $json ) ) {
						$json = [];
					}

					if ( ! $this->is_rest_request() ) {
						$json = htmlspecialchars( $json );
					}

					return '<div
						data-render="' . self::get_full_block_name() . '"
						data-attributes="' . $json . '">
					</div>';
				},
			]
		);

		add_action( 'wp_ajax_get_en_session_token', [ self::class, 'get_session_token' ] );
		add_action( 'wp_ajax_nopriv_get_en_session_token', [ self::class, 'get_session_token' ] );

		add_action( 'enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ] );
	}

	/**
	 * @param array $attributes Block attributes.
	 *
	 * @return array
	 */
	public static function update_data( array $attributes ): array {
		$post_id = (int) ($attributes['en_form_id'] ?? 0);
		$post    = get_post( $post_id );

		if ( empty( $attributes['en_form_fields'] ) && $post_id ) {
			$attributes['en_form_fields'] = get_post_meta( $post_id, self::FIELDS_META, true );
		}

		if ( isset( $attributes['background'] ) && empty( $attributes['background_src'] ) ) {
			$attributes = array_merge( $attributes, self::get_background_data( $attributes ) );
		}

		$attributes['social_accounts'] = self::get_social_accounts();
		$attributes['social']          = self::get_shareable_data( $post->ID );

		return $attributes;
	}

	/**
	 * Return camelized version of block name.
	 */
	public static function get_camelized_block_name() {
		return 'ENForm';
	}

	/**
	 * Load assets for the EN block frontend.
	 */
	public static function enqueue_frontend_assets() {
		parent::enqueue_frontend_assets();

		wp_localize_script(
			'plugin-engagingnetworks',
			'p4_vars',
			[ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ]
		);

		wp_localize_script(
			'engagingnetworks-submit',
			'en_vars',
			[ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ]
		);
	}

	/**
	 * Additional background image data.
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return array
	 */
	private static function get_background_data( array $attributes ): array {
		$image_id = empty( $attributes['background'] ) ? 0 : $attributes['background'];
		if ( empty( $image_id ) ) {
			$opts     = get_option( 'planet4_options' );
			$image_id = empty( $opts['happy_point_bg_image_id'] ) ? 0 : $opts['happy_point_bg_image_id'];
		}
		$img_meta = wp_get_attachment_metadata( $image_id );

		$attributes['background_src']    = wp_get_attachment_image_src( $image_id, 'retina-large' );
		$attributes['background_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta );
		$attributes['background_sizes']  = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );

		return [
			'background_src'    => wp_get_attachment_image_src( $image_id, 'retina-large' ),
			'background_srcset' => wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta ),
			'background_sizes'  => wp_calculate_image_sizes( 'retina-large', null, null, $image_id ),
		];
	}

	/**
	 * Get post data to share via social sharing functionalities.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array
	 */
	private static function get_shareable_data( int $post_id ): array {
		$og_title       = '';
		$og_description = '';
		$link           = '';
		if ( $post_id > 0 ) {
			$og_title = get_post_meta( $post_id, 'p4_og_title', true );
			if ( empty( $og_title ) ) {
				$og_title = get_the_title( $post_id );
			}
			$og_description = get_post_meta( $post_id, 'p4_og_description', true );
			$link           = get_permalink( $post_id );
		}

		return [
			'title'       => esc_attr( $og_title ),
			'description' => esc_attr( wp_strip_all_tags( $og_description ) ),
			'link'        => esc_url( $link ),
		];
	}

	/**
	 * Social accounts.
	 *
	 * @return array List of social accounts.
	 */
	private static function get_social_accounts(): array {
		$social_accounts = [];
		$social_menu     = wp_get_nav_menu_items( 'Footer Social' );

		if ( ! isset( $social_menu ) || ! is_iterable( $social_menu ) ) {
			return $social_accounts;
		}

		foreach ( $social_menu as $social_menu_item ) {
			$url_parts = explode( '/', rtrim( $social_menu_item->url, '/' ) );
			if ( false !== strpos( $social_menu_item->url, 'twitter' ) ) {
				$social_accounts['twitter'] = count( $url_parts ) > 0 ? $url_parts[ count( $url_parts ) - 1 ] : '';
			}
		}

		return $social_accounts;
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {
		return [];
	}

	/**
	 * Get en session token for frontend api calls.
	 */
	public static function get_session_token() {
		$main_settings     = get_option( 'p4en_main_settings' );
		$ens_private_token = $main_settings['p4en_frontend_private_api'];
		$ens_api           = new Ensapi( $ens_private_token, false );

		return $ens_api->get_public_session_token();
	}
}
