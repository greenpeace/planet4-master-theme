<?php
/**
 * Post report controller
 *
 * @package P4MT
 */

if ( ! class_exists( 'P4_Post_Report_Controller' ) ) {

	/**
	 * Class P4_Post_Report_Controller
	 */
	class P4_Post_Report_Controller {

		/**
		 * Theme directory
		 *
		 * @var string $theme_dir Theme's base directory.
		 */
		protected $theme_dir;

		/**
		 * P4_Custom_Taxonomy constructor.
		 */
		public function __construct() {
			$this->hooks();
			$this->theme_dir = get_template_directory_uri();
		}

		/**
		 * Register actions for WordPress hooks and filters.
		 */
		private function hooks() {
			add_action( 'admin_menu', [ $this, 'add_posts_report_admin_menu_item' ] );
			add_filter( 'rest_post_query', [ $this, 'add_posts_param_to_endpoint' ], 10, 2 );
			add_filter( 'rest_page_query', [ $this, 'add_posts_param_to_endpoint' ], 10, 2 );
			add_filter( 'rest_post_collection_params', [ $this, 'filter_post_params_endpoint' ] );
			add_filter( 'rest_page_collection_params', [ $this, 'filter_post_params_endpoint' ] );
		}

		/**
		 * Add extra date column in post rest endpoint.
		 *
		 * @param array $args Arguments array passed to endpoint.
		 * @param array $request Initial request parameters to the endpoint.
		 *
		 * @return mixed
		 */
		function add_posts_param_to_endpoint( $args, $request ) {
			if ( ! isset( $request['before'] ) && ! isset( $request['after'] ) ) {
				return $args;
			}

			if ( isset( $request['date_query_column'] ) ) {
				$args['date_query'][0]['column'] = $request['date_query_column'];
			}

			return $args;
		}

		/**
		 * Add post report submenu item.
		 */
		function add_posts_report_admin_menu_item() {
			add_posts_page(
				__( 'Posts Report', 'planet4-master-theme-backend' ),
				__( 'Posts Report', 'planet4-master-theme-backend' ),
				'read',
				'posts-report',
				[ $this, 'render_posts_report_page' ]
			);
		}

		/**
		 * Use date_query_column added in post rest endpoint in order to query on posts/pages modified attribute.
		 *
		 * @param array $query_params Rest endpoint query parameters.
		 *
		 * @return mixed
		 */
		function filter_post_params_endpoint( $query_params ) {
			$query_params['date_query_column'] = [
				'description' => __( 'The date query column.', 'planet4-master-theme-backend' ),
				'type'        => 'string',
				'enum'        => [ 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ],
			];

			return $query_params;
		}

		/**
		 * Callback function to render posts report page.
		 */
		public function render_posts_report_page() {
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_register_script(
				'posts-report',
				$this->theme_dir . '/admin/js/posts_report.js',
				[
					'jquery',
					'wp-api',
					'wp-backbone',
				],
				'0.2',
				true
			);
			wp_localize_script(
				'posts-report',
				'p4_data',
				[
					'api_url' => get_site_url() . '/wp-json/wp/v2',
					'nonce'   => wp_create_nonce( 'wp_rest' ),
				]
			);
			wp_enqueue_script( 'posts-report' );
			wp_register_style(
				'jquery-ui-datepicker',
				'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css'
			);
			wp_enqueue_style( 'jquery-ui-datepicker' );
			include dirname( __FILE__ ) . '/../posts-report.php';
		}
	}
}

