<?php
/**
 * ENBlock class
 *
 * @package P4EN
 */

namespace P4GEN\Controllers\Blocks;

use P4GEN\Controllers\Ensapi_Controller as Ensapi;
use P4GEN\Controllers\Menu\Enform_Post_Controller;
use P4GEN\Controllers\Menu\Pages_Datatable_Controller;

if ( ! class_exists( 'ENBlock_Controller' ) ) {

	/**
	 * Class ENBlock_Controller
	 *
	 * @package P4GEN\Controllers\Blocks
	 */
	class ENBlock_Controller extends Controller {

		/**
		 * Block name
		 *
		 * @const string BLOCK_NAME
		 */
		const BLOCK_NAME = 'enblock';

		/**
		 * Page types for EN forms
		 *
		 * @const array ENFORM_PAGE_TYPES
		 */
		const ENFORM_PAGE_TYPES = [ 'PET', 'EMS' ];

		/**
		 * ENSAPI Object
		 *
		 * @var Ensapi $ensapi
		 */
		private $ens_api = null;

		/**
		 * Hooks all the needed functions to load the block.
		 */
		public function load() {
			parent::load();
			add_action( 'admin_print_footer_scripts-post.php', [ $this, 'print_admin_footer_scripts' ], 1 );
			add_action( 'admin_print_footer_scripts-post-new.php', [ $this, 'print_admin_footer_scripts' ], 1 );
			add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_assets' ] );
			add_action( 'wp_ajax_get_en_session_token', [ $this, 'get_session_token' ] );
			add_action( 'wp_ajax_nopriv_get_en_session_token', [ $this, 'get_session_token' ] );
		}

		/**
		 * Load assets only on the admin pages of the plugin.
		 *
		 * @param string $hook The slug name of the current admin page.
		 */
		public function load_admin_assets( $hook ) {
			if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
				return;
			}

			wp_enqueue_style( 'p4en_admin_style_blocks', P4GEN_ADMIN_DIR . 'css/admin_en.css', [], '0.4' );
			add_action(
				'enqueue_shortcode_ui',
				function () {
					wp_enqueue_script( 'en-ui-heading-view', P4GEN_ADMIN_DIR . 'js/en_ui_heading_view.js', [ 'shortcode-ui' ], '0.1', true );
					wp_register_script( 'en-ui', P4GEN_ADMIN_DIR . 'js/en_ui.js', [ 'shortcode-ui' ], '0.7', true );

					// Localize en-ui script.
					$localization_data = [
						'en_fields_description_1' => __( 'What kind of Information do you want to send to EN?', 'planet4-gutenberg-engagingnetworks' ),
						'en_fields_description_2' => __( 'Make sure to select the same fields of your Engaging Networks page / form', 'planet4-gutenberg-engagingnetworks' ),
						'block_name'              => self::BLOCK_NAME,
					];
					wp_localize_script( 'en-ui', 'p4_enblock', $localization_data );
					wp_enqueue_script( 'en-ui' );
				}
			);
		}

		/**
		 * Load underscore templates to footer.
		 */
		public function print_admin_footer_scripts() {
			echo $this->get_template( 'en-ui' ); // WPCS: XSS ok.
		}

		/**
		 * Shortcode UI setup for the ENForm shortcode.
		 *
		 * It is called when the Shortcake action hook `register_shortcode_ui` is called.
		 */
		public function prepare_fields() {
			$pages         = [];
			$pages_options = [];
			$forms         = [];
			$forms_options = [];

			// Get EN pages only on admin panel.
			if ( is_admin() ) {
				$main_settings = get_option( 'p4en_main_settings' );

				if ( isset( $main_settings['p4en_private_api'] ) ) {
					$ens_private_token = $main_settings['p4en_private_api'];
					$this->ens_api     = new Ensapi( $ens_private_token );
					$pages             = $this->ens_api->get_pages_by_types_status( self::ENFORM_PAGE_TYPES, 'live' );
					uasort(
						$pages,
						function ( $a, $b ) {
							return ( $a['name'] ?? '' ) <=> ( $b['name'] ?? '' );
						}
					);
				}

				$pages_options = [
					[
						'value' => '0',
						'label' => __( '- Select Page -', 'planet4-gutenberg-engagingnetworks' ),
					],
				];
				if ( $pages ) {
					foreach ( $pages as $type => $group_pages ) {
						$group_options = [];
						foreach ( $group_pages as $page ) {
							$group_options[] = [
								'value' => (string) $page['id'],
								'label' => (string) $page['name'],
							];
						}
						$pages_options[] = [
							'label'   => Pages_Datatable_Controller::SUBTYPES[ $type ]['subType'],
							'options' => $group_options,
						];
					}
				}

				// Get EN Forms.
				$query = new \WP_Query(
					[
						'post_status'      => 'publish',
						'post_type'        => Enform_Post_Controller::POST_TYPE,
						'orderby'          => 'post_title',
						'order'            => 'asc',
						'suppress_filters' => false,
						'numberposts'      => 100,
					]
				);
				$forms = $query->posts;

				$forms_options = [
					[
						'value' => '0',
						'label' => __( '- Select Form -', 'planet4-gutenberg-engagingnetworks' ),
					],
				];
				if ( $forms ) {
					foreach ( $forms as $form ) {
						$forms_options[] = [
							'value' => (string) $form->ID,
							'label' => (string) $form->post_title,
						];
					}
				}
			}

			$goal_options = [
				0                 => __( '- Select Goal -', 'planet4-gutenberg-engagingnetworks' ),
				'Petition Signup' => 'Petition Signup',
				'Action Alert'    => 'Action Alert',
				'Contact Form'    => 'Contact Form',
				'Other'           => 'Other',
			];

			$fields = [
				[
					'label'       => __( 'Engaging Network Live Pages', 'planet4-gutenberg-engagingnetworks' ),
					'description' => $pages ? __( 'Select the Live EN page that this form will be submitted to.', 'planet4-gutenberg-engagingnetworks' ) : __( 'Check your EngagingNetworks settings!', 'planet4-gutenberg-engagingnetworks' ),
					'attr'        => 'en_page_id',
					'type'        => 'select',
					'meta'        => [
						'required' => '',
					],
					'options'     => $pages_options,
				],
				[
					'label'       => __( 'Goal', 'planet4-gutenberg-engagingnetworks' ),
					'attr'        => 'enform_goal',
					'type'        => 'select',
					'meta'        => [
						'required' => '',
					],
					'options'     => $goal_options,
					'description' => __( 'When form data submitted to EN, The value added in "Goal" field is used in the GTM dataLayer push event.', 'planet4-gutenberg-engagingnetworks' ),
				],
				[
					'attr'    => 'en_form_style',
					'label'   => __( 'What style of form do you need?', 'planet4-gutenberg-engagingnetworks' ),
					'type'    => 'p4en_radio',
					'options' => [
						[
							'value' => 'full-width',
							'label' => __( 'Page body / text size width. No background.', 'planet4-gutenberg-engagingnetworks' ),
							'desc'  => __( 'Best to use inside pages. Form width will align with body / text width.', 'planet4-gutenberg-engagingnetworks' ),
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enfullwidth.png' ),
						],
						[
							'value' => 'full-width-bg',
							'label' => __( 'Full page width. With background image.', 'planet4-gutenberg-engagingnetworks' ),
							'desc'  => __( 'This form has a background image that expands the full width of the browser (aka "Happy Point").', 'planet4-gutenberg-engagingnetworks' ),
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/enfullwidthbg.png' ),
						],
						[
							'value' => 'side-style',
							'label' => __( 'Form on the side.', 'planet4-gutenberg-engagingnetworks' ),
							'desc'  => __( 'Form will be added to the top of the page, on the right side for most languages and on the left side for Right-to-left(RTL) languages.', 'planet4-gutenberg-engagingnetworks' ),
							'image' => esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/submenu-sidebar.jpg' ),
						],
					],
				],
				[
					'label'       => __( 'Background image for full width / on the side forms styles', 'planet4-gutenberg-engagingnetworks' ),
					'attr'        => 'background',
					'type'        => 'attachment',
					'libraryType' => [ 'image' ],
					'addButton'   => __( 'Select Background Image', 'planet4-gutenberg-engagingnetworks' ),
					'frameTitle'  => __( 'Select Background Image', 'planet4-gutenberg-engagingnetworks' ),
				],
				[
					'label' => __( 'Form Title', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'title',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter title', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Form Description', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'description',
					'type'  => 'textarea',
					'meta'  => [
						'placeholder' => __( 'Enter description', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Content Title', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'content_title',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter content title', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Content Description', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'content_description',
					'type'  => 'textarea',
					'meta'  => [
						'placeholder' => __( 'Enter content description', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( 'Call to Action button (e.g. "Sign up now!")', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'button_text',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter the "Call to Action" button text', 'planet4-gutenberg-engagingnetworks' ),
						'required'    => '',
					],
				],
				[
					'label' => __( 'Text below Call to Action button', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'text_below_button',
					'type'  => 'textarea',
					'meta'  => [
						'placeholder' => __( 'Enter text to go below the button', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( '"Thank you" main text / Title (e.g. "Thank you for signing!")', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'thankyou_title',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter "Thank you" main text / Title ', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( '"Thank You" secondary message / Subtitle (e.g. "Your support means world")', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'thankyou_subtitle',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter Thank you Subtitle', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( '"Thank You" social media message (e.g. "Can you share it with your family and friends?")', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'thankyou_donate_message',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter Donate Message', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( '"Thank You" donate message (e.g. "or make a donation")', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'thankyou_take_action_message',
					'type'  => 'text',
					'meta'  => [
						'placeholder' => __( 'Enter Take Action Message', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label' => __( '"Thank you page" url (Title and Subtitle will not be shown)', 'planet4-gutenberg-engagingnetworks' ),
					'attr'  => 'thankyou_url',
					'type'  => 'url',
					'meta'  => [
						'placeholder' => __( 'Enter "Thank you page" url', 'planet4-gutenberg-engagingnetworks' ),
					],
				],
				[
					'label'       => __( 'Planet 4 Engaging Networks form', 'planet4-gutenberg-engagingnetworks' ),
					'description' => $forms ? __( 'Select the P4EN Form that will be displayed.', 'planet4-gutenberg-engagingnetworks' ) : __( 'Create an EN Form', 'planet4-gutenberg-engagingnetworks' ),
					'attr'        => 'en_form_id',
					'type'        => 'select',
					'meta'        => [
						'required' => '',
					],
					'options'     => $forms_options,
				],
			];

			// Define the Shortcode UI arguments.
			$shortcode_ui_args = [
				'label'         => __( 'Engaging Networks Form', 'planet4-gutenberg-engagingnetworks' ),
				'listItemImage' => '<img src="' . esc_url( plugins_url() . '/planet4-plugin-engagingnetworks/admin/images/' . self::BLOCK_NAME . '.png' ) . '" />',
				'attrs'         => $fields,
				'post_type'     => P4GEN_ALLOWED_PAGETYPE,
			];

			shortcode_ui_register_for_shortcode( 'shortcake_' . self::BLOCK_NAME, $shortcode_ui_args );
		}

		/**
		 * Get all the data that will be needed to render the block correctly.
		 *
		 * @param array  $fields This is the array of fields of the block.
		 * @param string $content This is the post content.
		 * @param string $shortcode_tag The shortcode tag of the block.
		 *
		 * @return array The data to be passed in the View.
		 */
		public function prepare_data( $fields, $content, $shortcode_tag ) : array {
			$fields = $this->ignore_unused_attributes( $fields );

			// Handle background image.
			if ( isset( $fields['background'] ) ) {
				$options                     = get_option( 'planet4_options' );
				$p4_happy_point_bg_image     = $options['happy_point_bg_image_id'] ?? '';
				$image_id                    = '' !== $fields['background'] ? $fields['background'] : $p4_happy_point_bg_image;
				$img_meta                    = wp_get_attachment_metadata( $image_id );
				$fields['background_src']    = wp_get_attachment_image_src( $image_id, 'retina-large' );
				$fields['background_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta );
				$fields['background_sizes']  = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
			}
			$fields['default_image'] = get_bloginfo( 'template_directory' ) . '/images/happy-point-block-bg.jpg';

			$data = [];

			if ( isset( $fields['thankyou_url'] ) && 0 !== strpos( $fields['thankyou_url'], 'http' ) ) {
				$fields['thankyou_url'] = 'http://' . $fields['thankyou_url'];
			} else {
				$options              = get_option( 'planet4_options' );
				$fields['donatelink'] = $options['donate_button'] ?? '#';
			}

			$data = array_merge(
				$data,
				[
					'fields'       => $fields,
					'redirect_url' => isset( $fields['thankyou_url'] ) ? filter_var( $fields['thankyou_url'], FILTER_VALIDATE_URL ) : '',
					'nonce_action' => 'enform_submit',
					'form'         => '[' . Enform_Post_Controller::POST_TYPE . ' id="' . $fields['en_form_id'] . '" en_form_style="' . $fields['en_form_style'] . '" /]',
				]
			);

			return $data;
		}

		/**
		 * Get en session token for frontend api calls.
		 */
		public function get_session_token() {
			// If this is an ajax call.
			if ( wp_doing_ajax() ) {

				$nonce    = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
				$response = [];

				// CSRF protection.
				if ( ! wp_verify_nonce( $nonce, 'enform_submit' ) ) {
					$response['message'] = __( 'Invalid nonce!', 'planet4-gutenberg-engagingnetworks' );
					$response['token']   = '';
				} else {

					$main_settings     = get_option( 'p4en_main_settings' );
					$ens_private_token = $main_settings['p4en_frontend_private_api'];
					$this->ens_api     = new Ensapi( $ens_private_token, false );
					$token             = $this->ens_api->get_public_session_token();
					$response['token'] = $token;
				}
				wp_send_json( $response );
			}
		}

		/**
		 * Validates the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 *
		 * @return bool
		 */
		public function validate( $input ) : bool {
			if (
				( ! isset( $input['en_page_id'] ) || '0' === $input['en_page_id'] ) ||
				( ! isset( $input['supporter.emailAddress'] ) || false === filter_var( $input['supporter.emailAddress'], FILTER_VALIDATE_EMAIL ) )
			) {
				return false;
			}
			return true;
		}

		/**
		 * Sanitizes the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 */
		public function sanitize( &$input ) {
			foreach ( $input as $key => $value ) {
				if ( 'supporter.emailAddress' === $key ) {
					$input[ $key ] = sanitize_email( $value );

				} elseif ( false !== strpos( $key, 'supporter.question.' ) ) {  // Question/Optin name is in the form of 'supporter.question.{id}'.
					$key_parts = explode( '.', $key );
					if ( isset( $key_parts[2] ) ) {
						$input['supporter.questions'][ "question.$key_parts[2]" ] = sanitize_text_field( $value );
						unset( $input[ "supporter.question.$key_parts[2]" ] );
					}
				} else {
					$input[ $key ] = sanitize_text_field( $value );
				}
			}
		}
	}
}
