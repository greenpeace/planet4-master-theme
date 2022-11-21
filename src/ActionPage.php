<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Features\ActionPostType;

/**
 * Class P4\MasterTheme\ActionPage
 */
class ActionPage {

	public const POST_TYPE      = 'p4_action';
	public const POST_TYPE_SLUG = 'action';

	public const TAXONOMY           = 'action-type';
	public const TAXONOMY_PARAMETER = 'action_type';
	public const TAXONOMY_SLUG      = 'action-type';

	public const META_FIELDS = [
		'nav_type',
		'p4_hide_page_title_checkbox',
		'p4_og_title',
		'p4_og_description',
		'p4_og_image',
		'p4_og_image_id',
		'p4_seo_canonical_url',
	];

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
		add_action( 'load-options-permalink.php', [ $this, 'p4_load_permalinks' ] );

		// Flush and regenerate rewrite rules on taxonomy change.
		add_action( 'created_term', [ $this, 'trigger_rewrite_rules' ], 10, 3 );
		add_action( 'edited_term', [ $this, 'trigger_rewrite_rules' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'trigger_rewrite_rules' ], 10, 3 );

		// Rewrites the permalink to this taxonomy's page.
		add_filter( 'term_link', [ $this, 'filter_term_permalink' ], 10, 3 );
		add_filter( 'post_rewrite_rules', [ $this, 'replace_taxonomy_terms_in_rewrite_rules' ], 10, 1 );
		add_filter( 'root_rewrite_rules', [ $this, 'add_terms_rewrite_rules' ], 10, 1 );
	}

	/**
	 * Register Action page post type.
	 */
	public function register_post_type() {

		// IA: display action page type in admin sidebar.
		$enable_action_post_type = ActionPostType::is_active();
		// Use a custom action slug if added on permalink page, else use a default.
		$action_slug = get_option( 'p4_action_posttype_slug' );
		if ( ! $action_slug ) {
			$action_slug = self::POST_TYPE_SLUG;
		}

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
				'slug'       => $action_slug,
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

		$options = get_option( 'planet4_options' );

		register_post_meta(
			self::POST_TYPE,
			'action_button_text',
			array_merge( $args, [ 'default' => $options['take_action_covers_button_text'] ?? __( 'Take action', 'planet4-master-theme' ) ] )
		);

		foreach ( self::META_FIELDS as $field ) {
			register_post_meta( self::POST_TYPE, $field, $args );
		}
	}

	/**
	 * On load of permalinks page, add a action slug setting field.
	 */
	public function p4_load_permalinks() {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['p4_action_posttype_slug'] ) ) {
			update_option( 'p4_action_posttype_slug', sanitize_title_with_dashes( $_POST['p4_action_posttype_slug'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Add a settings field to the permalink page.
		add_settings_field( 'p4_action_posttype_slug', __( 'Action post type slug', 'planet4-master-theme-backend' ), [ $this, 'add_action_slug_field' ], 'permalink', 'optional' );
	}

	/**
	 * Add Action slug text field on permalinks page.
	 */
	public function add_action_slug_field() {

		$value = get_option( 'p4_action_posttype_slug' );
		echo '<input type="text" value="' . esc_attr( $value ) . '" name="p4_action_posttype_slug" id="p4_action_posttype_slug" class="regular-text" /><p>' . esc_html__( 'The default Action post type slug is "action".', 'planet4-master-theme-backend' ) . '</p>';
	}

	/**
	 * Filter for term_link.
	 *
	 * @param string $link     The link value.
	 * @param string $term     The term passed to the filter (unused).
	 * @param string $taxonomy Taxonomy of the given link.
	 *
	 * @return string The filtered permalink for this taxonomy.
	 */
	public function filter_term_permalink( $link, $term, $taxonomy ) {
		if ( self::TAXONOMY !== $taxonomy ) {
			return $link;
		}

		return str_replace( self::TAXONOMY_SLUG . '/', '', $link );
	}

	/**
	 * Filter for post_rewrite_rules.
	 *
	 * @param array $rules   Post rewrite rules passed by WordPress.
	 *
	 * @return array        The filtered post rewrite rules.
	 */
	public function replace_taxonomy_terms_in_rewrite_rules( $rules ) {
		// Get planet4 page type taxonomy terms.
		$term_slugs = $this->get_terms_slugs();

		if ( $term_slugs ) {
			$terms_slugs_regex = implode( '|', $term_slugs );

			$new_rules = [];
			foreach ( $rules as $match => $rule ) {
				$new_match               = str_replace( '%' . self::TAXONOMY_PARAMETER . '%', "($terms_slugs_regex)", $match );
				$new_rule                = str_replace( '%' . self::TAXONOMY_PARAMETER . '%', self::TAXONOMY . '=', $rule );
				$new_rules[ $new_match ] = $new_rule;
			}

			return $new_rules;
		}

		return $rules;
	}

	/**
	 * Add each taxonomy term as a root rewrite rule.
	 * Filter hook for root_rewrite_rules.
	 *
	 * @param array $rules  Root rewrite rules passed by WordPress.
	 *
	 * @return array        The filtered root rewrite rules.
	 */
	public function add_terms_rewrite_rules( $rules ) {
		// Add a rewrite rule for each slug of this taxonomy type (e.g.: "petition", "event", etc.)
		// for action type pages.
		// e.g | petition/?$ | index.php?action-type=petition | .
		$terms_slugs = $this->get_terms_slugs();

		if ( $terms_slugs ) {
			foreach ( $terms_slugs as $slug ) {
				$rules[ $slug . '/?$' ] = 'index.php?' . self::TAXONOMY . '=' . $slug;
			}
		}

		return $rules;
	}

	/**
	 * Get the slugs for all terms in this taxonomy.
	 *
	 * @return array Flat array containing the slug for every term.
	 */
	private function get_terms_slugs() : array {
		// Get planet4 action type taxonomy terms.
		$terms = $this->get_all_terms();

		if ( ! is_wp_error( $terms ) ) {
			$term_slugs = [];
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$term_slugs[] = $term->slug;
				}
			}

			return $term_slugs;
		}

		return [];
	}

	/**
	 * Get all taxonomy's terms, despite if wpml plugin is activated.
	 *
	 * @return array|int|WP_Error
	 */
	public function get_all_terms() {
		// Get taxonomy terms if wpml plugin is installed and activated.
		if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			return $this->get_multilingual_terms();
		}
		return $this->get_terms();
	}

	/**
	 * Get all taxonomy's terms (for all languages available) if wpml is enabled.
	 *
	 * @return WP_Term[]
	 */
	public function get_multilingual_terms() {

		$all_terms           = [];
		$current_lang        = apply_filters( 'wpml_current_language', null );
		$available_languages = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );

		foreach ( $available_languages as $lang ) {
			do_action( 'wpml_switch_language', $lang['language_code'] );
			$terms = get_terms(
				[
					'fields'     => 'all',
					'hide_empty' => false,
					'taxonomy'   => self::TAXONOMY,
				]
			);
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$all_terms = array_merge( $all_terms, $terms );
			}
		}

		do_action( 'wpml_switch_language', $current_lang );

		return $all_terms;
	}

	/**
	 * Get taxonomy's terms.
	 *
	 * @return array|int|WP_Error
	 */
	public function get_terms() {
		// Get planet4 action type taxonomy terms.
		return get_terms(
			[
				'fields'     => 'all',
				'hide_empty' => false,
				'taxonomy'   => self::TAXONOMY,
			]
		);
	}

	/**
	 * Flush and regenerate rewrite rules when a new action_type is created/edited/deleted.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function trigger_rewrite_rules( $term_id, $tt_id, $taxonomy ) {
		if ( self::TAXONOMY !== $taxonomy ) {
			return;
		}

		flush_rewrite_rules();
	}
}
