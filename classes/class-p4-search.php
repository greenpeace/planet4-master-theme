<?php

use Timber\Timber;

if ( ! class_exists( 'P4_Search' ) ) {

	/**
	 * Class P4_Search
	 */
	class P4_Search {

		const POSTS_PER_PAGE = 10;
		const POSTS_PER_LOAD = 5;
		const DEFAULT_SORT   = 'relevant';

		/** @var string $search_query */
		protected $search_query;
		/** @var array|bool|null $all_posts */
		protected $all_posts;
		/** @var array|bool|null $posts */
		protected $posts;
		/** @var array $templates */
		public $templates;
		/** @var array $context */
		public $context;
		/** @var int $current_page */
		public $current_page;

		/**
		 * P4_Search constructor.
		 *
		 * @param string     $search_query The searched term.
		 * @param array      $templates An indexed array with template file names. The first to be found will be used.
		 * @param array|null $context An associative array with all the context needed to render the template found first.
		 */
		public function __construct( $search_query, $templates = [ 'search.twig', 'archive.twig', 'index.twig' ], $context = null ) {
			$this->search_query = $search_query;
			$this->templates    = $templates;

			if ( $context ) {
				$this->context  = $context;
			} else {
				$this->context      = Timber::get_context();

				/*
				* With no args passed to this call, Timber uses the main query which we filter for customisations via P4_Master_Site class.
				*
			    * When customising this query, use filters on the main query to avoid bypassing SearchWP's handling of the query.
			    */
				$this->all_posts    = Timber::get_posts();
				$this->current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
				$this->posts        = array_slice( $this->all_posts, ( $this->current_page - 1 ) * self::POSTS_PER_PAGE, self::POSTS_PER_PAGE );
				$this->add_context( $this->context );
			}
		}

		/**
		 * Sets the P4 Search page context.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function add_context( &$context ) {
			$this->add_general_context( $context );
			$this->add_results_context( $context );
			$this->add_filters_context( $context );
		}

		/**
		 * Adds the general context for the Search page.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function add_general_context( &$context ) {
			global $wp_query;

			$context['all_posts']    = $this->all_posts;
			$context['posts']        = $this->posts;
			$context['search_query'] = $this->search_query;
			$context['found_posts']  = $wp_query->found_posts;

			$selected_sort = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
			if ( ! in_array( $selected_sort, array_keys( $context['sort_options'] ), true ) ) {
				$context['selected_sort'] = self::DEFAULT_SORT;
			} else {
				$context['selected_sort'] = $selected_sort;
			}

			// Footer Items.
			$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
			$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
			$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
			$context['copyright_text']        = get_option( 'copyright', '' );
		}

		/**
		 * Adds the context for the results of the Search.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function add_results_context( &$context ) {

			foreach ( (array) $this->all_posts as $post ) {
				// Category <-> Issue.
				$category = get_the_category( $post->ID )[0];
				if ( $category ) {
					$context['categories'][ $category->term_id ] = [
						'name'    => $category->name,
						'results' => ++$context['categories'][ $category->term_id ]['results'],
					];
				}

				// Content Type <-> Post Type (+Action).
				switch ( $post->post_type ) {
					case 'page':
						if ( 'act' === basename( get_permalink( $post->post_parent ) ) ) {
							$content_type_text = __( 'ACTION', 'planet4-master-theme' );
							$content_type      = 'action';
							$context['posts_data']['found_actions']++;
						} else {
							$content_type_text = __( 'PAGE', 'planet4-master-theme' );
							$content_type      = 'page';
							$context['posts_data']['found_pages']++;
						}
						break;
					case 'attachment':
						$content_type_text = __( 'DOCUMENT', 'planet4-master-theme' );
						$content_type      = 'document';
						$context['posts_data']['found_documents']++;
						break;
					default:
						$content_type_text = __( 'POST', 'planet4-master-theme' );
						$content_type      = 'post';
						$context['posts_data']['found_posts']++;
				}

				// Page Type <-> Category.
				$page_types = get_the_terms( $post->ID, 'p4-page-type' );
				if ( $page_types ) {
					foreach ( (array) $page_types as $page_type ) {
						// p4-page-type filters.
						$context['page_types'][ $page_type->term_id ] = [
							'name'    => $page_type->name,
							'results' => ++$context['page_types'][ $page_type->term_id ]['results'],
						];
					}
				}
				$context['posts_data'][ $post->ID ] = [
					'content_type_text' => $content_type_text,
					'content_type'      => $content_type,
					'page_types'        => $page_types,
				];

				// Tag <-> Campaign.
				$tags = get_the_terms( $post->ID, 'post_tag' );
				if ( $tags ) {
					foreach ( (array) $tags as $tag ) {
						// Set tags info for each result item.
						$context['posts_data'][ $post->ID ]['tags'][] = [
							'name' => $tag->name,
							'link' => get_tag_link( $tag ),
						];

						// Tag filters.
						$context['tags'][ $tag->term_id ] = [
							'name'    => $tag->name,
							'results' => ++$context['tags'][ $tag->term_id ]['results'],
						];
					}
				}
			}

			$context['content_types'] = [
				[
					'name'    => __( 'Action', 'planet4-master-theme' ),
					'results' => $context['posts_data']['found_actions'],
				],
				[
					'name'    => __( 'Document', 'planet4-master-theme' ),
					'results' => $context['posts_data']['found_documents'],
				],
				[
					'name'    => __( 'Page', 'planet4-master-theme' ),
					'results' => $context['posts_data']['found_pages'],
				],
				[
					'name'    => __( 'Post', 'planet4-master-theme' ),
					'results' => $context['posts_data']['found_posts'],
				],
			];

			// Sort filters alphabetically.
			usort( $context['categories'], function( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			} );
			usort( $context['tags'], function( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			});
		}

		/**
		 * Adds the context for the Filters section.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function add_filters_context( &$context ) {
			$context['filters'] = [
				//	[
				//		'name' => 'filter_name',
				//		'link' => 'filter_link',
				//	],
			];
		}

		/**
		 * Adds a section with a Load more button.
		 *
		 * @param array|null $load_more The array with the data for the pagination.
		 */
		public function add_load_more( $load_more = null ) {
			// Add pagination temporarily until we have a lazy loading solution. Use Timber::get_pagination() if we want a more customized one.
			$this->context['load_more'] = $load_more ?? [
				'posts_per_load' => self::POSTS_PER_LOAD,
				'button_text'    => sprintf( __( 'SHOW %s MORE RESULTS', 'planet4-master-theme' ), self::POSTS_PER_LOAD ),
			];
		}

		/**
		 * Adds a section with pagination.
		 *
		 * @param array|null $pagination The array with the data for the pagination.
		 */
		public function add_pagination( $pagination = null ) {
			// Add pagination temporarily until we have a lazy loading solution. Use Timber::get_pagination() if we want a more customized one.
			$this->context['pagination'] = $pagination ?? [
				'screen_reader_text' => ' ',
			];
		}

		/**
		 * Adds a section with suggested keywords.
		 *
		 * @param array|null $suggestions The array with the suggested keywords.
		 */
		public function add_suggestions( $suggestions = null ) {
			$this->context['suggestions'] = $suggestions ?? [
				'agriculture',
				'agriculture',
				'agriculture',
				'food',
				'food',
				'food',
				'organic',
				'organic',
				'organic',
			];
		}

		/**
		 * View the Search page template.
		 */
		public function view() {
			Timber::render( $this->templates, $this->context );
		}
	}
}
