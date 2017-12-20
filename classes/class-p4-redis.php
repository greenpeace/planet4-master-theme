<?php

if ( ! class_exists( 'P4_Redis' ) ) {

	/**
	 * Class P4_Redis
	 */
	class P4_Redis {

		/**
		 * P4_Redis constructor.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Hooks actions and filters.
		 */
		public function hooks() {
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ), 9 );
		}

		public function add_dashboard_widgets() {
			wp_add_dashboard_widget( 'planet4_dashboard_widget', __( 'Planet 4 Control Panel', 'planet4-master-theme' ), array( $this, 'add_control_panel' ) );
		}

		// Function that outputs the contents of the dashboard widget

		/**
		 * @param $post
		 * @param $callback_args
		 */
		public function add_control_panel( $post, $callback_args ) {
			$this->add_flush_cache_button();
			$this->check_redis();
		}

		/**
		 * Adds a flush cache button in the WP admin bar to delete all keys in Redis database.
		 */
		public function add_flush_cache_button() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( isset( $_GET['flush-cache-button'] )
			     && 'flush' === $_GET['flush-cache-button']
			     && wp_verify_nonce( $_GET['_wpnonce'], 'flush-cache-button' )
			) {
				wp_cache_flush();
				echo '<div class="cp-redis">
							<p>Object Cache flushed.</p>
						</div>';
			}

			$dashboard_url = admin_url( add_query_arg( 'flush-cache-button', 'flush', 'index.php' ) );
			$args = [
				'id'    => 'flush_cache_button',
				'title' => 'Flush Object Cache',
				'href'  => wp_nonce_url( $dashboard_url, 'flush-cache-button' ),
				'meta'  => [
					'class' => 'flush-cache-button',
				],
			];

			echo '<a class="btn btn-flush-cache" href="' . esc_attr( $args['href'] ) . '">' . esc_html( $args['title'] ) . '</a>';
		}

		/**
		 * Checks connectivity to the Redis server and adds an error or success admin notice.
		 */
		public function check_redis() {
			if ( is_admin() ) {
				$messages = [];
				$info     = wp_redis_get_info();

				if ( $info instanceof WP_Error ) {
					if ( $info->errors['wp-redis'] ) {
						foreach ( $info->errors['wp-redis'] as $index => $error ) {
							$messages[] = $index + 1 . ') ' . $error;
						}
						$class = 'cp-notice-error';
					}
				} elseif ( 'connected' === $info['status'] ) {
					$messages[0] = __( 'Planet 4 is connected to Redis.', 'planet4-master-theme' );
					$class       = 'cp-notice-success';
				}

				if ( $messages ) {
					foreach ( $messages as $message ) {
						echo '
						<div class="cp-redis ' . esc_attr( $class ) . '">
							<p>' . esc_html( $message ) . '</p>
						</div>
						';
					}
				}
			}
		}
	}
}
