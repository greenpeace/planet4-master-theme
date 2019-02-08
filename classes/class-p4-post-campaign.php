<?php
/**
 * P4 Post Campaign Template Settings
 *
 * @package P4MT
 */

if ( ! class_exists( 'P4_Post_Campaign' ) ) {
	/**
	 * Class P4_Post_Campaign
	 */
	class P4_Post_Campaign {

		/**
		 * Taxonomy
		 *
		 * @var string $taxonomy
		 */
		private $taxonomy = 'post_tag';

		/** @var string $post_type */
		private $post_type = 'campaigns';

		/**
		 * Page Types
		 *
		 * @var array $page_types
		 */
		public $page_types = [];

		/**
		 * Taxonomy_Image constructor.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {

			add_action( 'init', [ $this, 'register_campaigns_cpt' ] );
			add_action( 'add_meta_boxes', [ $this, 'campaign_page_templates_meta_box' ] );
			add_action( 'save_post_campaigns', [ $this, 'save_campaign_page_templates_meta_box_data' ] );
		}

		/**
		 * Register campaigns cpt
		 */
		public function register_campaigns_cpt() {

			$labels = array(
				'name'               => _x( 'Campaigns', 'post type general name', 'planet4-master-theme-backend' ),
				'singular_name'      => _x( 'Campaign', 'post type singular name', 'planet4-master-theme-backend' ),
				'menu_name'          => _x( 'Campaigns', 'admin menu', 'planet4-master-theme-backend' ),
				'name_admin_bar'     => _x( 'Campaign', 'add new on admin bar', 'planet4-master-theme-backend' ),
				'add_new'            => _x( 'Add New', 'campaign', 'planet4-master-theme-backend' ),
				'add_new_item'       => __( 'Add New Campaign', 'planet4-master-theme-backend' ),
				'new_item'           => __( 'New Campaign', 'planet4-master-theme-backend' ),
				'edit_item'          => __( 'Edit Campaign', 'planet4-master-theme-backend' ),
				'view_item'          => __( 'View Campaign', 'planet4-master-theme-backend' ),
				'all_items'          => __( 'All Campaigns', 'planet4-master-theme-backend' ),
				'search_items'       => __( 'Search Campaigns', 'planet4-master-theme-backend' ),
				'parent_item_colon'  => __( 'Parent Campaigns:', 'planet4-master-theme-backend' ),
				'not_found'          => __( 'No campaigns found.', 'planet4-master-theme-backend' ),
				'not_found_in_trash' => __( 'No campaigns found in Trash.', 'planet4-master-theme-backend' ),
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Campaigns', 'planet4-master-theme-backend' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'campaign' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'taxonomies'         => array( 'category', 'post_tag' ),
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'          => 'dashicons-megaphone',
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
			);

			register_post_type( $this->post_type, $args );
		}

		/**
		 * Add metabox for campaign page template selection on campaigns cpt
		 */
		public function campaign_page_templates_meta_box() {
			add_meta_box(
				'campaigns-page-templates',
				__( 'Campaign Templates', 'planet4-master-theme-backend' ),
				array( $this, 'campaign_page_templates_meta_box_callback' ),
				'campaigns',
				'side'
			);
		}

		/**
		 * Callback function for campaign page template selection
		 *
		 * @param $post
		 */
		public function campaign_page_templates_meta_box_callback( $post ) {

			// Add a nonce field so we can check for it later.
			wp_nonce_field( 'campaign_page_template_nonce_' . $post->ID, 'campaign_page_template_nonce' );

			$value = get_post_meta( $post->ID, '_campaign_page_template', true );

			$campaign_templates = array(
				'antarctic' => __( 'Antarctic', 'planet4-master-theme-backend' ),
				'arctic'    => __( 'Arctic', 'planet4-master-theme-backend' ),
				'forest'    => __( 'Forest', 'planet4-master-theme-backend' ),
				'oceans'    => __( 'Oceans', 'planet4-master-theme-backend' ),
				'oil'       => __( 'Oil', 'planet4-master-theme-backend' ),
				'plastic'   => __( 'Plastics', 'planet4-master-theme-backend' ),
			);
			?>
			<select id="campaign_page_template" name="campaign_page_template">
				<option value=""><?php _e( 'Select Campaign Template', 'planet4-master-theme-backend' ); ?></option>
				<?php
				foreach ( $campaign_templates as $campaign => $campaign_template ) {
					?>
					<option
					value="<?php echo $campaign; ?>" <?php selected( $value, $campaign ); ?>><?php echo $campaign_template; ?></option>
					<?php
				}
				?>
			</select>
			<?php
		}

		/**
		 * Save campaigns page template data
		 *
		 * @param $post_id
		 */
		public function save_campaign_page_templates_meta_box_data( $post_id ) {

			// Check if our nonce is set.
			if ( ! isset( $_POST['campaign_page_template_nonce'] ) ) {
				return;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['campaign_page_template_nonce'], 'campaign_page_template_nonce_' . $post_id ) ) {
				return;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			/* OK, it's safe for us to save the data now. */

			// Make sure that it is set.
			if ( ! isset( $_POST['campaign_page_template'] ) ) {
				return;
			}

			// Sanitize user input.
			$campaign_page_template = sanitize_text_field( $_POST['campaign_page_template'] );

			// Update the meta field in the database.
			update_post_meta( $post_id, '_campaign_page_template', $campaign_page_template );
		}
	}
}
