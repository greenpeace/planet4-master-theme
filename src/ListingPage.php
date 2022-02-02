<?php

namespace P4\MasterTheme;

/**
 * Class P4\MasterTheme\PostCampaign
 */
class ListingPage {
	public const POST_TYPE = 'listing_page';

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
		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	/**
	 * Register list page post type.
	 */
	public function register_post_type() {
		$labels = [
			'name'               => _x( 'Listing Pages', 'post type general name', 'planet4-master-theme-backend' ),
			'singular_name'      => _x( 'Listing Page', 'post type singular name', 'planet4-master-theme-backend' ),
			'menu_name'          => _x( 'Listing Pages', 'admin menu', 'planet4-master-theme-backend' ),
			'name_admin_bar'     => _x( 'Listing Pages', 'add new on admin bar', 'planet4-master-theme-backend' ),
			'add_new'            => _x( 'Add New', 'campaign', 'planet4-master-theme-backend' ),
			'add_new_item'       => __( 'Add New Listing Page', 'planet4-master-theme-backend' ),
			'new_item'           => __( 'New Listing Page', 'planet4-master-theme-backend' ),
			'edit_item'          => __( 'Edit Listing Page', 'planet4-master-theme-backend' ),
			'view_item'          => __( 'View Listing Page', 'planet4-master-theme-backend' ),
			'all_items'          => __( 'All Listing Pages', 'planet4-master-theme-backend' ),
			'search_items'       => __( 'Search Listing Pages', 'planet4-master-theme-backend' ),
			'parent_item_colon'  => __( 'Parent Listing Page:', 'planet4-master-theme-backend' ),
			'not_found'          => __( 'No Listing Pages found.', 'planet4-master-theme-backend' ),
			'not_found_in_trash' => __( 'No Listing Pages found in Trash.', 'planet4-master-theme-backend' ),
		];

		$args = [
			'labels'             => $labels,
			'description'        => __( 'Listing Pages', 'planet4-master-theme-backend' ),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => [ 'campaign', 'campaigns' ],
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'hierarchical'       => true,
			'show_in_nav_menus'  => true,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-excerpt-view',
			'show_in_rest'       => true,
			'supports'           => [
				'page-attributes',
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'revisions',
				// Required to expose meta fields in the REST API.
				'custom-fields',
			],
		];
		register_post_type( self::POST_TYPE, $args );
	}
}
