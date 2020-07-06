<?php
/**
 * Articles block class.
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

use P4_Post;
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
	 * Articles constructor.
	 */
	public function __construct() {
		register_block_type(
			'planet4-blocks/articles',
			[
				'editor_script' => 'planet4-blocks',
				'attributes'    => [
					'article_heading'      => [
						'type' => 'string',
					],
					'article_count'        => [
						'type'    => 'integer',
						'default' => 3,
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
				],
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
	 * Callback for Lazy-loading the next results.
	 * Gets the paged posts that belong to the next page/load and are to be used with the twig template.
	 */
	public function load_more() {

		// If this is an ajax call.
		if ( ! wp_doing_ajax() ) {
			return;
		}

		$page    = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT );
		$dataset = filter_input_array( INPUT_GET );

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
		$dataset['args']['post_status'] = 'publish';

		$recent_posts = Timber::get_posts( $dataset['args'], P4_Post::class, true );

		foreach ( $recent_posts as $key => $recent_post ) {
			if (
				null !== $recent_post->thumbnail
				&& $recent_post->thumbnail instanceof \Timber\Image
			) {
				$img_id                       = $recent_post->thumbnail->id;
				$dimensions                   = wp_get_attachment_metadata( $img_id );
				$recent_post->thumbnail_ratio = ( isset( $dimensions['height'] ) && $dimensions['height'] > 0 )
					? $dimensions['width'] / $dimensions['height'] : 1;
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
		wp_die();
	}

	/**
	 * Populate selected posts for frontend template.
	 *
	 * @param array $posts Selected posts.
	 *
	 * @return array
	 */
	public function populate_post_items( $posts ) {
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
					$recent['thumbnail_url']   = get_the_post_thumbnail_url( $recent['ID'], 'medium-large' );
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
	public function filter_posts_by_ids( &$fields ) {

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
	public function filter_posts_by_page_types_or_tags( &$fields ) {

		$exclude_post_id   = (int) ( $fields['exclude_post_id'] ?? '' );
		$ignore_categories = $fields['ignore_categories'];

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

		// Validate tag ids.
		$tags = array_filter(
			(array) $tags,
			function( $tag_id ) {
				return get_tag( $tag_id ) instanceof \WP_Term;
			}
		);

		// If user has not provided any tag, use post's tags.
		if ( empty( $tags ) ) {
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

		if ( true !== $ignore_categories ) {
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
			$filtered_tag = [];
			foreach ( $tags as $tag_id ) {
				$tag = get_tag( $tag_id );
				// Check if tag exist or not.
				if ( $tag instanceof \WP_Term ) {
					$filtered_tag[] = $tag_id;
				}
			}
			$args['tag__in'] = $filtered_tag;
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
	public function filter_posts_for_tag_page( &$fields ) {

		$tag_id = $fields['tags'] ?? '';
		$tag    = get_tag( $tag_id[0] );

		if ( $tag instanceof \WP_Term ) {
			// Get all posts with arguments.
			$args            = self::DEFAULT_POST_ARGS;
			$args['tag__in'] = $tag_id;

			return $args;
		}

		return false;
	}

	/**
	 * Filter posts based on page's/post's tags.
	 *
	 * @return array
	 */
	public function filter_posts_by_pages_tags() {

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
