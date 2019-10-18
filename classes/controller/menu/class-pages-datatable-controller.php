<?php
/**
 * Pages Datatable Controller
 *
 * @package P4GEN\Controllers
 */

namespace P4GEN\Controllers\Menu;

use P4GEN\Controllers\Ensapi_Controller;

if ( ! class_exists( 'Pages_Datatable_Controller' ) ) {

	/**
	 * Class Pages_Datatable_Controller
	 */
	class Pages_Datatable_Controller extends Controller {

		const SUBTYPES = [
			'DCF'   => [
				'type'    => 'Data capture',
				'subType' => 'Data capture form',
			],
			'MEM'   => [
				'type'    => 'Fundraising',
				'subType' => 'Membership',
			],
			'EMS'   => [
				'type'    => 'List management',
				'subType' => 'Email subscribe',
			],
			'UNSUB' => [
				'type'    => 'List management',
				'subType' => 'Email unsubscribe',
			],
			'PET'   => [
				'type'    => 'Advocacy',
				'subType' => 'Petition',
			],
			'ET'    => [
				'type'    => 'Advocacy',
				'subType' => 'Email to target',
			],
			'ND'    => [
				'type'    => 'Fundraising',
				'subType' => 'Donation',
			],
		];

		const STATUSES = [
			'all'    => 'All',
			'new'    => 'New',
			'live'   => 'Live',
			'tested' => 'Tested',
		];

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {
				add_submenu_page(
					P4GEN_PLUGIN_SLUG_NAME,
					__( 'EN Pages', 'planet4-gutenberg-engagingnetworks' ),
					__( 'EN Pages', 'planet4-gutenberg-engagingnetworks' ),
					'edit_pages',
					'en-pages',
					[ $this, 'prepare_pages_datatable' ]
				);
			}
		}

		/**
		 * Pass all needed data to the view object for the datatable page.
		 */
		public function prepare_pages_datatable() {
			$data           = [];
			$pages          = [];
			$params         = [];
			$pages_settings = [];

			$current_user = wp_get_current_user();
			$validated    = $this->handle_submit( $current_user, $data );

			if ( $validated ) {
				$pages_settings = get_user_meta( $current_user->ID, 'p4en_pages_datatable_settings', true );
				if ( isset( $pages_settings['p4en_pages_subtype'] ) && $pages_settings['p4en_pages_subtype'] ) {
					$params['type'] = $pages_settings['p4en_pages_subtype'];

					if ( isset( $pages_settings['p4en_pages_status'] ) && 'all' !== $pages_settings['p4en_pages_status'] ) {
						$params['status'] = $pages_settings['p4en_pages_status'];
					}

					$main_settings = get_option( 'p4en_main_settings' );
					if ( isset( $main_settings['p4en_private_api'] ) ) {

						$ens_private_token = $main_settings['p4en_private_api'];
						$ens_api           = new Ensapi_Controller( $ens_private_token );

						// Communication with ENS API is authenticated.
						if ( $ens_api->is_authenticated() ) {
							$response = $ens_api->get_pages( $params );
							if ( is_array( $response ) ) {
								$pages = $response;
							} else {
								$this->error( $response );
							}
						} else {
							$this->error( __( 'Authentication failed', 'planet4-gutenberg-engagingnetworks' ) );
						}
					} else {
						$this->warning( __( 'Plugin Settings are not configured well!', 'planet4-gutenberg-engagingnetworks' ) );
					}
				} else {
					$this->notice( __( 'Select Subtype', 'planet4-gutenberg-engagingnetworks' ) );
				}
			} else {
				$this->error( __( 'Changes are not saved!', 'planet4-gutenberg-engagingnetworks' ) );
			}

			$data = array_merge(
				$data,
				[
					'pages'          => $pages,
					'pages_settings' => $pages_settings,
					'subtypes'       => self::SUBTYPES,
					'statuses'       => self::STATUSES,
					'messages'       => $this->messages,
					'domain'         => 'planet4-gutenberg-engagingnetworks',
				]
			);

			$this->filter_pages_datatable( $data );
			// Provide hook for other plugins to be able to filter the datatable output.
			$data = apply_filters( 'p4en_filter_pages_datatable', $data );

			$this->view->pages_datatable( $data );
		}

		/**
		 * Handle form submit.
		 *
		 * @param \WP_User $current_user The current user.
		 * @param mixed[]  $data The form data.
		 *
		 * @return bool Array if validation is ok, false if validation fails.
		 */
		public function handle_submit( $current_user, &$data ) : bool {
			// CSRF protection.
			$nonce_action         = 'pages_datatable_submit';
			$nonce                = wp_create_nonce( $nonce_action );
			$data['nonce_action'] = $nonce_action;
			$data['form_submit']  = 0;

			if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				$data['form_submit'] = 1;

				if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
					$this->error( __( 'Nonce verification failed!', 'planet4-gutenberg-engagingnetworks' ) );
					return false;
				} else {
					$pages_datatable_settings = $_POST['p4en_pages_datatable_settings'];

					$pages_datatable_settings = $this->valitize( $pages_datatable_settings );
					if ( false === $pages_datatable_settings ) {
						return false;
					}

					update_user_meta( $current_user->ID, 'p4en_pages_datatable_settings', $pages_datatable_settings );

					$this->success( __( 'Changes saved!', 'planet4-gutenberg-engagingnetworks' ) );
				}
			}
			return true;
		}

		/**
		 * Filter the output for the datatable page.
		 *
		 * @param array $data The data array that will be passed to the View.
		 */
		public function filter_pages_datatable( &$data ) {

			if ( $data ) {
				foreach ( $data['pages'] as &$page ) {
					$page['campaignStatus'] = ucfirst( $page['campaignStatus'] );
					if ( ! $page['subType'] ) {
						$page['subType'] = strtoupper( $page['type'] );
					}

					switch ( $page['type'] ) {
						case 'dc':
							switch ( $page['subType'] ) {
								case 'DCF':
									$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/data/1' );
									break;
								case 'PET':
									$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/petition/1' );
									break;
								default:
									$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/petition/1' );
							}
							break;
						case 'nd':
							$page['url'] = esc_url( $page['campaignBaseUrl'] . '/page/' . $page['id'] . '/donation/1' );
							break;
					}
				}
			}
		}

		/**
		 * Validates the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			$has_errors = false;
			return ! $has_errors;
		}

		/**
		 * Sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin (Call by Reference).
		 */
		public function sanitize( &$settings ) {
			if ( $settings ) {
				foreach ( $settings as $name => $setting ) {
					$settings[ $name ] = sanitize_text_field( $setting );
				}
			}
		}
	}
}
