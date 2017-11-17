<?php

use Timber\Timber;

if ( ! class_exists( 'P4_Search' ) ) {

	/**
	 * Class P4_Search
	 */
	class P4_Search {

		/** @var string $search_query */
		protected $search_query;
		/** @var array $templates */
		public $templates;
		/** @var array $context */
		public $context;
		/** @var array|bool|null $posts */
		protected $posts;

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
				$this->context  = Timber::get_context();
				$this->posts    = Timber::get_posts();
				$this->set_context( $this->context );
			}
		}

		/**
		 * Sets the P4 Search page context.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function set_context( &$context ) {
			$this->add_general_context( $context );
			$this->add_items_context( $context );
			$this->add_filters_context( $context );
		}

		/**
		 * Adds the general context for the Search page.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function add_general_context( &$context ) {
			global $wp_query;

			/*
			 * With no args passed to this call, Timber uses the main query which we filter for customisations via P4_Master_Site class.
			 *
			 * When customising this query, use filters on the main query to avoid bypassing SearchWP's handling of the query.
			 */
			$context['posts']        = $this->posts;
			$context['search_query'] = $this->search_query;
			$context['found_posts']  = $wp_query->found_posts;

			$selected_sort = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
			if ( ! in_array( $selected_sort, array_keys( $context['sort_options'] ), true ) ) {
				$context['selected_sort'] = P4_Master_Site::DEFAULT_SORT;
			} else {
				$context['selected_sort'] = $selected_sort;
			}

			// Footer Items.
			$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
			$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
			$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
			$context['copyright_text']        = get_option( 'copyright', '' );
			$context['page_category']         = $category->name ?? __( 'Search page', 'planet4-master-theme' );
		}

		/**
		 * Adds the context for each Search result item.
		 *
		 * @param array $context Associative array with the data to be passed to the view.
		 */
		protected function add_items_context( &$context ) {

			foreach ( $this->posts as $post ) {
				switch ( $post->post_type ) {
					case 'page':
						if ( 'act' === basename( get_permalink( $post->post_parent ) ) ) {
							$content_type_text = __( 'ACTION', 'planet4-master-theme' );
							$content_type = 'action';
						} else {
							$content_type_text = __( 'PAGE', 'planet4-master-theme' );
							$content_type = 'page';
						}
						break;
					case 'attachment':
						$content_type_text = __( 'DOCUMENT', 'planet4-master-theme' );
						$content_type = 'document';
						break;
					default:
						$content_type_text = __( 'POST', 'planet4-master-theme' );
						$content_type = 'post';
				}

				$page_types = get_the_terms( $post->ID, 'p4-page-type' );

				$tags = get_the_terms( $post->ID, 'post_tag' );

				$context['posts_data'][ $post->ID ] = [
					'content_type_text' => $content_type_text,
					'content_type'      => $content_type,
					'page_types'        => $page_types,
				];
				foreach ( $tags as $tag ) {
					$context['posts_data'][ $post->ID ]['tags'][] = [
						'name' => $tag->name,
						'link' => get_tag_link( $tag ),
					];
				}
			}
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

			$categories = get_categories( [
				'child_of' => get_category_by_slug( 'issues' )->term_id,
				'orderby'  => 'name',
				'order'    => 'ASC',
			] );

			foreach ( $categories as $category ) {
				$context['issues'][] = [
					'name'    => $category->name,
					'results' => 0,
				];
			}

			$context['campaigns'] = [
				[
					'name'    => '#CampaignName1',
					'results' => 0,
				],
				[
					'name'    => '#CampaignName2',
					'results' => 0,
				],
				[
					'name'    => '#CampaignName3',
					'results' => 0,
				],
				[
					'name'    => '#CampaignName4',
					'results' => 0,
				],
			];
			$context['page_types'] = [
				[
					'name'    => 'Press Release',
					'results' => 0,
				],
				[
					'name'    => 'Publication',
					'results' => 0,
				],
				[
					'name'    => 'Story',
					'results' => 0,
				],
			];
			$context['content_types'] = [
				[
					'name'    => 'Action',
					'results' => 0,
				],
				[
					'name'    => 'Document',
					'results' => 0,
				],
				[
					'name'    => 'Page',
					'results' => 0,
				],
				[
					'name'    => 'Post',
					'results' => 0,
				],
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
