<?php
/**
 * ENForm block class
 *
 * @package P4GEN
 */

namespace P4GEN\Blocks;

use P4GEN\Controllers\Menu\Enform_Post_Controller;

/**
 * Class ENForm
 *
 * @package P4GEN\Blocks
 */
class ENForm extends Base_Block {

	/** @const string BLOCK_NAME */
	const BLOCK_NAME = 'enform';

	/**
	 * Register old shortcode for backwarsd compatibility.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @param array $content The content of the post.
	 */
	public function add_block_shortcode( $attributes, $content ) {
		$attributes = shortcode_atts(
			[
				'en_page_id'    			         => '',
				'enform_goal'    			         => '',
				'enform_style'    			       => '',
				'enform_style'    			       => '',
				'title'                        => '',
				'description'                  => '',
				'content_title'                => '',
				'content_description'          => '',
				'button_text'                  => '',
				'text_below_button'            => '',
				'thankyou_title'               => '',
				'thankyou_subtitle'            => '',
				'thankyou_donate_message'      => '',
				'thankyou_take_action_message' => '',
				'thankyou_url'                 => '',
				'background'                   => '',
				'en_form_id'                   => '',
			],
			$attributes,
			'shortcake_enform'
		);

		return $this->render( $attributes );
	}

	/**
	 * Cookies constructor.
	 */
	public function __construct() {
		//add_shortcode( 'shortcake_enform', [ $this, 'add_block_shortcode' ] );

		// - Register the block for the editor
		// in the PHP side.
		register_block_type(
			'planet4-gutenberg-engagingnetworks/enform',
			[
				'editor_script'   => 'planet4-gutenberg-engagingnetworks/enform',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'en_page_id'    			         => [
						'type'    => 'integer',
						'default' => 0,
					],
					'enform_goal'    			         => [
						'type'    => 'string',
						'default' => '',
					],
					'en_form_style'    			       => [
						'type'    => 'string',
						'default' => 'fullwidth',
					],
					'enform_style'    			       => [
						'type'    => 'string',
						'default' => '',
					],
					'title'                        => [
						'type'    => 'string',
						'default' => '',
					],
					'description'                  => [
						'type'    => 'string',
						'default' => '',
					],
					'content_title'                => [
						'type'    => 'string',
						'default' => '',
					],
					'content_description'          => [
						'type'    => 'string',
						'default' => '',
					],
					'button_text'                  => [
						'type'    => 'string',
						'default' => '',
					],
					'text_below_button'            => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_title'               => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_subtitle'            => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_donate_message'      => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_take_action_message' => [
						'type'    => 'string',
						'default' => '',
					],
					'thankyou_url'                 => [
						'type'    => 'string',
						'default' => '',
					],
					'background'                   => [
						'type'    => 'string',
						'default' => '',
					],
					'en_form_id'                   => [
						'type'    => 'integer',
						'default' => 0,
					],
				],
			]
		);
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {

		// Enqueue js for the frontend.
		//if ( ! $this->is_rest_request() ) {
			//wp_enqueue_script( 'enform', P4GEN_PLUGIN_URL . 'public/js/enform.js', [ 'jquery' ], '0.1', true );
		//}

		//$fields = $this->ignore_unused_attributes( $fields );

		// Handle background image.
		if ( isset( $attributes['background'] ) ) {
			$options                     = get_option( 'planet4_options' );
			$p4_happy_point_bg_image     = $options['happy_point_bg_image_id'] ?? '';
			$image_id                    = '' !== $attributes['background'] ? $attributes['background'] : $p4_happy_point_bg_image;
			$img_meta                    = wp_get_attachment_metadata( $image_id );
			$attributes['background_src']    = wp_get_attachment_image_src( $image_id, 'retina-large' );
			$attributes['background_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta );
			$attributes['background_sizes']  = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
		}
		$attributes['default_image'] = get_bloginfo( 'template_directory' ) . '/images/happy-point-block-bg.jpg';

		$data = [];

		if ( isset( $attributes['thankyou_url'] ) && 0 !== strpos( $attributes['thankyou_url'], 'http' ) ) {
			$attributes['thankyou_url'] = 'http://' . $attributes['thankyou_url'];
		} else {
			$options              = get_option( 'planet4_options' );
			$attributes['donatelink'] = $options['donate_button'] ?? '#';
		}

		$data = array_merge(
			$data,
			[
				'fields'       => $attributes,
				'redirect_url' => isset( $attributes['thankyou_url'] ) ? filter_var( $attributes['thankyou_url'], FILTER_VALIDATE_URL ) : '',
				'nonce_action' => 'enform_submit',
				'form'         => '[' . Enform_Post_Controller::POST_TYPE . ' id="' . $attributes['en_form_id'] . '" en_form_style="' . $attributes['en_form_style'] . '" /]',
			]
		);

		return $data;
	}
}
