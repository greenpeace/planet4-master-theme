<?php
/**
 * OldENForm block class
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

use P4GBKS\Views\View;
use P4GBKS\Controllers\Ensapi_Controller as Ensapi;
use WP_Block_Type_Registry;

/**
 * Class OldENForm
 *
 * @package P4GBKS\Blocks
 */
class OldENForm extends Base_Block {

	/** @const string BLOCK_NAME */
	protected const BLOCK_NAME = 'enform';

	/**
	 * Page types for EN forms
	 *
	 * @const array ENFORM_PAGE_TYPES
	 */
	public const ENFORM_PAGE_TYPES = [ 'PET', 'EMS' ];

	/**
	 * Custom meta field where fields configuration is saved to.
	 */
	private const FIELDS_META = 'p4enform_fields';

	/**
	 * OldENForm constructor.
	 */
	public function __construct() {
		if ( WP_Block_Type_Registry::get_instance()->is_registered( self::get_full_block_name() ) ) {
			return;
		}

		add_shortcode( 'shortcake_enblock', [ $this, 'add_block_shortcode' ] );
		register_block_type(
			self::get_full_block_name(),
			[
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'en_page_id'                    => [
						'type'    => 'integer',
						'default' => 0,
					],
					'enform_goal'                   => [
						'type'    => 'string',
						'default' => '',
					],
					'en_form_style'                 => [
						'type'    => 'string',
						'default' => 'fullwidth',
					],
					'enform_style'                  => [
						'type'    => 'string',
						'default' => '',
					],
					'title'                         => [
						'type'    => 'string',
						'default' => '',
					],
					'description'                   => [
						'type'    => 'string',
						'default' => '',
					],
					'campaign_logo'                 => [
						'type' => 'boolean',
					],
					'content_title'                 => [
						'type'    => 'string',
						'default' => '',
					],
					'content_title_size'            => [
						'type'    => 'string',
						'default' => 'h1',
					],
					'content_description'           => [
						'type'    => 'string',
						'default' => '',
					],
					'button_text'                   => [
						'type'    => 'string',
						'default' => '',
					],
					'text_below_button'             => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_title'                => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_subtitle'             => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_donate_message'       => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_social_media_message' => [
						'type'    => 'string',
						'default' => '',
					],
					'donate_button_checkbox'        => [
						'type' => 'boolean',
					],
					'thankyou_url'                  => [
						'type'    => 'string',
						'default' => '',
					],
					'custom_donate_url'             => [
						'type'    => 'string',
						'default' => '',
					],
					'background'                    => [
						'type'    => 'integer',
						'default' => 0,
					],
					'en_form_id'                    => [
						'type'    => 'integer',
						'default' => 0,
					],
				],
			]
		);

		add_action( 'wp_ajax_get_en_session_token', [ $this, 'get_session_token' ] );
		add_action( 'wp_ajax_nopriv_get_en_session_token', [ $this, 'get_session_token' ] );
	}


	/**
	 * Register old shortcode for backward compatibility.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @param array $content The content of the post.
	 */
	public function add_block_shortcode( $attributes, $content ) {
		$attributes = shortcode_atts(
			[
				'en_page_id'                    => '',
				'enform_goal'                   => '',
				'enform_style'                  => '',
				'title'                         => '',
				'description'                   => '',
				'content_title'                 => '',
				'content_description'           => '',
				'button_text'                   => '',
				'text_below_button'             => '',
				'thankyou_title'                => '',
				'thankyou_subtitle'             => '',
				'thankyou_social_media_message' => '',
				'thankyou_donate_message'       => '',
				'donate_button_checkbox'        => '',
				'thankyou_take_action_message'  => '',
				'thankyou_url'                  => '',
				'background'                    => '',
				'en_form_id'                    => '',
			],
			$attributes,
			'shortcake_enform'
		);

		return $this->render( $attributes );
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {

		global $post;

		// Extract twitter account from footer.
		$social_menu = wp_get_nav_menu_items( 'Footer Social' );
		if ( isset( $social_menu ) && is_iterable( $social_menu ) ) {
			foreach ( $social_menu as $social_menu_item ) {
				$url_parts = explode( '/', rtrim( $social_menu_item->url, '/' ) );
				if ( false !== strpos( $social_menu_item->url, 'twitter' ) ) {
					$social_accounts['twitter'] = count( $url_parts ) > 0 ? $url_parts[ count( $url_parts ) - 1 ] : '';
				}
			}
		}

		// Handle background image.
		if ( isset( $attributes['background'] ) ) {
			$options                         = get_option( 'planet4_options' );
			$p4_happy_point_bg_image         = $options['happy_point_bg_image_id'] ?? '';
			$image_id                        = '' !== $attributes['background'] ? $attributes['background'] : $p4_happy_point_bg_image;
			$img_meta                        = wp_get_attachment_metadata( $image_id );
			$attributes['background_src']    = wp_get_attachment_image_src( $image_id, 'retina-large' );
			$attributes['background_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta );
			$attributes['background_sizes']  = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
		}
		$attributes['default_image'] = get_bloginfo( 'template_directory' ) . '/images/happy-point-block-bg.jpg';

		if ( is_object( $post ) ) {
			$og_title       = get_post_meta( $post->ID, 'p4_og_title', true );
			$og_description = get_post_meta( $post->ID, 'p4_og_description', true );
			$link           = get_permalink( $post->ID );
		} else {
			$og_title       = '';
			$og_description = '';
			$link           = '';
		}

		if ( '' === $og_title && is_object( $post ) ) {
			$title = get_the_title( $post->ID );
			if ( '' !== $title ) {
				$og_title = $title;
			}
		}
		$social = [
			'title'       => esc_attr( $og_title ),
			'description' => esc_attr( wp_strip_all_tags( $og_description ) ),
			'link'        => esc_url( $link ),
		];

		$data = [];

		if ( isset( $attributes['thankyou_url'] ) && $attributes['thankyou_url'] && 0 !== strpos( $attributes['thankyou_url'], 'http' ) ) {
			$attributes['thankyou_url'] = 'https://' . $attributes['thankyou_url'];
		} else {
			$options                   = get_option( 'planet4_options' );
			$attributes['donate_text'] = $options['donate_text'] ?? __( 'Donate', 'planet4-engagingnetworks' );
			if ( isset( $attributes['custom_donate_url'] ) && $attributes['custom_donate_url'] ) {
				// Check if url start with http/https or not.
				$attributes['donatelink'] = ( 0 !== strpos( $attributes['custom_donate_url'], 'http' ) ) ? 'https://' . $attributes['custom_donate_url'] : $attributes['custom_donate_url'];
			} else {
				$attributes['donatelink'] = $options['donate_button'] ?? '#';
			}

			$donate_button_checkbox = 'false';
			if ( isset( $attributes['donate_button_checkbox'] ) && $attributes['donate_button_checkbox'] ) {
				$donate_button_checkbox = 'true';
			}
			$attributes['donate_button_checkbox'] = $donate_button_checkbox;
		}

		$attributes['content_title_size'] = $attributes['content_title_size'] ?? 'h1';

		$campaign_data = [];

		if ( 'campaign' === get_post_type() ) {
			$page_meta_data    = get_post_meta( $post->ID );
			$campaign_template = $page_meta_data['theme']
				?? ! empty( $page_meta_data['_campaign_page_template'][0] )
					? $page_meta_data['_campaign_page_template'][0] ?? null
					: false;
			$campaign_data     = [
				'template' => $campaign_template,
			];
			if ( isset( $attributes['campaign_logo'] ) ) {
				if ( 'true' === $attributes['campaign_logo'] && $campaign_template ) {
					$campaign_logo_path         = get_bloginfo( 'template_directory' ) . '/images/' . $campaign_template . '/logo-light.png';
					$campaign_data['logo_path'] = $campaign_logo_path;
					$campaign_data['logo']      = $attributes['campaign_logo'];
				}
			}
		}

		$view    = new View();
		$post_id = $attributes['en_form_id'];

		if ( $post_id ) {
			$fields = get_post_meta( $post_id, self::FIELDS_META, true );

			$data = [
				'form_fields'   => $fields,
				'en_form_style' => $attributes['en_form_style'] ?? 'full-width',
			];

			$rendered_form = $view->view_template( 'enform_post', $data, '/blocks/enform/', true );
		} else {
			$rendered_form = '';
		}

		$data = array_merge(
			$data,
			[
				'fields'          => $attributes,
				'redirect_url'    => isset( $attributes['thankyou_url'] ) ? filter_var( $attributes['thankyou_url'], FILTER_VALIDATE_URL ) : '',
				'form'            => $rendered_form,
				'social'          => $social,
				'social_accounts' => $social_accounts ?? null,
				'campaign_data'   => $campaign_data,
			]
		);

		// Enqueue js for the frontend.
		if ( ! $this->is_rest_request() ) {
			$this->enqueue_public_assets();
		}

		return $data;
	}

	/**
	 * Get en session token for frontend api calls.
	 */
	public function get_session_token() {
		// If this is an ajax call.
		if ( wp_doing_ajax() ) {

			$response          = [];
			$main_settings     = get_option( 'p4en_main_settings' );
			$ens_private_token = $main_settings['p4en_frontend_private_api'];
			$ens_api           = new Ensapi( $ens_private_token, false );
			$token             = $ens_api->get_public_session_token();
			$response['token'] = $token;

			wp_send_json( $response );
		}
	}

	/**
	 * Load assets for the EN block frontend.
	 */
	public function enqueue_public_assets() {
		// EN-blocks assets.
		\P4GBKS\Loader::enqueue_local_script(
			'engagingnetworks-submit',
			'/public/js/enform_submit.js',
			[
				'jquery',
				'main',
			]
		);

		\P4GBKS\Loader::enqueue_local_script(
			'engagingnetworks-dependency',
			'/public/js/enform_dependency.js',
			[
				'jquery',
				'main',
			]
		);

		\P4GBKS\Loader::enqueue_local_script(
			'engagingnetworks-side-style',
			'/public/js/enform_side_style.js',
			[
				'jquery',
				'main',
			]
		);

		wp_localize_script(
			'plugin-engagingnetworks',
			'p4_vars',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			]
		);

		wp_localize_script(
			'engagingnetworks-submit',
			'en_vars',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			]
		);
	}
}
