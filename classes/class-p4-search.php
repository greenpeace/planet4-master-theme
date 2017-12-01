<?php

use Timber\Timber;
use Timber\Post as TimberPost;

if ( ! class_exists( 'P4_Search' ) ) {

	/**
	 * Class P4_Search
	 */
	class P4_Search {

		const POSTS_LIMIT    = 100;
		const POSTS_PER_PAGE = 10;
		const POSTS_PER_LOAD = 5;
		const DEFAULT_SORT   = 'relevant';

		/** @var string $search_query */
		protected $search_query;
		/** @var array|bool|null $all_posts */
		protected $all_posts;
		/** @var array|bool|null $posts */
		protected $posts;
		/** @var array $selected_sort */
		protected $selected_sort;
		/** @var array $filters */
		protected $filters;
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
		 * @param string     $selected_sort The selected order_by.
		 * @param array      $filters The selected filters.
		 * @param array      $templates An indexed array with template file names. The first to be found will be used.
		 * @param array|null $context An associative array with all the context needed to render the template found first.
		 */
		public function __construct( $search_query, $selected_sort, $filters, $templates = [ 'search.twig', 'archive.twig', 'index.twig' ], $context = null ) {
			$this->search_query = $search_query;
			$this->templates    = $templates;

			if ( $context ) {
				$this->context = $context;
			} else {
				$this->context = Timber::get_context();

				if ( $this->search_query ) {
					if ( $this->validate( $this->context, $selected_sort, $filters ) ) {
						$this->selected_sort = $selected_sort;
						$this->filters       = $filters;
					}
					$this->all_posts     = $this->get_timber_posts();
					$this->current_page  = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
					$this->posts         = array_slice( $this->all_posts, ( $this->current_page - 1 ) * self::POSTS_PER_PAGE, self::POSTS_PER_PAGE );
				}
				$this->set_context( $this->context );
			}
		}

		/**
		 * Gets the respective Timber Posts, to be used with the twig template.
		 * If there are not then uses Timber's get_posts to retrieve all of them (up to the limit set).
		 *
		 * @return array The respective Timber Posts.
		 */
		protected function get_timber_posts() : array {
			$timber_posts = [];

			if ( ! $this->filters ) {
				/*
				 * With no args passed to this call, Timber uses the main query which we filter for customisations via P4_Master_Site class.
				 * When customising this query, use filters on the main query to avoid bypassing SearchWP's handling of the query.
				 */
				return Timber::get_posts();
			} else {
				$posts = $this->get_posts();
				// Use Timber's Post instead of WP_Post so that we can make use of Timber within the template.
				foreach ( $posts as $post ) {
					$timber_posts[] = new TimberPost( $post->ID );
				}
			}

			return $timber_posts;
		}

		/**
		 * Applies user selected filters to the search if there are any and gets the filtered posts.
		 *
		 * @return array The posts of the search.
		 */
		protected function get_posts() : array {

			$args = [
				's'              => $this->search_query,
				'posts_per_page' => self::POSTS_LIMIT,          // Set a high maximum because -1 will get ALL posts and this can be very intensive in production.
				'no_found_rows'  => true,                       // This means that the result counters of each filter might not be 100% precise.
			];

			if ( $this->filters ) {
				foreach ( $this->filters as $type => $filter_type ) {
					foreach ( $filter_type as $filter ) {
						switch ( $type ) {
							case 'cat':
								if ( count( (array) $filter_type ) > 1 ) {
									$args['category__and'][] = $filter['id'];
								} else {
									$args['tax_query'][] = [
										'taxonomy' => 'category',
										'field'    => 'term_id',
										'terms'    => $filter['id'],
									];
								}
								break;
							case 'tag':
								if ( count( (array) $filter_type ) > 1 ) {
									$args['tag__and'][] = $filter['id'];
								} else {
									$args['tax_query'][] = [
										'taxonomy' => 'post_tag',
										'field'    => 'term_id',
										'terms'    => $filter['id'],
									];
								}
								break;
							case 'ptype':
								if ( count( (array) $filter_type ) > 1 ) {
									$args['post__in'][] = $filter['id'];
								} else {
									$args['tax_query'][] = [
										'taxonomy' => 'p4-page-type',
										'field'    => 'term_id',
										'terms'    => $filter['id'],
									];
								}
								break;
							case 'ctype':
								switch ( $filter['id'] ) {
									case 0:
										$args['post_type']   = 'page';
										$options             = get_option( 'planet4_options' );
										$args['post_parent'] = esc_sql( $options['act_page'] );
										break;
									case 1:
										$args['post_type']   = 'attachment';
										break;
									case 2:
										$args['post_type']   = 'page';
										$options             = get_option( 'planet4_options' );
										$args['post_parent__not_in'][] = esc_sql( $options['act_page'] );
										break;
									case 3:
										$args['post_type'] = 'post';
										break;
								}
								break;
						}
					}
				}
			}

			/*
			 * 1. Pass params for SWP_Query and get posts.
			 * 2. If more params that are not supported by SWP_Query like post_parent, post_parent__not_in,
			 *    tag__and, category__and, are required then pass them to WP_Query and get posts.
			 * 3. Get the respective Timber Posts, so that we can use Timber functionality in our search template.
			 */
			$posts = ( new SWP_Query( $args ) )->posts;

			if ( 'attachment' !== $args['post_type'] ) {    // This does not happen when filtering for attachments, since WP_Query does not support searching within rich text documents.
				$ids = [];
				foreach ( $posts as $post ) {
					$ids[] = $post->ID;
				}
				$args['post__in'] = $ids;
				$args['orderby']  = 'post__in';
				$posts            = ( new WP_Query( $args ) )->posts;
			}

			return $posts;
		}

		/**
		 * Sets the P4 Search page context.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function set_context( &$context ) {
			$this->set_general_context( $context );
			$this->set_results_context( $context );
		}

		/**
		 * Sets the general context for the Search page.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function set_general_context( &$context ) {

			// Search context.
			$context['all_posts']     = $this->all_posts;
			$context['posts']         = $this->posts;
			$context['search_query']  = $this->search_query;
			$context['selected_sort'] = $this->selected_sort;
			$context['filters']       = $this->filters;
			$context['found_posts']   = count( (array) $this->all_posts );

			// Footer context.
			$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
			$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
			$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
			$context['copyright_text']        = get_option( 'copyright', '' );
			$context['page_category']         = $category->name ?? __( 'Search page', 'planet4-master-theme' );
			$context['google_tag_value']      = get_option( 'google_tag_manager_identifier', '' ) ?? '';
		}

		/**
		 * Sets the context for the results of the Search.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function set_results_context( &$context ) {

			foreach ( (array) $this->all_posts as $post ) {
				// Category <-> Issue.
				$category = get_the_category( $post->ID )[0];
				if ( $category ) {
					$context['categories'][ $category->term_id ] = [
						'term_id' => $category->term_id,
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
							'term_id' => $page_type->term_id,
							'name'    => $page_type->name,
							'results' => ++$context['page_types'][ $page_type->term_id ]['results'],
						];
					}
				}
				$context['posts_data'][ $post->ID ]['content_type_text'] = $content_type_text;
				$context['posts_data'][ $post->ID ]['content_type'] = $content_type;
				$context['posts_data'][ $post->ID ]['page_types'] = $page_types;

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
							'term_id' => $tag->term_id,
							'name'    => $tag->name,
							'results' => ++$context['tags'][ $tag->term_id ]['results'],
						];
					}
				}
			}

			if ( $context['posts_data']['found_actions'] > 0 ) {
				$context['content_types']['0'] = [
					'name'    => __( 'Action', 'planet4-master-theme' ),
					'results' => $context['posts_data']['found_actions'],
				];
			}
			if ( $context['posts_data']['found_documents'] > 0 ) {
				$context['content_types']['1'] = [
					'name'    => __( 'Document', 'planet4-master-theme' ),
					'results' => $context['posts_data']['found_documents'],
				];
			}
			if ( $context['posts_data']['found_pages'] > 0 ) {
				$context['content_types']['2'] = [
					'name'    => __( 'Page', 'planet4-master-theme' ),
					'results' => $context['posts_data']['found_pages'],
				];
			}
			if ( $context['posts_data']['found_posts'] > 0 ) {
				$context['content_types']['3'] = [
					'name'    => __( 'Post', 'planet4-master-theme' ),
					'results' => $context['posts_data']['found_posts'],
				];
			}

			// Track checked filters.
			if ( $this->filters ) {
				foreach ( $this->filters as $type => $filter_type ) {
					foreach ( $filter_type as $filter ) {
						switch ( $type ) {
							case 'cat':
								$context['categories'][ $filter['id'] ]['checked'] = 'checked';
								break;
							case 'tag':
								$context['tags'][ $filter['id'] ]['checked'] = 'checked';
								break;
							case 'ptype':
								$context['page_types'][ $filter['id'] ]['checked'] = 'checked';
								break;
							case 'ctype':
								$context['content_types'][ $filter['id'] ]['checked'] = 'checked';
								break;
						}
					}
				}
			}

			// Sort associative array with filters alphabetically.
			if ( $context['categories'] ) {
				uasort( $context['categories'], function ( $a, $b ) {
					return strcmp( $a['name'], $b['name'] );
				} );
			}
			if ( $context['tags'] ) {
				uasort( $context['tags'], function ( $a, $b ) {
					return strcmp( $a['name'], $b['name'] );
				} );
			}
		}

		/**
		 * Validates the input.
		 *
		 * @param array  $context Associative array with the data to be passed to the view.
		 * @param string $selected_sort The selected orderby to be validated.
		 * @param array  $filters The selected filters to be validated.
		 *
		 * @return bool True if validation is ok, false if validation fails.
		 */
		public function validate( $context, &$selected_sort, &$filters ) : bool {
			$selected_sort = filter_var( $selected_sort, FILTER_SANITIZE_STRING );
			if ( ! in_array( $selected_sort, array_keys( $context['sort_options'] ), true ) ) {
				$selected_sort = P4_Search::DEFAULT_SORT;
			}

			foreach ( $filters as &$filter_type ) {
				foreach ( $filter_type as &$filter ) {
					$filter['id'] = filter_var( $filter['id'], FILTER_VALIDATE_INT );
					if ( false === $filter['id'] || null === $filter['id'] || $filter['id'] < 0 ) {
						return false;
					}
				}
			}
			return true;
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
				'food',
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
