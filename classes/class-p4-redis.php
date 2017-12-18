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
			if ( function_exists( 'wp_cache_flush' ) ) {
				add_action( 'admin_bar_menu', array( $this, 'add_flush_cache_button' ), 100 );
			}
			add_action( 'admin_bar_menu', array( $this, 'add_check_cache_button' ), 101 );
		}

		/**
		 * Adds a flush cache button in the WP admin bar to delete all keys in Redis database.
		 *
		 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
		 */
		public function add_flush_cache_button( $wp_admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( isset( $_GET['flush-cache-button'] )
			     && 'flush' === $_GET['flush-cache-button']
			     && wp_verify_nonce( $_GET['_wpnonce'], 'flush-cache-button' )
			) {
				wp_cache_flush();
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-success is-dismissible"><p>Object Cache flushed.</p></div>';
				} );
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
			$wp_admin_bar->add_node( $args );
		}

		/**
		 * Adds a check cache button in the WP admin bar to check connectivity with the Redis server.
		 *
		 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
		 */
		public function add_check_cache_button( $wp_admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( isset( $_GET['check-cache-button'] )
			     && 'check-cache' === $_GET['check-cache-button']
			     && wp_verify_nonce( $_GET['_wpnonce'], 'check-cache-button' )
			) {
				$this->admin_notice_check();
			}

			$dashboard_url = admin_url( add_query_arg( 'check-cache-button', 'check-cache', 'index.php' ) );
			$args = [
				'id'    => 'check_cache_button',
				'title' => 'Check Object Cache',
				'href'  => wp_nonce_url( $dashboard_url, 'check-cache-button' ),
				'meta'  => [
					'class' => 'check-cache-button',
				],
			];
			$wp_admin_bar->add_node( $args );
		}

		/**
		 * Checks connectivity to the Redis server and adds an error or success admin notice.
		 */
		public function admin_notice_check() {
			if ( is_admin() ) {
				$messages = [];
				$info     = wp_redis_get_info();

				if ( $info instanceof WP_Error ) {
					if ( $info->errors['wp-redis'] ) {
						foreach ( $info->errors['wp-redis'] as $index => $error ) {
							$messages[] = $index + 1 . ') ' . $error;
						}
						$class = 'notice-error';
					}
				} elseif ( 'connected' === $info['status'] ) {
					$messages[0] = __( 'Planet 4 is connected to Redis.', 'planet4-master-theme' );
					$class       = 'notice-success';
				}

				if ( $messages ) {
					foreach ( $messages as $message ) {
						?>
						<div class="notice is-dismissible <?php echo esc_html( $class ); ?>">
							<p><?php echo esc_html( $message ); ?></p>
						</div>
						<?php
					}
				}
			}
		}
	}
}
