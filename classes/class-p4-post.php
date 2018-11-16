<?php

use Timber\Post as TimberPost;
use Timber\Term as TimberTerm;

if ( ! class_exists( 'P4_Post' ) ) {

	/**
	 * Class P4_Post extends TimberPost to add planet4 specific functionality.
	 */
	class P4_Post extends TimberPost {

		/** @var array $issues_nav_data */
		protected $issues_nav_data;
		/** @var string $content_type */
		protected $content_type;
		/** @var TimberTerm[] $page_types */
		protected $page_types;

		/**
		 * Checks if post is the act page.
		 *
		 * @return bool
		 */
		public function is_act_page() : bool {
			$act_page_id = planet4_get_option( 'act_page' );

			return absint( $act_page_id ) === $this->id;
		}

		/**
		 * Checks if post is the explore page.
		 *
		 * @return bool
		 */
		public function is_explore_page() : bool {
			$explore_page_id = planet4_get_option( 'explore_page' );

			return absint( $explore_page_id ) === $this->id;
		}

		/**
		 * Checks if post is a take action page (child of act page).
		 *
		 * @return bool
		 */
		public function is_take_action_page() : bool {
			$act_page_id = planet4_get_option( 'act_page' );
			$pages       = [];

			if ( 0 !== absint( $act_page_id ) ) {
				$take_action_pages_args = [
					'post_type'        => 'page',
					'post_parent'      => $act_page_id,
					'numberposts'      => - 1,
					'fields'           => 'ids',
					'suppress_filters' => false,
				];

				$pages = get_posts( $take_action_pages_args );
			}

			return in_array( $this->id, $pages );
		}

		/**
		 * Loads in context information on the navigation links for Issue pages relevant to current Post's categories.
		 */
		public function set_issues_links() {
			// Retrieve P4 settings in order to check that we add only categories that are children of the Issues category.
			$options         = get_option( 'planet4_options' );
			$explore_page_id = $options['explore_page'] ?? '';
			$categories      = get_the_category( $this->ID );

			// Handle navigation links.
			if ( $categories ) {
				$categories_ids = [];

				foreach ( $categories as $category ) {
					$categories_ids[] = $category->term_id;
				}
				// Get the Issue pages that are relevant to the Categories of the current Post.
				if ( $categories_ids && $explore_page_id ) {
					$args = [
						'post_parent'  => $explore_page_id,
						'post_type'    => 'page',
						'post_status'  => 'publish',
					];

					$args['category__in'] = $categories_ids;
					$issues = ( new WP_Query( $args ) )->posts;

					if ( $issues ) {
						foreach ( $issues as $issue ) {
							if ( $issue && $this->post_parent !== (int) $explore_page_id ) {
								$this->issues_nav_data[] = [
									'name' => $issue->post_title,
									'link' => get_permalink( $issue ),
								];
							}
						}
					}
				}
			}
		}

		/**
		 * Retrieves the accounts for each social media item found within the footer social menu.
		 *
		 * @param array $social_menu Array of a post objects for each menu item.
		 *
		 * @return array Associative array with the social media accounts.
		 */
		public function get_social_accounts( $social_menu ) : array {
			$social_accounts = [];
			if ( isset( $social_menu ) && is_iterable( $social_menu ) ) {

				$brands = [
					'facebook',
					'twitter',
					'youtube',
					'instagram',
				];
				foreach ( $social_menu as $social_menu_item ) {
					$url_parts = explode( '/', rtrim( $social_menu_item->url, '/' ) );
					foreach ( $brands as $brand ) {
						if ( false !== strpos( $social_menu_item->url, $brand ) ) {
							$social_accounts[ $brand ] = count( $url_parts ) > 0 ? $url_parts[ count( $url_parts ) - 1 ] : '';
						}
					}
				}
			}

			return $social_accounts;
		}

		/**
		 * Get post's planet4 custom taxonomy terms.
		 *
		 * @return WP_Term[]
		 */
		public function get_custom_terms() {
			$terms = get_the_terms( $this->id, P4_Custom_Taxonomy::TAXONOMY );
			if ( false !== $terms && ! $terms instanceof WP_Error ) {
				return $terms;
			}

			return [];
		}

		/**
		 * Get post's author override status.
		 *
		 * @return bool
		 */
		public function get_author_override() {
			$author_override = get_post_meta( $this->id, 'p4_author_override', true );
			if ( $author_override ) {
				return true;
			}
			return false;
		}

		/**
		 * Sets the page types for this P4_Post.
		 */
		public function set_page_types() {
			$taxonomies = $this->get_terms( P4_Custom_Taxonomy::TAXONOMY );

			if ( $taxonomies && ! is_wp_error( $taxonomies ) ) {
				$this->page_types = $taxonomies;
			}
		}

		/**
		 * Gets the page types of this P4_Post.
		 */
		public function get_page_types() {
			return $this->page_types;
		}

		/**
		 * Sets post/page custom planet4 type.
		 * ACTION, DOCUMENT, PAGE, POST
		 */
		public function set_content_type() {
			switch ( $this->post_type ) {
				case 'page':
					if ( $this->is_take_action_page() ) {
						$this->content_type = __( 'ACTION', 'planet4-master-theme' );
					} else {
						$this->content_type = __( 'PAGE', 'planet4-master-theme' );
					}
					break;
				case 'attachment':
					$this->content_type = __( 'DOCUMENT', 'planet4-master-theme' );
					break;
				default:
					$this->content_type = __( 'POST', 'planet4-master-theme' );
			}
		}

		/**
		 * Get post/page custom planet4 type.
		 * ACTION, DOCUMENT, PAGE, POST
		 *
		 * @return string
		 */
		public function get_content_type() {
			return $this->content_type;
		}

		/**
		 * Get value for open graph title meta.
		 *
		 * @return string
		 */
		public function get_og_title() {
			$og_title = get_post_meta( $this->id, 'p4_og_title', true );
			if ( '' === $og_title ) {
				if ( '' !== $this->post_title ) {
					return $this->post_title . ' - ' . get_bloginfo( 'name' );
				} else {
					return get_bloginfo( 'name' );
				}
			}

			return $og_title;
		}

		/**
		 * Get value for open graph description meta.
		 *
		 * @return string
		 */
		public function get_og_description() {
			$og_desc = get_post_meta( $this->id, 'p4_og_description', true );
			if ( '' === $og_desc ) {
				return $this->post_excerpt;
			}

			return $og_desc;
		}

		/**
		 * Get image data for open graph image meta.
		 *
		 * @return array
		 */
		public function get_og_image() {
			$meta        = get_post_meta( $this->id );
			$image_id    = null;
			$image_metas = [ 'p4_og_image_id', 'background_image_id', '_thumbnail_id' ];
			foreach ( $image_metas as $image_meta ) {
				if ( isset( $meta[ $image_meta ][0] ) ) {
					$image_id = $meta[ $image_meta ][0];
					break;
				}
			}

			if ( null !== $image_id ) {
				$image_data = wp_get_attachment_image_src( $image_id, 'full' );

				return $image_data;
			}

			return [];
		}

		/**
		 * Get post's author override status.
		 *
		 * @return bool
		 */
		public function get_author_override() {
			$author_override = get_post_meta( $this->id, 'p4_author_override', true );
			if ( $author_override ) {
				return true;
			}
			return false;
		}

		/**
		 * Overrides parent function author in case `author_override` is set,
		 * returns a fake author mimicking the interface of \Timber\User.
		 *
		 * @return P4_FakeUser()
		 */
		public function author() {
			$author_override = get_post_meta( $this->id, 'p4_author_override', true );
			if ($author_override) {
				return new P4_FakeUser($author_override);
			} else {
				return parent::author();
			}
		}
	}
}
