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
	}
}
