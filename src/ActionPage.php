<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Features\ActionPostType;
use P4\MasterTheme\Settings\InformationArchitecture as IA;

/**
 * Class P4\MasterTheme\ActionPage
 */
class ActionPage {

	public const POST_TYPE      = 'p4_action';
	public const POST_TYPE_SLUG = 'action';

	public const TAXONOMY           = 'action-type';
	public const TAXONOMY_PARAMETER = 'action_type';
	public const TAXONOMY_SLUG      = 'action-type';

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
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'register_post_meta' ] );
		add_action( 'init', [ $this, 'register_taxonomy' ], 2 );
	}

	/**
	 * Register Action page post type.
	 */
	public function register_post_type() {

		// IA: display action page type in admin sidebar.
		$enable_action_post_type = ActionPostType::is_active();

		$labels = [
			'name'               => _x( 'Actions', 'post type general name', 'planet4-master-theme-backend' ),
			'singular_name'      => _x( 'Action', 'post type singular name', 'planet4-master-theme-backend' ),
			'menu_name'          => _x( 'Actions', 'admin menu', 'planet4-master-theme-backend' ),
			'name_admin_bar'     => _x( 'Actions', 'add new on admin bar', 'planet4-master-theme-backend' ),
			'add_new'            => _x( 'Add New', 'action', 'planet4-master-theme-backend' ),
			'add_new_item'       => __( 'Add New Action', 'planet4-master-theme-backend' ),
			'new_item'           => __( 'New Action', 'planet4-master-theme-backend' ),
			'edit_item'          => __( 'Edit Action', 'planet4-master-theme-backend' ),
			'view_item'          => __( 'View Action', 'planet4-master-theme-backend' ),
			'all_items'          => __( 'All Actions', 'planet4-master-theme-backend' ),
			'search_items'       => __( 'Search Actions', 'planet4-master-theme-backend' ),
			'parent_item_colon'  => __( 'Parent Action:', 'planet4-master-theme-backend' ),
			'not_found'          => __( 'No actions found.', 'planet4-master-theme-backend' ),
			'not_found_in_trash' => __( 'No actions found in Trash.', 'planet4-master-theme-backend' ),
		];

		$args = [
			'labels'             => $labels,
			'description'        => __( 'Use Actions to inspire your website\'s users to take action on issues and campaigns they care about!', 'planet4-master-theme-backend' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => $enable_action_post_type,
			'query_var'          => true,
			'rewrite'            => [
				'slug'       => self::POST_TYPE_SLUG,
				'with_front' => false,
			],
			'has_archive'        => true,
			'hierarchical'       => false,
			'show_in_nav_menus'  => true,
			'menu_position'      => 21,
			'menu_icon'          => 'dashicons-editor-textcolor',
			'show_in_rest'       => true,
			'taxonomies'         => [ 'category', 'post_tag' ],
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

	/**
	 * Register a custom taxonomy for Action page post types.
	 */
	public function register_taxonomy() {

		$labels = [
			'name'              => _x( 'Action Type', 'taxonomy general name', 'planet4-master-theme-backend' ),
			'singular_name'     => _x( 'Action Type', 'taxonomy singular name', 'planet4-master-theme-backend' ),
			'search_items'      => __( 'Search in Action Type', 'planet4-master-theme-backend' ),
			'all_items'         => __( 'All Action Types', 'planet4-master-theme-backend' ),
			'most_used_items'   => null,
			'parent_item'       => null,
			'parent_item_colon' => null,
			'edit_item'         => __( 'Edit Action Type', 'planet4-master-theme-backend' ),
			'update_item'       => __( 'Update Action Type', 'planet4-master-theme-backend' ),
			'add_new_item'      => __( 'Add new Action Type', 'planet4-master-theme-backend' ),
			'new_item_name'     => __( 'New Action Type', 'planet4-master-theme-backend' ),
			'menu_name'         => __( 'Action Type', 'planet4-master-theme-backend' ),
		];

		$args = [
			'hierarchical'      => true,
			'labels'            => $labels,
			'rewrite'           => [
				'slug' => self::TAXONOMY_SLUG,
			],
			'show_in_rest'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
		];

		register_taxonomy( self::TAXONOMY, [ self::TAXONOMY_PARAMETER, self::POST_TYPE ], $args );
		register_taxonomy_for_object_type( self::TAXONOMY, self::POST_TYPE );
	}

	/**
	 * Register Action page post meta data.
	 */
	public function register_post_meta() {
		$args = [
			'show_in_rest' => true,
			'type'         => 'string',
			'single'       => true,
		];

		register_post_meta( self::POST_TYPE, 'nav_type', $args );
	}
}
