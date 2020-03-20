<?php
/**
 * A custom post type for P3 posts that were archived.
 *
 * @package P4MT
 */

/**
 * A custom post type for P3 posts that were archived.
 */
class P4_Post_Archive {

	public const POST_TYPE = 'archive';

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Class hooks.
	 */
	private function hooks() {
		add_action( 'init', [ $this, 'register_archive_cpt' ] );
		add_action( 'add_meta_boxes_archive', [ $this, 'add_archive_link' ] );
	}

	/**
	 * Register the custom post type.
	 */
	public function register_archive_cpt() {
		$args = [
			'labels'             => [
				'name'               => _x( 'Archive', 'post type general name', 'planet4-master-theme-backend' ),
				'singular_name'      => _x( 'Archive', 'post type singular name', 'planet4-master-theme-backend' ),
				'menu_name'          => _x( 'Archive', 'admin menu', 'planet4-master-theme-backend' ),
				'name_admin_bar'     => _x( 'Archive', 'add new on admin bar', 'planet4-master-theme-backend' ),
				'add_new'            => _x( 'Add New', 'archive', 'planet4-master-theme-backend' ),
				'add_new_item'       => __( 'Add New archive', 'planet4-master-theme-backend' ),
				'new_item'           => __( 'New archive', 'planet4-master-theme-backend' ),
				'edit_item'          => __( 'Edit archive', 'planet4-master-theme-backend' ),
				'view_item'          => __( 'View archive', 'planet4-master-theme-backend' ),
				'all_items'          => __( 'All archived posts', 'planet4-master-theme-backend' ),
				'search_items'       => __( 'Search archives', 'planet4-master-theme-backend' ),
				'parent_item_colon'  => __( 'Parent archive:', 'planet4-master-theme-backend' ),
				'not_found'          => __( 'No archives found.', 'planet4-master-theme-backend' ),
				'not_found_in_trash' => __( 'No archives found in Trash.', 'planet4-master-theme-backend' ),
			],
			'description'        => __( 'Archive', 'planet4-master-theme-backend' ),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'show_in_nav_menus'  => false,
			'menu_icon'          => 'dashicons-archive',
			'menu_position'      => 100,
			'supports'           => [
				'title',
				'author',
				'thumbnail',
				'excerpt',
				'revisions',
				'editor',
			],
		];

		register_post_type( self::POST_TYPE, $args );

	}

	/**
	 * Add a link to the internet archive page.
	 */
	public function add_archive_link() {
		add_meta_box(
			'archive-url',
			__( 'Archive URL', 'planet4-master-theme' ),
			function ( $post ) {
				echo "<a target=\"_blank\" href=\"{$post->guid}\">{$post->guid}</a>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			},
			null,
			'side',
			'high'
		);

	}
}
