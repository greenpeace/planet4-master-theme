<?php
/**
 * Split Two Columns block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class SplitTwoColumns
 *
 * @package P4BKS
 * @since 0.1
 */
class SplitTwoColumns extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	public const BLOCK_NAME = 'planet4-blocks/split-two-columns';

	/**
	 * Block version, update when changing attributes
	 *
	 * @var int VERSION.
	 */
	public const VERSION = 2;

	/**
	 * @var array Block attributes.
	 */
	private static $attributes = [
		'version'            => [ 'type' => 'integer' ],
		'select_issue'       => [ 'type' => 'integer' ],
		'title'              => [ 'type' => 'string' ],
		'issue_description'  => [ 'type' => 'string' ],
		'issue_link_text'    => [ 'type' => 'string' ],
		'issue_link_path'    => [ 'type' => 'string' ],
		'issue_image_id'     => [ 'type' => 'integer' ],
		'issue_image_src'    => [ 'type' => 'integer' ],
		'issue_image_srcset' => [ 'type' => 'integer' ],
		'issue_image_title'  => [ 'type' => 'integer' ],
		'focus_issue_image'  => [ 'type' => 'string' ],
		'select_tag'         => [ 'type' => 'integer' ],
		'tag_name'           => [ 'type' => 'string' ],
		'tag_description'    => [ 'type' => 'string' ],
		'tag_link'           => [ 'type' => 'string' ],
		'button_text'        => [ 'type' => 'string' ],
		'button_link'        => [ 'type' => 'string' ],
		'tag_image_id'       => [ 'type' => 'integer' ],
		'tag_image_src'      => [ 'type' => 'integer' ],
		'tag_image_srcset'   => [ 'type' => 'integer' ],
		'tag_image_title'    => [ 'type' => 'integer' ],
		'focus_tag_image'    => [ 'type' => 'string' ],
	];

	/**
	 * SplitTwoColumns constructor.
	 */
	public function __construct() {
		\register_block_type(
			self::BLOCK_NAME,
			[
				'editor_script'   => 'planet4-blocks',
				'attributes'      => static::$attributes,
				'render_callback' => function ( $attributes ) {
					$json = \wp_json_encode(
						[ 'attributes' => $this->update_data( $attributes ) ]
					);

					if ( ! $this->is_rest_request() ) {
						$json = htmlspecialchars( $json ? $json : [] );
					}

					return '<div 
						data-render="' . self::BLOCK_NAME . '"
						data-attributes="' . ( $json ?? [] ) . '">
					</div>';
				},
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
		return [];
	}

	/**
	 * Migrate data from one version to the last.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The data updated to the current version.
	 */
	public static function update_data( array $fields ): array {
		$version = empty( $fields['version'] ) ? 0 : (int) $fields['version'];
		if ( version_compare( $version, self::VERSION, '>=' ) ) {
			return $fields;
		}

		$issue_id       = (int) ( $fields['select_issue'] ?? 0 );
		$tag_id         = (int) ( $fields['select_tag'] ?? 0 );
		$issue_image_id = (int) ( $fields['issue_image'] ?? $fields['issue_image_id'] ?? 0 );
		$tag_image_id   = (int) ( $fields['tag_image'] ?? $fields['tag_image_id'] ?? 0 );

		$fields                      = array_filter( $fields );
		$fields['issue_image_src']   = $issue_image_id ? wp_get_attachment_url( $issue_image_id ) : '';
		$fields['issue_img_srcset']  = $issue_image_id ? wp_get_attachment_image_srcset( $issue_image_id, 'large' ) : '';
		$fields['issue_image_title'] = $issue_image_id ? get_post_meta( $issue_image_id, '_wp_attachment_image_alt', true ) : '';
		$fields['tag_image_src']     = $tag_image_id ? wp_get_attachment_url( $tag_image_id ) : '';
		$fields['tag_img_srcset']    = $tag_image_id ? wp_get_attachment_image_srcset( $tag_image_id, 'large' ) : '';
		$fields['tag_image_title']   = $tag_image_id ? get_post_meta( $tag_image_id, '_wp_attachment_image_alt', true ) : '';

		if ( $issue_id ) {
			$issue_meta_data           = get_post_meta( (int) $issue_id );
			$fields['title']           = $fields['title'] ?? $issue_meta_data['p4_title'][0] ?? get_the_title( $issue_id );
			$fields['issue_link_path'] = $fields['issue_link_path'] ?? get_permalink( $issue_id );
			$fields['issue_link_text'] = $fields['issue_link_text'] ?? __( 'Learn more about this issue', 'planet4-blocks' );
		}

		if ( $tag_id ) {
			$tag = get_term( $tag_id );
			if ( $tag instanceof \WP_Term ) {
				$fields['tag_name']        = $fields['tag_name'] ?? $tag->name ?? '';
				$fields['tag_link']        = get_tag_link( $tag );
				$fields['tag_description'] = $fields['tag_description'] ?? $tag->description ?? '';
			}
		}

		$fields['button_text'] = $fields['button_text'] ?? __( 'Get Involved', 'planet4-blocks' );
		$fields['button_link'] = $fields['button_link'] ?? $fields['tag_link'] ?? '';

		// Filter allowed properties.
		$updated = array_filter(
			$fields,
			static function ( $key ) {
				return isset( static::$attributes[ $key ] );
			},
			\ARRAY_FILTER_USE_KEY
		);
		// Typehint ids.
		$updated['version']        = self::VERSION;
		$updated['select_issue']   = $issue_id;
		$updated['select_tag']     = $tag_id;
		$updated['issue_image_id'] = $issue_image_id;
		$updated['tag_image_id']   = $tag_image_id;

		return $updated;
	}
}
