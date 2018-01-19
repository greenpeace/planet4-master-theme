<?php

use P4EN\Controllers\Ensapi_Controller as ENS_API;

if ( ! class_exists( 'P4_Control_Panel' ) ) {

	/**
	 * Class P4_Control_Panel
	 */
	class P4_Control_Panel {

		/**
		 * P4_Control_Panel constructor.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Hooks actions and filters.
		 */
		public function hooks() {
			// Display the Control Panel only to Administrators.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			add_action( 'wp_dashboard_setup',              array( $this, 'add_dashboard_widgets' ), 9 );
			add_action( 'wp_ajax_flush_cache',             array( $this, 'flush_cache' ) );
			add_action( 'wp_ajax_check_cache',             array( $this, 'check_cache' ) );
			add_action( 'wp_ajax_check_engaging_networks', array( $this, 'check_engaging_networks' ) );
			add_action( 'wp_ajax_check_search_indexer',    array( $this, 'check_search_indexer' ) );
			add_action( 'admin_enqueue_scripts',           array( $this, 'enqueue_admin_assets' ) );
		}

		/**
		 * Adds a new Dashboard widget.
		 */
		public function add_dashboard_widgets() {
			wp_add_dashboard_widget(
				'planet4_control_panel',
				__( 'Planet 4 Control Panel', 'planet4-master-theme' ),
				array( $this, 'add_items' )
			);
		}

		/**
		 * Adds items to the Control Panel.s
		 */
		public function add_items() {
			wp_nonce_field( 'cp-action' );

			$this->add_item( [
				'title'    => __( 'Cache', 'planet4-master-theme' ),
				'subitems' => [
					[
						'title'  => __( 'Flush Object Cache', 'planet4-master-theme' ),
						'action' => 'flush_cache',
					],
					[
						'title'  => __( 'Check Object Cache', 'planet4-master-theme' ),
						'action' => 'check_cache',
					],
				],
			] );

			$this->add_item( [
				'title'    => __( 'Engaging Networks', 'planet4-master-theme' ),
				'subitems' => [
					[
						'title'  => __( 'Check Engaging Networks', 'planet4-master-theme' ),
						'action' => 'check_engaging_networks',
					],
				],
			] );

			$this->add_item( [
				'title'    => __( 'SearchWP', 'planet4-master-theme' ),
				'subitems' => [
					[
						'title'  => __( 'Check Search Indexer', 'planet4-master-theme' ),
						'action' => 'check_search_indexer',
					],
				],
			] );
		}

		/**
		 * Adds a new item in the Control Panel and all of its subitems.
		 *
		 * @param array $data Associative array with all the data needed to add a new item in the Control Panel.
		 */
		public function add_item( $data ) {
			echo '<div class="cp-item">
					<div class="welcome-panel"><span><strong>' . esc_html( $data['title'] ) . '</strong></span>';
			foreach ( $data['subitems'] as $subitem ) {
				echo '<div>
						<a href="#" class="btn btn-cp-action btn-' . esc_attr( $subitem['action'] ) . '-async" data-action="' . esc_attr( $subitem['action'] ) . '">' . esc_html( $subitem['title'] ) . '</a>
						<span class="cp-subitem-response"></span>
					</div>';
			}
			echo '</div>
				</div>';
		}

		/**
		 * Adds a flush cache button to delete all keys in Redis database.
		 */
		public function flush_cache() {
			if ( wp_doing_ajax() ) {
				// Allow this action only to Administrators.
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				$cp_nonce  = filter_input( INPUT_GET, '_wpnonce',  FILTER_SANITIZE_STRING );
				$cp_action = filter_input( INPUT_GET, 'cp-action', FILTER_SANITIZE_STRING );

				if ( wp_verify_nonce( $cp_nonce, 'cp-action' ) && 'flush_cache' === $cp_action ) {
					$response = [];

					if ( wp_cache_flush() ) {
						$response['message'] = __( 'Object Cache flushed', 'planet4-master-theme' );
						$response['class']   = 'cp-success';
					} else {
						$response['message'] = __( 'Object Cache did not flush', 'planet4-master-theme' );
						$response['class']   = 'cp-error';
					}

					if ( $response ) {
						echo wp_json_encode( $response );
					}
				}
				wp_die();
			}
		}

		/**
		 * Adds a check cache button to check connectivity to the Redis server.
		 */
		public function check_cache() {
			if ( wp_doing_ajax() ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				$cp_nonce  = filter_input( INPUT_GET, '_wpnonce',  FILTER_SANITIZE_STRING );
				$cp_action = filter_input( INPUT_GET, 'cp-action', FILTER_SANITIZE_STRING );

				// CSRF protection.
				if ( wp_verify_nonce( $cp_nonce, 'cp-action' ) && 'check_cache' === $cp_action ) {
					$response = [];
					$info     = wp_redis_get_info();

					if ( $info instanceof WP_Error ) {
						if ( $info->errors['wp-redis'] && is_array( $info->errors['wp-redis'] ) ) {
							$response['message'] = $info->errors['wp-redis'][0];
							$response['class']   = 'cp-error';
						}
					} elseif ( 'connected' === $info['status'] ) {
						$response['message'] = __( 'Planet 4 is connected to Redis.', 'planet4-master-theme' );
						$response['class']   = 'cp-success';
					}

					if ( $response ) {
						echo wp_json_encode( $response );
					}
				}
				wp_die();
			}
		}

		/**
		 * Adds a check cache button to check the ENS API.
		 */
		public function check_engaging_networks() {
			if ( wp_doing_ajax() ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				$cp_nonce  = filter_input( INPUT_GET, '_wpnonce',  FILTER_SANITIZE_STRING );
				$cp_action = filter_input( INPUT_GET, 'cp-action', FILTER_SANITIZE_STRING );

				// CSRF protection.
				if ( wp_verify_nonce( $cp_nonce, 'cp-action' ) && 'check_engaging_networks' === $cp_action ) {
					$response      = [];
					$main_settings = get_option( 'p4en_main_settings' );

					if ( isset( $main_settings['p4en_private_api'] ) && $main_settings['p4en_private_api'] ) {
						$ens_api           = new ENS_API();
						$ens_private_token = $main_settings['p4en_private_api'];
						$ens_response      = $ens_api->authenticate( $ens_private_token );

						if ( is_array( $ens_response ) && $ens_response['body'] ) {
							$response['message'] = __( 'Success', 'planet4-master-theme' );
							$response['class']   = 'cp-success';
						} elseif ( is_string( $ens_response ) ) {
							$response['message'] = $ens_response;
							$response['class']   = 'cp-error';
						}
					}

					if ( $response ) {
						echo wp_json_encode( $response );
					}
				}
				wp_die();
			}
		}

		/**
		 * Adds a check button to check the Indexer of the SearchWP plugin.
		 */
		public function check_search_indexer() {
			if ( wp_doing_ajax() ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				$cp_nonce  = filter_input( INPUT_GET, '_wpnonce',  FILTER_SANITIZE_STRING );
				$cp_action = filter_input( INPUT_GET, 'cp-action', FILTER_SANITIZE_STRING );

				if ( wp_verify_nonce( $cp_nonce, 'cp-action' ) && 'check_search_indexer' === $cp_action ) {
					$threshold = 180;
					$response  = [];

					$last_activity  = searchwp_get_setting( 'last_activity', 'stats' );
					$running        = searchwp_get_setting( 'running' );
					$doing_delta    = searchwp_get_option( 'doing_delta' );
					$busy           = searchwp_get_option( 'busy' );

					if ( ! is_null( $last_activity ) && false !== $last_activity ) {
						if (
							( current_time( 'timestamp' ) > $last_activity + absint( $threshold ) )
							&& ! $running && ! $doing_delta && ! $busy
						) {
							$response['message'] = __( 'Indexer has stalled', 'planet4-master-theme' );
							$response['class']   = 'cp-error';
						} else {
							$response['message'] = __( 'Indexer is awake', 'planet4-master-theme' );
							$response['class']   = 'cp-success';
						}
					}

					if ( $response ) {
						echo wp_json_encode( $response );
					}
				}
				wp_die();
			}
		}

		/**
		 * Load assets.
		 */
		public function enqueue_admin_assets() {
			if ( ! is_admin() || 'dashboard' !== get_current_screen()->base ) {
				return;
			}
			$theme_dir = get_template_directory_uri();
			wp_enqueue_style( 'dashboard-style', "$theme_dir/assets/css/dashboard.css", array(), '0.1.0' );
			wp_enqueue_script( 'dashboard-script', "$theme_dir/assets/js/dashboard.js", array( 'jquery' ), '0.1.0', true );
		}
	}
}
