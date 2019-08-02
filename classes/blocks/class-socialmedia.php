<?php
/**
 * Social Media block class.
 *
 * @package P4GBKS\Blocks
 */

namespace P4GBKS\Blocks;

/**
 * Class Socialmedia
 *
 * @package P4GBKS\Blocks
 */
class SocialMedia extends Base_Block {

	/** @const string BLOCK_NAME */
	const BLOCK_NAME = 'social_media';

	const ALLOWED_OEMBED_PROVIDERS = [
		'twitter',
		'facebook',
		'instagram',
	];

	/**
	 * Register shortcake shortcode.
	 *
	 * @param array  $attributes Shortcode attributes.
	 * @param string $content   Content.
	 *
	 * @return mixed
	 */
	public function add_block_shortcode( $attributes, $content ) {
		$attributes = shortcode_atts(
			[
				'title'             => '',
				'description'       => '',
				'embed_type'        => '',
				'facebook_page_tab' => '',
				'social_media_url'  => '',
				'alignment_class'   => '',
			],
			$attributes,
			'shortcake_social_media'
		);

		return $this->render( $attributes );
	}

	/**
	 * SocialMedia constructor.
	 */
	public function __construct() {
		add_shortcode( 'shortcake_social_media', [ $this, 'add_block_shortcode' ] );

		// - Register the block for the editor
		register_block_type(
			'planet4-blocks/social-media',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],

				// These attributes match the current fields.
				'attributes'      => [
					'title'             => [
						'type'    => 'string',
						'default' => '',
					],
					'description'       => [
						'type'    => 'string',
						'default' => '',
					],
					'embed_type'        => [
						'type'    => 'string',
						'default' => 'oembed',
					],
					'facebook_page_tab' => [
						'type'    => 'string',
						'default' => 'timeline',
					],
					'social_media_url'  => [
						'type'    => 'string',
						'default' => '',
					],
					'alignment_class'   => [
						'type'    => 'string',
						'default' => '',
					],
				],
			]
		);
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $fields ): array {
		$title             = $fields['title'] ?? '';
		$description       = $fields['description'] ?? '';
		$url               = $fields['social_media_url'] ?? '';
		$embed_type        = $fields['embed_type'];
		$alignment_class   = $fields['alignment_class'];
		$facebook_page_tab = $fields['facebook_page_tab'];

		$data = [
			'title'           => $title,
			'description'     => $description,
			'alignment_class' => $alignment_class,
		];

		if ( $url ) {
			if ( 'oembed' === $embed_type ) {
				// need to remove . so instagr.am becomes instagram.
				$provider = preg_replace( '#(^www\.)|(\.com$)|(\.)#', '', strtolower( wp_parse_url( $url, PHP_URL_HOST ) ) );

				// Fix for backend preview, do not include embed script in response.
				if ( $this->is_rest_request() && 'twitter' === $provider ) {
					$url = add_query_arg( [ 'omit_script' => true ], $url );
				}
				if ( in_array( $provider, self::ALLOWED_OEMBED_PROVIDERS, true ) ) {
					$data['embed_code'] = wp_oembed_get( $url );
				}
			} elseif ( 'facebook_page' === $embed_type ) {
				$data['facebook_page_url'] = $url;
				$data['facebook_page_tab'] = $facebook_page_tab;
			}
		}

		return $data;
	}
}
