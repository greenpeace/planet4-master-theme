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
	public const BLOCK_NAME = 'split-two-columns';

	/**
	 * Block version, update when changing attributes
	 *
	 * @var int VERSION.
	 */
	private const VERSION = 2;

	/**
	 * @var array Block attributes.
	 */
	private const ATTRIBUTES = [
		'version'            => [ 'type' => 'integer' ],
		'select_issue'       => [ 'type' => 'integer' ],
		'title'              => [ 'type' => 'string' ],
		'issue_description'  => [ 'type' => 'string' ],
		'issue_link_text'    => [ 'type' => 'string' ],
		'issue_link_path'    => [ 'type' => 'string' ],
		'issue_image_id'     => [ 'type' => 'integer' ],
		'issue_image_src'    => [ 'type' => 'string' ],
		'issue_image_srcset' => [ 'type' => 'string' ],
		'issue_image_title'  => [ 'type' => 'string' ],
		'focus_issue_image'  => [ 'type' => 'string' ],
		'select_tag'         => [ 'type' => 'integer' ],
		'tag_name'           => [ 'type' => 'string' ],
		'tag_description'    => [ 'type' => 'string' ],
		'tag_link'           => [ 'type' => 'string' ],
		'button_text'        => [ 'type' => 'string' ],
		'button_link'        => [ 'type' => 'string' ],
		'tag_image_id'       => [ 'type' => 'integer' ],
		'tag_image_src'      => [ 'type' => 'string' ],
		'tag_image_srcset'   => [ 'type' => 'string' ],
		'tag_image_title'    => [ 'type' => 'string' ],
		'focus_tag_image'    => [ 'type' => 'string' ],
		'edited'             => [ 'type' => 'object' ],
	];

	/**
	 * SplitTwoColumns constructor.
	 */
	public function __construct() {
		// Registering meta field to make it appear in REST API.
		\register_meta(
			'term',
			'tag_attachment_id',
			[
				'show_in_rest' => true,
				'type'         => 'integer',
				'single'       => true,
			]
		);

		\register_block_type(
			self::get_full_block_name(),
			[
				'editor_script'   => 'planet4-blocks',
				'attributes'      => self::ATTRIBUTES,
				'render_callback' => function ( $attributes ) {
					return self::render_frontend( self::update_data( $attributes ) );
				},
			]
		);
	}

	/**
	 * Required by the `Base_Block` class.
	 *
	 * @param array $fields Unused, required by the abstract function.
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

		$issue_id       = (int) ( $fields['select_issue'] ?? null );
		$tag_id         = (int) ( $fields['select_tag'] ?? null );
		$issue_image_id = (int) ( $fields['issue_image'] ?? $fields['issue_image_id'] ?? get_post_thumbnail_id( $issue_id ) ?? 0 );
		$tag_image_id   = (int) ( $fields['tag_image'] ?? $fields['tag_image_id'] ?? get_term_meta( $tag_id, 'tag_attachment_id', true ) ?? 0 );

		// Registering fields edition status.
		$edited = [
			'title'             => ! empty( $fields['title'] ),
			'issue_description' => ! empty( $fields['issue_description'] ),
			'issue_link_text'   => ! empty( $fields['issue_link_text'] ),
			'tag_description'   => ! empty( $fields['tag_description'] ),
			'button_text'       => ! empty( $fields['button_text'] ),
			'issue_image_id'    => null !== $issue_image_id,
			'tag_image_id'      => null !== $tag_image_id,
		];

		$fields = array_filter( $fields );

		$fields['issue_image_src']    = $issue_image_id ? wp_get_attachment_url( $issue_image_id ) : '';
		$fields['issue_image_title']  = $issue_image_id ? get_post_meta( $issue_image_id, '_wp_attachment_image_alt', true ) : '';
		$fields['issue_image_srcset'] = $issue_image_id ? wp_get_attachment_image_srcset( $issue_image_id, 'large' ) : '';

		$fields['tag_image_src']    = $tag_image_id ? wp_get_attachment_url( $tag_image_id ) : '';
		$fields['tag_img_srcset']   = $tag_image_id ? wp_get_attachment_image_srcset( $tag_image_id, 'large' ) : '';
		$fields['tag_image_title']  = $tag_image_id ? get_post_meta( $tag_image_id, '_wp_attachment_image_alt', true ) : '';
		$fields['tag_image_srcset'] = $tag_image_id ? wp_get_attachment_image_srcset( $tag_image_id, 'large' ) : '';

		if ( $issue_id ) {
			$issue_meta_data             = get_post_meta( $issue_id );
			$fields['title']             = $fields['title'] ?? $issue_meta_data['p4_title'][0] ?? get_the_title( $issue_id );
			$fields['issue_description'] = wp_trim_words( $fields['issue_description'] ?? $issue_meta_data['p4_description'][0] ?? '', 25 );
			$fields['issue_link_path']   = $fields['issue_link_path'] ?? get_permalink( $issue_id );
			$fields['issue_link_text']   = $fields['issue_link_text'] ?? __( 'Learn more about this issue', 'planet4-blocks' );
		}

		if ( $tag_id ) {
			$tag = get_term( $tag_id );
			if ( $tag instanceof \WP_Term ) {
				$fields['tag_name']        = $fields['tag_name'] ?? $tag->name ?? '';
				$fields['tag_link']        = get_tag_link( $tag );
				$fields['tag_description'] = wp_trim_words( $fields['tag_description'] ?? $tag->description ?? '', 25 );
			}
		}

		$fields['button_text'] = $fields['button_text'] ?? __( 'Get involved', 'planet4-blocks' );
		$fields['button_link'] = $fields['button_link'] ?? $fields['tag_link'] ?? '';

		// Filter allowed properties.
		$updated = array_filter(
			$fields,
			static function ( $key ) {
				return isset( self::ATTRIBUTES[ $key ] ) || 'className' === $key;
			},
			\ARRAY_FILTER_USE_KEY
		);

		$updated['version']        = self::VERSION;
		$updated['select_issue']   = $issue_id;
		$updated['select_tag']     = $tag_id;
		$updated['issue_image_id'] = $issue_image_id;
		$updated['tag_image_id']   = $tag_image_id;

		$updated['edited'] = $edited;

		return $updated;
	}
}
