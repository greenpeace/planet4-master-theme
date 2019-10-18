<?php
/**
 * Articles block class.
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

use Timber\Timber;

/**
 * Class Articles
 *
 * @package P4GBKS\Blocks
 */
class Articles extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'articles';

	const MAX_ARTICLES = 100;

	const DEFAULT_POST_ARGS = [
		'orderby'          => 'date',
		'post_status'      => 'publish',
		'suppress_filters' => false,
	];

	/**
	 * Register shortcake shortcode.
	 *
	 * @param array  $attributes Shortcode attributes.
	 * @param string $content Content.
	 *
	 * @return mixed
	 */
	public function add_block_shortcode( $attributes, $content ) {
		$attributes = shortcode_atts(
			[
				'article_count'        => '',
				'article_heading'      => '',
				'read_more_text'       => '',
				'read_more_link'       => '',
				'ignore_categories'    => false,
				'title'                => '',
				'articles_description' => '',
				'post_types'           => [],
				'posts'                => [],
				'tags'                 => [],
			],
			$attributes,
			'shortcake_articles'
		);

		return $this->render( $attributes );
	}

	/**
	 * Articles constructor.
	 */
	public function __construct() {
		add_shortcode( 'shortcake_articles', [ $this, 'add_block_shortcode' ] );

		register_block_type(
			'planet4-blocks/articles',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'article_heading'      => [
						'type' => 'string',
					],
					'article_count'        => [
						'type' => 'integer',
					],
					'read_more_text'       => [
						'type' => 'string',
					],
					'read_more_link'       => [
						'type'    => 'string',
						'default' => '',
					],
					'articles_description' => [
						'type'    => 'string',
						'default' => '',
					],
					'tags'                 => [
						'type'  => 'array',
						'items' => [
							'type' => 'integer', // Array definitions require an item type.
						],
					],
					'post_types'           => [
						'type'  => 'array',
						'items' => [
							'type' => 'integer',
						],
					],
					'ignore_categories'    => [
						'type'    => 'boolean',
						'default' => false,
					],
					'button_link_new_tab'  => [
						'type'    => 'boolean',
						'default' => false,
					],
					'posts'                => [
						'type'  => 'array',
						'items' => [
							'type' => 'integer',
						],
					],
					'exclude_post_id'      => [
						'type'    => 'integer',
						'default' => '',
					],
				],
			]
		);

		add_action( 'wp_ajax_load_more', [ $this, 'load_more' ] );
		add_action( 'wp_ajax_nopriv_load_more', [ $this, 'load_more' ] );
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $fields ): array {
		// Read more button links to search results if no link is specified.

		// Article block default text setting.
		$options              = get_option( 'planet4_options' );
		$article_title        = $options['articles_block_title'] ?? __( 'Related Articles', 'planet4-blocks' );
		$article_button_title = $options['articles_block_button_title'] ?? __( 'READ ALL THE NEWS', 'planet4-blocks' );
		$post_type            = get_post_type();

		if ( 'post' === $post_type ) {
			$exclude_post_id           = get_the_ID();
			$fields['exclude_post_id'] = $exclude_post_id;
		}

		$fields['article_heading']      = ! empty( $fields['article_heading'] ) ? $fields['article_heading'] : $article_title;
		$fields['read_more_text']       = ! empty( $fields['read_more_text'] ) ? $fields['read_more_text'] : $article_button_title;
		$fields['article_count']        = ( empty( $fields['article_count'] ) || $fields['article_count'] < 0 ) ? 3 : $fields['article_count'];
		$fields['articles_description'] = $fields['articles_description'] ?? '';

		// Four scenarios for filtering posts.
		// 1) inside tag page - Get posts that have the specific tag assigned.
		// Add extra check for post_types and posts attributes to ensure that the block is rendered from a tag page.
		// 2) post types or tags -
		// a. Get posts by post types or tags defined from select boxes - new behavior.
		// b. inside post - Get results excluding specific post.
		// 3) specific posts - Get posts by ids specified in backend - new behavior / manual override.
		// 4) issue page - Get posts based on page's tags.
		$fiedls_diff = count( array_diff( [ 'post_types', 'posts' ], array_keys( $fields ) ) );
		if ( is_tag() && ! empty( $fields['tags'] ) && 2 === $fiedls_diff ) {
			$args = $this->filter_posts_for_tag_page( $fields );
		} elseif ( ! empty( $fields['post_types'] ) ||
				! empty( $fields['tags'] ) ||
				! empty( $exclude_post_id ) ) {
			$args = $this->filter_posts_by_page_types_or_tags( $fields );
		} elseif ( ! empty( $fields['posts'] ) ) {
			$args = $this->filter_posts_by_ids( $fields );
		} else {
			$args = $this->filter_posts_by_pages_tags( $fields );
		}

		// Get max posts.
		$args['numberposts'] = self::MAX_ARTICLES;

		// Ignore rule, arguments contain suppress_filters.
		// phpcs:ignore$fields['article_count']
		$all_posts    = wp_get_recent_posts( $args );
		$total_pages  = 0 !== $fields['article_count'] ? ceil( count( (array) $all_posts ) / $fields['article_count'] ) : 0;
		$sliced_posts = array_slice( $all_posts, 0, $fields['article_count'] );
		$recent_posts = [];

		// Populate posts array for frontend template if results have been returned.
		if ( false !== $sliced_posts ) {
			$recent_posts = $this->populate_post_items( $sliced_posts );
		}

		// Enqueue js for the frontend.
		if ( ! $this->is_rest_request() ) {
			wp_enqueue_script( 'load-more', P4GBKS_PLUGIN_URL . 'public/js/load_more.js', [ 'jquery' ], '0.3', true );
			wp_localize_script( 'load-more', 'more_url', [ admin_url( 'admin-ajax.php' ) ] );
		}

		$dataset = urldecode( http_build_query( $args, '', ' ' ) );
		$dataset = explode( ' ', $dataset );

		$data = [
			'fields'       => $fields,
			'recent_posts' => $recent_posts,
			'total_pages'  => $total_pages,
			'nonce_action' => 'load_more',
			'dataset'      => $dataset,
		];

		return $data;
	}

	/**
	 * Callback for Lazy-loading the next results.
	 * Gets the paged posts that belong to the next page/load and are to be used with the twig template.
	 */
	public function load_more() {

		// If this is an ajax call.
		if ( wp_doing_ajax() ) {
			$nonce   = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
			$page    = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT );
			$dataset = filter_input_array( INPUT_GET );
			/** @var \P4_Post[] $recent_posts */
			$recent_posts = [];

			// CSRF check.
			if ( wp_verify_nonce( $nonce, 'load_more' ) ) {
				Timber::$locations = P4GBKS_INCLUDES_DIR;

				if ( isset( $dataset['args'] ) ) {
					foreach ( $dataset['args'] as $key => $value ) {
						if ( false !== strpos( $key, '[', true ) ) {
							$new_key                       = strstr( $key, '[', true );
							$dataset['args'][ $new_key ][] = $value;
							unset( $dataset['args'][ $key ] );
						}
					}
					unset( $dataset['args']['page'] );
					unset( $dataset['args']['total'] );

					$dataset['args']['numberposts'] = $dataset['args']['article_count'];
					if ( $page ) {
						$dataset['args']['paged'] = $page;
					}
				}
				$recent_posts = Timber::get_posts( $dataset['args'], 'P4_Post', true );

				if ( $recent_posts ) {
					foreach ( $recent_posts as $key => $recent_post ) {
						if ( ! is_null( $recent_post->thumbnail ) && $recent_post->thumbnail instanceof \Timber\Image ) {
							$img_id                       = $recent_post->thumbnail->id;
							$dimensions                   = wp_get_attachment_metadata( $img_id );
							$recent_post->thumbnail_ratio = ( isset( $dimensions['height'] ) && $dimensions['height'] > 0 ) ? $dimensions['width'] / $dimensions['height'] : 1;
							$recent_post->alt_text        = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
						}
						Timber::render(
							[ 'teasers/tease-articles.twig' ],
							[
								'key'         => $key,
								'recent_post' => $recent_post,
							]
						);
					}
				}
			}
			wp_die();
		}
	}

	/**
	 * Populate selected posts for frontend template.
	 *
	 * @param array $posts Selected posts.
	 *
	 * @return array
	 */
	private function populate_post_items( $posts ) {
		$recent_posts = [];

		if ( $posts ) {
			foreach ( $posts as $recent ) {
				$recent['alt_text'] = '';
				// TODO - Update this method to use P4_Post functionality to get P4_User.
				$author_override           = get_post_meta( $recent['ID'], 'p4_author_override', true );
				$recent['author_name']     = '' === $author_override ? get_the_author_meta( 'display_name', $recent['post_author'] ) : $author_override;
				$recent['author_url']      = '' === $author_override ? get_author_posts_url( $recent['post_author'] ) : '#';
				$recent['author_override'] = $author_override;

				if ( has_post_thumbnail( $recent['ID'] ) ) {
					$img_id                    = get_post_thumbnail_id( $recent['ID'] );
					$dimensions                = wp_get_attachment_metadata( $img_id );
					$recent['thumbnail_ratio'] = ( isset( $dimensions['height'] ) && $dimensions['height'] > 0 ) ? $dimensions['width'] / $dimensions['height'] : 1;
					$recent['alt_text']        = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
				}

				// TODO - Update this method to use P4_Post functionality to get Tags/Terms.
				$wp_tags = wp_get_post_tags( $recent['ID'] );

				$tags = [];

				if ( $wp_tags ) {
					foreach ( $wp_tags as $wp_tag ) {
						$tags_data['name'] = $wp_tag->name;
						$tags_data['slug'] = $wp_tag->slug;
						$tags_data['link'] = get_tag_link( $wp_tag );
						$tags[]            = $tags_data;
					}
				}

				$recent['tags'] = $tags;
				$page_type_data = get_the_terms( $recent['ID'], 'p4-page-type' );
				$page_type      = '';
				$page_type_id   = '';

				if ( $page_type_data && ! is_wp_error( $page_type_data ) ) {
					$page_type    = $page_type_data[0]->name;
					$page_type_id = $page_type_data[0]->term_id;
				}

				$recent['page_type']    = $page_type;
				$recent['page_type_id'] = $page_type_id;
				$recent['link']         = get_permalink( $recent['ID'] );

				$recent_posts[] = $recent;
			}
		}

		return $recent_posts;
	}

	/**
	 * Filter posts based on post ids.
	 *
	 * @param array $fields Block fields values.
	 *
	 * @return array|false
	 */
	private function filter_posts_by_ids( &$fields ) {

		$post_ids = $fields['posts'] ?? [];

		if ( ! empty( $post_ids ) ) {

			// Get all posts with arguments.
			$args = [
				'orderby'          => 'post__in',
				'post_status'      => 'publish',
				'post__in'         => $post_ids,
				'suppress_filters' => false,
			];

			return $args;
		}

		return false;
	}

	/**
	 * Filter posts based on post types (p4_page_type terms).
	 *
	 * @param array $fields Block fields values.
	 *
	 * @return array
	 */
	private function filter_posts_by_page_types_or_tags( &$fields ) {

		$exclude_post_id   = (int) ( $fields['exclude_post_id'] ?? '' );
		$ignore_categories = $fields['ignore_categories'];
		$options           = get_option( 'planet4_options' );

		// Get page categories.
		$post_categories   = get_the_category();
		$category_id_array = [];
		foreach ( $post_categories as $category ) {
			$category_id_array[] = $category->term_id;
		}

		// If any p4_page_type was selected extract the term's slug to be used in the wp query below.
		// post_types attribute filtering.
		$post_types = $fields['post_types'] ?? [];
		// Get user defined tags from backend.
		$tags = $fields['tags'] ?? [];

		// If user has not provided any tag, use post's tags.
		if ( empty( $tags ) && $exclude_post_id ) {
			// Get page/post tags.
			$tags = get_the_tags();

			$tags = ! is_array( $tags ) ? [] : array_map(
				function ( $tag ) {
					return $tag->term_id;
				},
				$tags
			);
		}

		// Get all posts with arguments.
		$args = self::DEFAULT_POST_ARGS;

		if ( $ignore_categories ) {
			if ( $category_id_array ) {
				$args['category__in'] = $category_id_array;
			}
		}

		// For post page block so current main post will exclude.
		if ( $exclude_post_id ) {
			$args['post__not_in'] = [ $exclude_post_id ];
		}

		// Add filter for p4-page-type terms.
		if ( ! empty( $post_types ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'p4-page-type',
					'field'    => 'term_id',
					'terms'    => $post_types,
				],
			];
		}

		if ( ! empty( $tags ) ) {
			$args['tag__in'] = $tags;
		}

		return $args;
	}

	/**
	 * Filter posts based for a specific tag page.
	 *
	 * @param array $fields Block fields values.
	 *
	 * @return array|false
	 */
	private function filter_posts_for_tag_page( &$fields ) {

		$tag_id = $fields['tags'] ?? '';
		$tag    = get_tag( $tag_id );

		if ( $tag instanceof \WP_Term ) {
			// Get all posts with arguments.
			$args            = self::DEFAULT_POST_ARGS;
			$args['tag__in'] = [ (int) $tag_id ];

			return $args;
		}

		return false;
	}

	/**
	 * Filter posts based on page's/post's tags.
	 *
	 * @return array
	 */
	private function filter_posts_by_pages_tags() {

		// Get all posts with arguments.
		$args = self::DEFAULT_POST_ARGS;

		// Get page/post tags.
		$post_tags = get_the_tags();

		if ( $post_tags ) {
			$args['tag__in'] = array_map(
				function ( $tag ) {
						return $tag->term_id;
				},
				$post_tags
			);
		}

		return $args;
	}
}
