<?php
/**
 * P4 Search Class
 *
 * @package P4MT
 */

use Timber\Timber;
use Timber\Post as TimberPost;

if ( ! class_exists( 'P4_Search' ) ) {

	/**
	 * Class P4_Search
	 */
	class P4_Search {

		const POSTS_LIMIT           = 300;
		const POSTS_PER_PAGE        = 10;
		const POSTS_PER_LOAD        = 5;
		const SHOW_SCROLL_TIMES     = 2;
		const DEFAULT_SORT          = 'relevant';
		const DEFAULT_MIN_WEIGHT    = 1;
		const DEFAULT_PAGE_WEIGHT   = 20;
		const DEFAULT_ACTION_WEIGHT = 25;
		const DEFAULT_MAX_WEIGHT    = 30;
		const DEFAULT_CACHE_TTL     = 600;
		const DUMMY_THUMBNAIL       = '/images/dummy-thumbnail.png';
		const DOCUMENT_TYPES        = [
			'application/pdf',
		];

		/**
		 * Search Query
		 *
		 * @var string $search_query
		 */
		protected $search_query;

		/**
		 * Posts
		 *
		 * @var array|bool|null $posts
		 */
		protected $posts;

		/**
		 * Paged Posts
		 *
		 * @var array|bool|null $paged_posts
		 */
		protected $paged_posts;

		/**
		 * Selected sort criteria
		 *
		 * @var array $selected_sort
		 */
		protected $selected_sort;

		/**
		 * FIlters
		 *
		 * @var array $filters
		 */
		protected $filters;

		/**
		 * True if search is performed via ES.
		 *
		 * @var boolean $is_elastic_search
		 */
		protected $is_elastic_search;

		/**
		 * Localizations
		 *
		 * @var array $localizations
		 */
		protected $localizations;

		/**
		 * Templates
		 *
		 * @var array $templates
		 */
		public $templates;

		/**
		 * Context
		 *
		 * @var array $context
		 */
		public $context;

		/**
		 * Current Page
		 *
		 * @var int $current_page
		 */
		public $current_page;

		/**
		 * P4_Search constructor.
		 */
		public function __construct() {}

		/**
		 * Initialize the class. Hook necessary actions and filters.
		 */
		protected function initialize() {
			$this->localizations = [
				// The ajaxurl variable is a global js variable defined by WP itself but only for the WP admin
				// For the frontend we need to define it ourselves and pass it to js.
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'show_scroll_times' => self::SHOW_SCROLL_TIMES,
			];
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
		}

		/**
		 * Conducts the actual search.
		 *
		 * @param string     $search_query The searched term.
		 * @param string     $selected_sort The selected order_by.
		 * @param array      $filters The selected filters.
		 * @param boolean    $is_elastic_search True if we are using Elasticsearch or false if we are using SearchWP.
		 * @param array      $templates An indexed array with template file names. The first to be found will be used.
		 * @param array|null $context An associative array with all the context needed to render the template found first.
		 */
		public function load( $search_query, $selected_sort = self::DEFAULT_SORT, $filters = [], $is_elastic_search = false, $templates = [ 'search.twig', 'archive.twig', 'index.twig' ], $context = null ) {
			$this->initialize();
			$this->search_query      = $search_query;
			$this->is_elastic_search = $is_elastic_search;
			$this->templates         = $templates;

			if ( $context ) {
				$this->context = $context;
			} else {
				$this->context = Timber::get_context();

				// Validate user input (sort, filters, etc).
				if ( $this->validate( $selected_sort, $filters, $this->context ) ) {
					$this->selected_sort = $selected_sort;
					$this->filters       = $filters;
				}

				// Set the decoded url query string as key.
				$query_string = urldecode( filter_input( INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING ) );
				$group        = 'search';
				$subgroup     = $this->search_query ? $this->search_query : 'all';

				// Check Object cache for stored key.
				$this->check_cache( $query_string, "$group:$subgroup" );

				// If posts were found either in object cache or primary database then get the first POSTS_PER_LOAD results.
				if ( $this->posts ) {
					// TODO - This if will be removed after applying Lazy-loading also when searching for specific term.
					// TODO - For now get paged posts only when searching for everything.
					if ( ! $this->search_query ) {
						$this->paged_posts = array_slice( $this->posts, 0, self::POSTS_PER_LOAD );
					}
				}

				$this->current_page = ( 0 === get_query_var( 'paged' ) ) ? 1 : get_query_var( 'paged' );
				$this->set_context( $this->context );
			}
		}

		/**
		 * Callback for Lazy-loading the next results.
		 * Gets the paged posts that belong to the next page/load and are to be used with the twig template.
		 */
		public function get_paged_posts() {
			// If this is an ajax call.
			if ( wp_doing_ajax() ) {
				$search_action = filter_input( INPUT_GET, 'search-action', FILTER_SANITIZE_STRING );
				$paged         = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_STRING );

				// Check if call action is correct.
				if ( 'get_paged_posts' === $search_action ) {
					$search_async               = new self();
					$search_async->search_query = trim( get_search_query() );

					// Get the decoded url query string and then use it as key for redis.
					$query_string_full = urldecode( filter_input( INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING ) );
					$query_string      = str_replace( '&query-string=', '', strstr( $query_string_full, '&query-string=' ) );

					$group                      = 'search';
					$subgroup                   = $search_async->search_query ? $search_async->search_query : 'all';
					$search_async->current_page = $paged;

					parse_str( $query_string, $filters_array );
					$selected_sort     = $filters_array['orderby'];
					$selected_filters  = $filters_array['f'];
					$is_elastic_search = $filters_array['es'];
					$filters           = [];

					// Handle submitted filter options.
					if ( $selected_filters && is_array( $selected_filters ) ) {
						foreach ( $selected_filters as $type => $filter_type ) {
							foreach ( $filter_type as $name => $id ) {
								$filters[ $type ][] = [
									'id'   => $id,
									'name' => $name,
								];
							}
						}
					}

					// Validate user input (sort, filters, etc).
					if ( $search_async->validate( $selected_sort, $filters, $search_async->context ) ) {
						$search_async->selected_sort     = $selected_sort;
						$search_async->filters           = $filters;
						$search_async->is_elastic_search = $is_elastic_search;
					}

					// TODO - Set the correct filters so that it will work when searching for specific term with filters applied.
					// Check Object cache for stored key.
					$search_async->check_cache( $query_string, "$group:$subgroup" );

					// Check if there are results already in the cache else fallback to the primary database.
					if ( $search_async->posts ) {
						$search_async->paged_posts = array_slice( $search_async->posts, ( $search_async->current_page - 1 ) * self::POSTS_PER_LOAD, self::POSTS_PER_LOAD );
					} else {
						$search_async->paged_posts = $search_async->get_timber_posts( $search_async->current_page );
					}

					// If there are paged results then set their context and send them back to client.
					if ( $search_async->paged_posts ) {
						$search_async->set_results_context( $search_async->context );
						$search_async->view_paged_posts();
					}
				}
				wp_die();
			}
		}

		/**
		 * Check if search is cached. If it is not then get posts from primary database and cache it.
		 *
		 * @param string $cache_key The key that will be used for storing the results in the object cache.
		 * @param string $cache_group The group that will be used for storing the results in the object cache.
		 */
		protected function check_cache( $cache_key, $cache_group ) {
			// Get search results from cache and then set the context for those results.
			$this->posts = wp_cache_get( $cache_key, $cache_group );

			// If cache key expired then retrieve results once again and re-cache them.
			if ( false === $this->posts ) {
				$this->posts = $this->get_timber_posts();
				if ( $this->posts ) {
					wp_cache_add( $cache_key, $this->posts, $cache_group, self::DEFAULT_CACHE_TTL );
				}
			}
		}

		/**
		 * Gets the respective Timber Posts, to be used with the twig template.
		 * If there are not then uses Timber's get_posts to retrieve all of them (up to the limit set).
		 *
		 * @param int $paged The number of the page of the results to be shown when using pagination/load_more.
		 *
		 * @return array The respective Timber Posts.
		 */
		protected function get_timber_posts( $paged = 1 ) : array {
			$timber_posts = [];

			if ( $this->search_query && ! $this->filters && false === $this->is_elastic_search ) {
				/*
				 * With no args passed to this call, Timber uses the main query which we filter for customisations via P4_Master_Site class.
				 * When customising this query, use filters on the main query to avoid bypassing SearchWP's handling of the query.
				 */
				$timber_posts = Timber::get_posts();
			} else {
				$posts = $this->get_posts( $paged );
				// Use Timber's Post instead of WP_Post so that we can make use of Timber within the template.
				if ( $posts ) {
					foreach ( $posts as $post ) {
						$timber_posts[] = new TimberPost( $post->ID );
					}
				}
			}

			return (array) $timber_posts;
		}

		/**
		 * Applies user selected filters to the search if there are any and gets the filtered posts.
		 *
		 * @param int $paged The number of the page of the results to be shown when using pagination/load_more.
		 *
		 * @return array The posts of the search.
		 */
		protected function get_posts( $paged = 1 ) : array {

			$args = [
				'posts_per_page' => self::POSTS_LIMIT,          // Set a high maximum because -1 will get ALL posts and this can be very intensive in production.
				'no_found_rows'  => true,                       // This means that the result counters of each filter might not be 100% precise.
				'post_type'      => 'any',
				'post_status'    => [ 'publish', 'inherit' ],
			];

			if ( $paged > 1 ) {
				$args['posts_per_page'] = self::POSTS_PER_LOAD;
				$args['paged']          = $paged;
			}

			if ( $this->search_query ) {
				$args['s'] = $this->search_query;
			} else {
				// If we search for everything then order first by 'weight' and then by 'post_date'.
				$args2 = [
					'orderby'    => 'meta_value date',
					'order'      => 'DESC DESC',
					'meta_query' => [
						'relation' => 'OR',
						[
							'key'     => 'weight',
							'compare' => 'NOT EXISTS',
						],
						[
							'key'     => 'weight',
							'compare' => 'EXISTS',
						],
					],
				];
				$args  = array_merge( $args, $args2 );
			}

			if ( $this->filters ) {
				foreach ( $this->filters as $type => $filter_type ) {
					foreach ( $filter_type as $filter ) {
						switch ( $type ) {
							case 'cat':
								$args['tax_query'][] = [
									'taxonomy' => 'category',
									'field'    => 'term_id',
									'terms'    => $filter['id'],
								];
								break;
							case 'tag':
								$args['tax_query'][] = [
									'taxonomy' => 'post_tag',
									'field'    => 'term_id',
									'terms'    => $filter['id'],
								];
								break;
							case 'ptype':
								// This taxonomy is used only for Posts.
								$args['post_type']   = 'post';
								$args['tax_query'][] = [
									'taxonomy' => 'p4-page-type',
									'field'    => 'term_id',
									'terms'    => $filter['id'],
								];
								break;
							case 'ctype':
								switch ( $filter['id'] ) {
									case 0:
										$args['post_type']   = 'page';
										$args['post_status'] = 'publish';
										$options             = get_option( 'planet4_options' );
										$args['post_parent'] = esc_sql( $options['act_page'] );
										break;
									case 1:
										$args['post_type']      = 'attachment';
										$args['post_status']    = 'inherit';
										$args['post_mime_type'] = self::DOCUMENT_TYPES;
										// TODO - Fix other mime types being calculated into the Document filter counter.
										add_filter( 'ep_formatted_args', function ( $formatted_args ) use ( $args ) {
											if ( ! empty( $args['post_mime_type'] ) ) {
												$formatted_args['post_filter']['bool']['must'] = array(
													'terms' => array(
														'post_type'      => 'attachment',
														'post_status'    => 'inherit',
														'post_mime_type' => array_values( (array) $args['post_mime_type'] ),
													),
												);
												$use_filters = true;
											}
											return $formatted_args;
										}, 10, 1 );
										break;
									case 2:
										$args['post_type']             = 'page';
										$args['post_status']           = 'publish';
										$options                       = get_option( 'planet4_options' );
										$args['post_parent__not_in'][] = esc_sql( $options['act_page'] );

										// Workaround for making 'post_parent__not_in' to work with ES.
										add_filter( 'ep_formatted_args', function ( $formatted_args ) use ( $args ) {
											if ( ! empty( $args['post_parent__not_in'] ) ) {
												$formatted_args['post_filter']['bool']['must_not'] = array(
													'terms' => array(
														'post_parent' => array_values( (array) $args['post_parent__not_in'] ),
													),
												);
												$use_filters = true;
											}
											return $formatted_args;
										}, 10, 1 );
										break;
									case 3:
										$args['post_type']   = 'post';
										$args['post_status'] = 'publish';
										break;
								}
								break;
						}
					}
				}
			}

			if ( false === $this->is_elastic_search ) {
				/*
				 * 1. Pass params for SWP_Query and get posts.
				 * 2. If more params that are not supported by SWP_Query like post_parent, post_parent__not_in,
				 *    tag__and, category__and, are required then pass them to WP_Query and get posts.
				 * 3. Get the respective Timber Posts, so that we can use Timber functionality in our search template.
				 */
				if ( $this->search_query ) {
					$posts = ( new SWP_Query( $args ) )->posts;
				}

				// This happens when we search for everything or when filtering for attachments, since WP_Query does not support searching within rich text documents.
				if ( ! $this->search_query || ( isset( $args['post_type'] ) && 'attachment' !== $args['post_type'] ) ) {
					if ( $posts ) {
						$ids = [];
						foreach ( $posts as $post ) {
							$ids[] = $post->ID;
						}
						$args['post__in'] = $ids;
						// If posts were found by SearchWP and we sort by relevance then keep
						// the order that they were found with by SearchWP.
						if ( self::DEFAULT_SORT === $this->selected_sort ) {
							$args['orderby'] = 'post__in';
						}
					}

					// Get the stem of the word and use it instead of the original word,
					// because WP_Query does not automatically use the stem of the word.
					$stem = $this->get_stem( $this->search_query );
					if ( $stem ) {
						$args['s'] = $stem;
					}

					$posts = ( new WP_Query( $args ) )->posts;
				}
			} else {
				$args['s']            = $this->search_query;
				$args['ep_integrate'] = true;

				// Add sort by date.
				$selected_sort = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
				$selected_sort = sanitize_sql_orderby( $selected_sort );

				if ( $selected_sort && self::DEFAULT_SORT !== $selected_sort ) {
					$args['orderby'] = 'date';
					$args['order']   = 'desc';
				}

				$posts = ( new WP_Query( $args ) )->posts;
			}

			return (array) $posts;
		}

		/**
		 * Gets the stem of a word that was produced and stored by SearchWP in the swp_terms table.
		 *
		 * @param string $word The original word.
		 *
		 * @return string The stem of the word.
		 */
		protected function get_stem( $word ) : string {
			global $wpdb;

			$statement = $wpdb->prepare( "SELECT `stem` FROM `{$wpdb->prefix}swp_terms` where `term` = %s", $word );
			$result    = $wpdb->get_col( $statement ); // WPCS: unprepared SQL OK.
			return $result[0] ?? '';
		}

		/**
		 * Sets the P4 Search page context.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function set_context( &$context ) {
			$this->set_general_context( $context );
			$this->set_filters_context( $context );
			$this->set_results_context( $context );
		}

		/**
		 * Sets the general context for the Search page.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function set_general_context( &$context ) {

			// Search context.
			$context['posts']             = $this->posts;
			$context['paged_posts']       = $this->paged_posts;
			$context['current_page']      = $this->current_page;
			$context['search_query']      = $this->search_query;
			$context['is_elastic_search'] = $this->is_elastic_search;
			$context['selected_sort']     = $this->selected_sort;
			$context['default_sort']      = self::DEFAULT_SORT;
			$context['filters']           = $this->filters;
			$context['found_posts']       = count( (array) $this->posts );
			$context['source_selection']  = false;
			$context['page_category']     = $category->name ?? __( 'Search page', 'planet4-master-theme' );

			if ( $this->search_query ) {
				$context['page_title'] = sprintf(
					// translators: %1$d = Number of results.
					_n( '%1$d result for \'%2$s\'', '%1$d results for \'%2$s\'', $context['found_posts'], 'planet4-master-theme' ),
					$context['found_posts'],
					$this->search_query
				);
			} else {
				// translators: %d = Number of results.
				$context['page_title'] = sprintf( _n( '%d result', '%d results', $context['found_posts'], 'planet4-master-theme' ), $context['found_posts'] );
			}
		}

		/**
		 * Set filters context
		 *
		 * @param mixed $context Context.
		 */
		protected function set_filters_context( &$context ) {
			// Retrieve P4 settings in order to check that we add only categories that are children of the Issues category.
			$options = get_option( 'planet4_options' );

			// Category <-> Issue.
			// Consider Issues that have multiple Categories.
			$categories = get_categories();
			if ( $categories ) {
				foreach ( $categories as $category ) {
					if ( $category->parent === (int) $options['issues_parent_category'] ) {
						$context['categories'][ $category->term_id ] = [
							'term_id' => $category->term_id,
							'name'    => $category->name,
							'results' => 0,
						];
					}
				}
			}

			// Tag <-> Campaign.
			$tags = get_terms(
				[
					'taxonomy'   => 'post_tag',
					'hide_empty' => false,
				]
			);
			if ( $tags ) {
				foreach ( (array) $tags as $tag ) {
					// Tag filters.
					$context['tags'][ $tag->term_id ] = [
						'term_id' => $tag->term_id,
						'name'    => $tag->name,
						'results' => 0,
					];
				}
			}

			// Page Type <-> Category.
			$page_types = get_terms(
				[
					'taxonomy'   => 'p4-page-type',
					'hide_empty' => false,
				]
			);
			if ( $page_types ) {
				foreach ( (array) $page_types as $page_type ) {
					// p4-page-type filters.
					$context['page_types'][ $page_type->term_id ] = [
						'term_id' => $page_type->term_id,
						'name'    => $page_type->name,
						'results' => 0,
					];
				}
			}

			// Post Type (+Action) <-> Content Type.
			$context['content_types']['0'] = [
				'name'    => __( 'Action', 'planet4-master-theme' ),
				'results' => 0,
			];
			$context['content_types']['1'] = [
				'name'    => __( 'Document', 'planet4-master-theme' ),
				'results' => 0,
			];
			$context['content_types']['2'] = [
				'name'    => __( 'Page', 'planet4-master-theme' ),
				'results' => 0,
			];
			$context['content_types']['3'] = [
				'name'    => __( 'Post', 'planet4-master-theme' ),
				'results' => 0,
			];

			// Keep track of which filters are already checked.
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
				uasort(
					$context['categories'],
					function ( $a, $b ) {
						return strcmp( $a['name'], $b['name'] );
					}
				);
			}
			if ( $context['tags'] ) {
				uasort(
					$context['tags'],
					function ( $a, $b ) {
						return strcmp( $a['name'], $b['name'] );
					}
				);
			}
		}

		/**
		 * Sets the context for the results of the Search.
		 *
		 * Categories are Issues.
		 * Tags       are Campaigns.
		 * Page types are Categories.
		 * Post_types are Content Types.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function set_results_context( &$context ) {

			$posts = $this->posts;

			// Retrieve P4 settings in order to check that we add only categories that are children of the Issues category.
			$options = get_option( 'planet4_options' );

			// Pass planet4 settings.
			$context['settings'] = get_option( 'planet4_options' );

			// Set default thumbnail.
			$context['posts_data']['dummy_thumbnail'] = get_template_directory_uri() . self::DUMMY_THUMBNAIL;

			foreach ( (array) $posts as $post ) {
				// Category <-> Issue.
				// Consider Issues that have multiple Categories.
				$categories = get_the_category( $post->ID );
				if ( $categories ) {
					foreach ( $categories as $category ) {
						if ( $category->parent === (int) $options['issues_parent_category'] ) {
							$context['categories'][ $category->term_id ]['term_id'] = $category->term_id;
							$context['categories'][ $category->term_id ]['name']    = $category->name;
							$context['categories'][ $category->term_id ]['results']++;
						}
					}
				}

				// Post Type (+Action) <-> Content Type.
				switch ( $post->post_type ) {
					case 'page':
						if ( $post->post_parent === (int) $options['act_page'] ) {
							$content_type_text = __( 'ACTION', 'planet4-master-theme' );
							$content_type      = 'action';
							$context['content_types']['0']['results']++;
						} else {
							$content_type_text = __( 'PAGE', 'planet4-master-theme' );
							$content_type      = 'page';
							$context['content_types']['2']['results']++;
						}
						break;
					case 'attachment':
						$content_type_text = __( 'DOCUMENT', 'planet4-master-theme' );
						$content_type      = 'document';
						$context['content_types']['1']['results']++;
						break;
					default:
						$content_type_text = __( 'POST', 'planet4-master-theme' );
						$content_type      = 'post';
						$context['content_types']['3']['results']++;
				}

				// Page Type <-> Category. This taxonomy is used only for Posts.
				if ( 'post' === $post->post_type ) {
					$page_types = get_the_terms( $post->ID, 'p4-page-type' );
					if ( is_array( $page_types ) ) {
						foreach ( (array) $page_types as $page_type ) {
							// p4-page-type filters.
							$context['page_types'][ $page_type->term_id ]['term_id'] = $page_type->term_id;
							$context['page_types'][ $page_type->term_id ]['name']    = $page_type->name;
							$context['page_types'][ $page_type->term_id ]['results'] ++;
						}
					}
				}
				$context['posts_data'][ $post->ID ]['content_type_text'] = $content_type_text;
				$context['posts_data'][ $post->ID ]['content_type']      = $content_type;
				$context['posts_data'][ $post->ID ]['page_types']        = $page_types;

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
						$context['tags'][ $tag->term_id ]['term_id'] = $tag->term_id;
						$context['tags'][ $tag->term_id ]['name']    = $tag->name;
						$context['tags'][ $tag->term_id ]['results']++;
					}
				}
			}
		}

		/**
		 * Validates the input.
		 *
		 * @param string $selected_sort The selected orderby to be validated.
		 * @param array  $filters The selected filters to be validated.
		 * @param array  $context Associative array with the data to be passed to the view.
		 *
		 * @return bool True if validation is ok, false if validation fails.
		 */
		public function validate( &$selected_sort, &$filters, $context ) : bool {
			$selected_sort = filter_var( $selected_sort, FILTER_SANITIZE_STRING );
			if ( ! isset( $context['sort_options'] ) || ! in_array( $selected_sort, array_keys( $context['sort_options'] ), true ) ) {
				$selected_sort = P4_Search::DEFAULT_SORT;
			}

			if ( $filters ) {
				foreach ( $filters as &$filter_type ) {
					foreach ( $filter_type as &$filter ) {
						$filter['id'] = filter_var( $filter['id'], FILTER_VALIDATE_INT );
						if ( false === $filter['id'] || null === $filter['id'] || $filter['id'] < 0 ) {
							return false;
						}
					}
				}
			}
			return true;
		}

		/**
		 * Adds a section with a Load more button.
		 *
		 * @param array|null $args The array with the data for the pagination.
		 */
		public function add_load_more( $args = null ) {
			// Add pagination temporarily until we have a lazy loading solution. Use Timber::get_pagination() if we want a more customized one.
			$this->context['load_more'] = $args ?? [
				'posts_per_load' => self::POSTS_PER_LOAD,
				// Translators: %s = number of results per page.
				'button_text'    => sprintf( __( 'SHOW %s MORE RESULTS', 'planet4-master-theme' ), self::POSTS_PER_LOAD ),
				'async'          => true,
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

		/**
		 * View the paged posts of the next page/load.
		 */
		public function view_paged_posts() {
			// TODO - The $paged_context related code should be transferred to set_results_context method for better separation of concerns.
			if ( $this->paged_posts ) {
				$paged_context             = [
					'posts_data' => $this->context['posts_data'],
				];
				$paged_context['settings'] = get_option( 'planet4_options' );

				foreach ( $this->paged_posts as $index => $post ) {
					$paged_context['post'] = $post;
					if ( 0 === $index % self::POSTS_PER_LOAD ) {
						$paged_context['first_of_the_page'] = true;
					} else {
						$paged_context['first_of_the_page'] = false;
					}
					Timber::render( [ 'tease-search.twig' ], $paged_context );
				}
			}
		}

		/**
		 * Load assets only on the search page.
		 */
		public function enqueue_public_assets() {
			if ( is_search() ) {
				wp_register_script( 'search', get_template_directory_uri() . '/assets/js/search.js', [ 'jquery' ], '0.2.3', true );
				wp_localize_script( 'search', 'localizations', $this->localizations );
				wp_enqueue_script( 'search' );
			}
		}
	}
}
