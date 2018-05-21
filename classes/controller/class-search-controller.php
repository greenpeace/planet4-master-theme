<?php

namespace P4ML\Controllers;

if ( ! class_exists( 'Search_Controller' ) ) {

	/**
	 * Class Search_Controller
	 *
	 * @package P4ML\Controllers
	 */
	class Search_Controller {

		const POSTS_LIMIT           = 300;
		const POSTS_PER_PAGE        = 10;
		const POSTS_PER_LOAD        = 5;
		const SHOW_SCROLL_TIMES     = 2;
		const DEFAULT_SORT          = 'relevant';

		/** @var string $search_query */
		protected $search_query;
		/** @var array|bool|null $posts */
		protected $posts;
		/** @var array|bool|null $paged_posts */
		protected $paged_posts;
		/** @var array $selected_sort */
		protected $selected_sort;
		/** @var array $filters */
		protected $filters;
		/** @var array $localizations */
		protected $localizations;
		/** @var array $templates */
		public $templates;
		/** @var array $context */
		public $context;
		/** @var int $current_page */
		public $current_page;

		/**
		 * Search_Controller constructor.
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
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		}

		/**
		 * Conducts the actual search.
		 *
		 * @param string     $search_query The searched term.
		 * @param string     $selected_sort The selected order_by.
		 * @param array      $filters The selected filters.
		 * @param array      $templates An indexed array with template file names. The first to be found will be used.
		 * @param array|null $context An associative array with all the context needed to render the template found first.
		 */
		public function load( $search_query, $selected_sort = self::DEFAULT_SORT, $filters = [], $templates = [ 'search.twig', 'archive.twig', 'index.twig' ], $context = null ) {
			$this->initialize();
			$this->search_query = $search_query;
			$this->templates    = $templates;

			// TODO: Implement load() method.
		}

		/**
		 * Load assets only on the search page.
		 */
		public function enqueue_public_assets() {
			if ( is_search() ) {
				wp_register_script( 'search', get_template_directory_uri() . '/assets/js/search.js', array( 'jquery' ), '0.1.9', true );
				wp_localize_script( 'search', 'localizations', $this->localizations );
				wp_enqueue_script( 'search' );
			}
		}
	}
}
