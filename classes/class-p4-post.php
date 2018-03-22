<?php


if ( ! class_exists( 'P4_Post' ) ) {

	/**
	 * Class P4_Post extends Timber\Post to add planet4 specific functionality.
	 */
	class P4_Post extends \Timber\Post {

		/**
		 * Checks if post is the act page.
		 * @return boolean
		 */
		public function is_act_page() {
			$act_page_id = planet4_get_option( 'act_page' );

			return $this->id === absint( $act_page_id );
		}

		/**
		 * Checks if post is the explore page.
		 * @return boolean
		 */
		public function is_explore_page() {
			$explore_page_id = planet4_get_option( 'explore_page' );

			return $this->id === absint( $explore_page_id );
		}

		/**
		 * Checks if post is a take action page (child of act page).
		 * @return boolean
		 */
		public function is_take_action_page() {
			$act_page_id = planet4_get_option( 'act_page' );
			$pages       = [];

			if ( 0 !== absint( $act_page_id ) ) {
				$take_action_pages_args = [
					'post_type'   => 'page',
					'post_parent' => $act_page_id,
					'numberposts' => - 1,
					'fields'      => 'ids'
				];

				$pages = get_posts( $take_action_pages_args );
			}

			return in_array( $this->id, $pages );
		}


		/**
		 * Loads in context information on the navigation links for Issue pages relevant to current Post's categories.
		 *
		 * @param array $context An indexed array with all data needed to render current page.
		 */
		public function load_nav_issues_links( &$context ) {
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
					$args   = [
						'post_parent'  => $explore_page_id,
						'post_type'    => 'page',
						'post_status'  => 'publish',
					];
					if ( count( $categories_ids ) > 1 ) {
						$args['category__in'] = $categories_ids;
					} elseif ( 1 === count( $categories_ids ) ) {
						$args['cat'] = (int) $categories_ids[0];
					}
					$issues = ( new WP_Query( $args ) )->posts;

					if ( $issues ) {
						foreach ( $issues as $issue ) {
							if ( $issue && $this->post_parent !== (int) $explore_page_id ) {
								$context['issues'][] = [
									'name' => $issue->post_title,
									'link' => get_permalink( $issue ),
								];
							}
						}
					}
				}
			}
		}
	}
}
