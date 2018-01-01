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
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			add_action( 'wp_dashboard_setup',           array( $this, 'add_dashboard_widgets' ), 9 );
			add_action( 'wp_ajax_check_cache',          array( $this, 'check_cache' ) );
			add_action( 'wp_ajax_check_en',             array( $this, 'check_en' ) );
			add_action( 'admin_enqueue_scripts',        array( $this, 'enqueue_admin_assets' ) );
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
		 * Outputs the contents of the dashboard widget.
		 */
		public function add_items() {
			wp_nonce_field( 'cp-action' );

			$this->add_item( [
				'title'    => __( 'Cache', 'planet4-master-theme' ),
				'subitems' => [
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
						'action' => 'check_en',
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
		 * Adds a check cache button to check connectivity to the Redis server.
		 */
		public function check_cache() {
			if ( wp_doing_ajax() ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				if ( wp_verify_nonce( $_GET['_wpnonce'], 'cp-action' )  // CSRF protection.
				     && isset( $_GET['cp-action'] )
				     && 'check_cache' === $_GET['cp-action']
				) {
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
		public function check_en() {
			if ( wp_doing_ajax() ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				if ( wp_verify_nonce( $_GET['_wpnonce'], 'cp-action' )     // CSRF protection.
				     && isset( $_GET['cp-action'] )
				     && 'check_en' === $_GET['cp-action']
				) {
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
		 * Load assets.
		 */
		public function enqueue_admin_assets() {
			if ( ! is_admin() || 'dashboard' !== get_current_screen()->base ) {
				return;
			}
			wp_enqueue_style( 'dashboard-style', get_template_directory_uri() . '/assets/css/dashboard.css', array(), '0.1.0' );
			wp_enqueue_script( 'dashboard-script', get_template_directory_uri() . '/assets/js/dashboard.js', array( 'jquery' ), '0.1.0', true );
		}
	}
}
