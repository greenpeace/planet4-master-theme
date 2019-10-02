<?php
/**
 * ENForm block class
 *
 * @package P4GEN
 */

namespace P4GEN\Blocks;

use P4GEN\Controllers\Menu\Enform_Post_Controller;
use P4GEN\Views\View;

/**
 * Class ENForm
 *
 * @package P4GEN\Blocks
 */
class ENForm extends Base_Block {

	/** @const string BLOCK_NAME */
	const BLOCK_NAME = 'enform';

	/**
	 * Page types for EN forms
	 *
	 * @const array ENFORM_PAGE_TYPES
	 */
	const ENFORM_PAGE_TYPES = [ 'PET', 'EMS' ];

	/**
	 * Custom meta field where fields configuration is saved to.
	 */
	const FIELDS_META = 'p4enform_fields';

	/**
	 * ENSAPI Object
	 *
	 * @var Ensapi $ensapi
	 */
	private $ens_api = null;

	/**
	 * Class Loader reference
	 *
	 * @var \P4GEN\Loader $loader;
	 */
	private $loader;

	/**
	 * Cookies constructor.
	 */
	public function __construct( $loader ) {
		$this->loader = $loader;
		//add_shortcode( 'shortcake_enform', [ $this, 'add_block_shortcode' ] );
		// Variables exposed from PHP to JS,
		// WP calls this "localizing a script"...

		// - Register the block for the editor
		// in the PHP side.
		register_block_type(
			'planet4-blocks/enform',
			[
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
						'type'    => 'integer',
						'default' => 0,
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
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {
		global $pagenow;

		// Enqueue js for the frontend.
		if ( ! $this->is_rest_request() ) {
			$this->loader->enqueue_public_assets();
		}

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

		$view = new View();
		$post_id = $attributes['en_form_id'];

		if ( $post_id ) {
		// if ( ! is_admin() ||
		// 	( 'post.php' === $pagenow && $post_id && self::POST_TYPE === get_post_type( $post_id ) ) ||
		// 	( 'admin-ajax.php' === $pagenow && self::POST_TYPE === get_post_type( $attributes['id'] ) ) ) {

			$fields = get_post_meta( $post_id, self::FIELDS_META, true );

			$data = [
				'form_fields'   => $fields,
				'en_form_style' => $attributes['en_form_style'],
			];

			$renderedForm = $view->view_template('enform_post', $data, '/blocks/', true);
		} else {
			$renderedForm = '';
		}

		$data = array_merge(
			$data,
			[
				'fields'       => $attributes,
				'redirect_url' => isset( $attributes['thankyou_url'] ) ? filter_var( $attributes['thankyou_url'], FILTER_VALIDATE_URL ) : '',
				'nonce_action' => 'enform_submit',
				'form'         => $renderedForm,
			]
		);

		return $data;
	}
}
