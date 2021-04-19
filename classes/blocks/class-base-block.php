<?php
/**
 * Base block class.
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

/**
 * Class Base_Block
 *
 * @package P4GBKS\Blocks
 */
abstract class Base_Block {

	protected const CSS_VARIABLES_ATTRIBUTE = [
		'type'    => 'object',
		'default' => [],
	];

	public const NAMESPACE = 'planet4-blocks';

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	abstract public function prepare_data( $fields ): array;

	/**
	 * @param array $attributes Block attributes.
	 *
	 * @return mixed
	 */
	public function render( $attributes ) {

		$data = $this->prepare_data( $attributes );

		\Timber::$locations = P4GBKS_PLUGIN_DIR . '/templates/blocks';

		$block_output = \Timber::compile( static::BLOCK_NAME . '.twig', $data );

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		$empty_message = defined( 'static::EMPTY_MESSAGE' ) ? __( static::EMPTY_MESSAGE, 'planet4-blocks' ) : "Block content is empty. Check the block's settings or remove it.";

		// Return empty string if rendered output contains only whitespace or new lines.
		// If it is a rest request from editor/admin area, return a message that block has no content.
		$empty_content = $this->is_rest_request() ? '<div class="EmptyMessage">' . $empty_message . '</div>' : '';

		return ctype_space( $block_output ) ? $empty_content : $block_output;
	}

	/**
	 * Outputs an error message.
	 *
	 * @param string $message Error message.
	 */
	public function render_error_message( $message ) {
		// Ensure only editors see the error, not visitors to the website.
		if ( current_user_can( 'edit_posts' ) ) {
			\Timber::render(
				P4GBKS_PLUGIN_DIR . 'templates/block-error-message.twig',
				[
					'category' => __( 'Error', 'planet4-blocks' ),
					'message'  => $message,
				]
			);
		}
	}

	/**
	 * Returns if current request is a rest api request.
	 *
	 * @return bool
	 */
	protected function is_rest_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}
		return false;
	}

	/**
	 * Update the attributes of a block to the latest version.
	 * It returns an array with the new version of the block attributes.
	 * PHPCS does not allow me to add the return type if there is no return statement, but here we always throw an
	 * exception, so adding a return after triggers another CS rule. Disabling the violated rule,
	 * Squiz.Commenting.FunctionComment.InvalidNoReturn, is not working in the doc comment.
	 *
	 * @param array $fields The old version of the block attributes.
	 * @throws NotImplemented If no implementation is given by the subclass.
	 */
	public static function update_data( array $fields ) {
		throw new NotImplemented( 'Method update_data is not implemented for ' . static::class );
	}

	/**
	 * Register scripts and styles for a block
	 */
	public static function enqueue_editor_assets() {
		static::enqueue_editor_script();
		static::enqueue_editor_style();
		static::enqueue_frontend_style();
	}

	/**
	 * Enqueue assets for the frontend IF the blocks is present.
	 */
	public static function enqueue_frontend_assets() {
		$full_name   = static::get_full_block_name();
		$to_look_for = $full_name;
		if ( 'planet4-blocks/covers' === $full_name ) {
			$to_look_for = 'planet4-blocks/covers-beta';
		} elseif ( 'planet4-blocks/carousel-header' === $full_name ) {
			$to_look_for = 'planet4-blocks/carousel-header-beta';
		}

		if ( ! has_block( $to_look_for ) ) {
			return;
		}

		static::enqueue_frontend_script();
		static::enqueue_frontend_style();
	}

	/**
	 * Editor script
	 */
	public static function enqueue_editor_script(): void {
		wp_enqueue_script(
			static::get_full_block_name() . '-editor-script',
			static::get_url_path() . 'EditorScript.js',
			'planet4-blocks-editor-script',
			\P4GBKS\Loader::file_ver( static::get_dir_path() . 'EditorScript.js' ),
			true
		);
	}

	/**
	 * Editor style
	 */
	public static function enqueue_editor_style(): void {
		wp_enqueue_style(
			static::get_full_block_name() . '-editor-style',
			static::get_url_path() . 'EditorStyle.min.css',
			[],
			\P4GBKS\Loader::file_ver( static::get_dir_path() . 'EditorStyle.min.css' ),
		);
	}

	/**
	 * Frontend script
	 */
	public static function enqueue_frontend_script(): void {
		wp_enqueue_script(
			static::get_full_block_name() . '-script',
			static::get_url_path() . 'Script.js',
			'planet4-blocks-script',
			\P4GBKS\Loader::file_ver( static::get_dir_path() . 'Script.js' ),
			true
		);
	}

	/**
	 * Frontend style
	 */
	public static function enqueue_frontend_style(): void {
		wp_enqueue_style(
			static::get_full_block_name() . '-style',
			static::get_url_path() . 'Style.min.css',
			[],
			\P4GBKS\Loader::file_ver( static::get_dir_path() . 'Style.min.css' ),
		);
	}

	/**
	 * Converts the hy-phe-na-ted block name into CamelCase.
	 */
	public static function get_camelized_block_name() {
		return str_replace( '-', '', ucwords( static::BLOCK_NAME, '-' ) );
	}

	/**
	 * Returns the block name with its namespace prefix, e.g.: planet4-blocks/accordion.
	 */
	public static function get_full_block_name() {
		return static::NAMESPACE . '/' . static::BLOCK_NAME;
	}

	/**
	 * Return URL path to plugin assets
	 */
	public static function get_url_path(): string {
		return trailingslashit( P4GBKS_PLUGIN_URL ) . 'assets/build/' . static::get_camelized_block_name();
	}

	/**
	 * Return directory path to plugin assets
	 */
	public static function get_dir_path(): string {
		return trailingslashit( P4GBKS_PLUGIN_DIR ) . 'assets/build/' . static::get_camelized_block_name();
	}

	/**
	 * Renders a `div` with the block's attributes in a `data-` attribute to be handled in the frontend.
	 *
	 * @param array $attributes The block's attributes.
	 */
	public static function render_frontend( $attributes ) {
		$json = wp_json_encode( [ 'attributes' => $attributes ] );

		return '<div data-render="' . self::get_full_block_name() . '" data-attributes="' . htmlspecialchars( $json ) . '"></div>';
	}

	/**
	 * Renders a hydratable block attributes with `data-` attributes.
	 *
	 * @param array $attributes The block's attributes.
	 * @param array $content The block's content.
	 */
	public static function as_hydratable_block( $attributes, $content ) {
		$json = wp_json_encode(
			[ 'attributes' => $attributes ]
		);

		return '<div data-hydrate="' . self::get_full_block_name() . '" data-attributes="' . htmlspecialchars( $json ) . '">'
			. trim( $content )
			. '</div>';
	}
}
