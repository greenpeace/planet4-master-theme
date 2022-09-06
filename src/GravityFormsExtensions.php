<?php

namespace P4\MasterTheme;

use GFAPI;

/**
 * Class P4\MasterTheme\GravityFormsExtensions
 * The Gravity form plugin extension, use to add custom functionality like Planet4 form type.
 */
class GravityFormsExtensions {
	/**
	 * @var string The default gravity form type.
	 */
	public const DEFAULT_GF_TYPE = 'other';

	/**
	 * @var array The Planet4 Gravity form types.
	 */
	public const P4_GF_TYPES = [
		[
			'label' => 'Other',
			'value' => 'other',
		],
		[
			'label' => 'Petition',
			'value' => 'petition',
		],
		[
			'label' => 'Email Signup',
			'value' => 'email-signup',
		],
		[
			'label' => 'Quiz/Poll',
			'value' => 'quiz-poll',
		],
		[
			'label' => 'Email-to-target',
			'value' => 'email-to-target',
		],
		[
			'label' => 'Contact',
			'value' => 'contact',
		],
		[
			'label' => 'Survey',
			'value' => 'survey',
		],
		[
			'label' => 'Feedback',
			'value' => 'feedback',
		],
	];

	/**
	 * @var string The default gravity form confirmation message.
	 */
	public const DEFAULT_GF_CONFIRMATION = 'planet4';

	/**
	 * @var array The Planet4 Gravity form confirmation messages.
	 */
	public const P4_GF_CONFIRMATIONS = [
		[
			'label' => 'Planet 4 message',
			'value' => 'planet4',
		],
		[
			'label' => 'Custom message',
			'value' => 'custom',
		],
	];

	/**
	 * @var array The Planet4 Gravity Forms share buttons options.
	 */
	public const P4_SHARE_BUTTONS = [
		[
			'label'         => 'WhatsApp',
			'name'          => 'whatsapp',
			'default_value' => 1,
		],
		[
			'label'         => 'Facebook',
			'name'          => 'facebook',
			'default_value' => 1,
		],
		[
			'label'         => 'Twitter',
			'name'          => 'twitter',
			'default_value' => 1,
		],
		[
			'label'         => 'Email',
			'name'          => 'email',
			'default_value' => 1,
		],
		[
			'label'         => 'Native share (mobile only)',
			'name'          => 'native',
			'default_value' => 1,
		],
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
		add_filter( 'gform_form_settings_fields', [ $this, 'p4_gf_settings' ], 5, 2 );
		add_filter( 'gform_secure_file_download_url', [ $this, 'p4_gf_file_download_url' ], 10, 2 );
		add_action( 'gform_after_save_form', [ $this, 'p4_gf_custom_initial_settings' ], 10, 2 );
		add_filter( 'gform_confirmation_settings_fields', [ $this, 'p4_gf_confirmation_settings' ], 10, 3 );
	}

	/**
	 * Add form settings to Gravity Forms: one to set the type of form, one to choose the confirmation message.
	 *
	 * @param array $fields The form settings fields.
	 *
	 * @return array The new fields array.
	 */
	public function p4_gf_form_settings( $fields ) {

		if ( ! array_key_exists( 'p4_options', $fields ) ) {
			$new_fields['p4_options'] = [
				'title' => __( 'Planet 4 Options', 'planet4-master-theme-backend' ),
			];

			// Add new field to beginning of the $fields array.
			$fields = array_merge( $new_fields, $fields );
		}

		$fields['p4_options']['fields'][] = [
			'type'           => 'select',
			'name'           => 'p4_gf_type',
			'label'          => __( 'Form Type', 'planet4-master-theme-backend' ),
			'tooltip'        => __( 'Please select a form type below so you can track and analyze each form type separately', 'planet4-master-theme-backend' ),
			'required'       => true,
			'default_value ' => self::DEFAULT_GF_TYPE,
			'choices'        => self::P4_GF_TYPES,
		];

		$fields['p4_options']['fields'][] = [
			'type'           => 'select',
			'name'           => 'p4_gf_confirmation',
			'label'          => __( 'Confirmation message', 'planet4-master-theme-backend' ),
			'tooltip'        => __( 'If you use the Planet 4 confirmation message, you will not be able to see it and edit it via the Confirmations menu item', 'planet4-master-theme-backend' ),
			'required'       => true,
			'default_value ' => self::DEFAULT_GF_CONFIRMATION,
			'choices'        => self::P4_GF_CONFIRMATIONS,
		];

		return $fields;
	}

	/**
	 * Update Gravity form file path before output.
	 *
	 * @param string $file_path The file path of the download file.
	 * @param object $field     The field object for further context.
	 *
	 * @return string The new file path.
	 */
	public function p4_gf_file_download_url( $file_path, $field ) {
		if ( strpos( $file_path, '/gravity_forms/' ) !== false ) {
			// The default gravity form uploaded files path gives error.
			// eg. https://www.greenpeace.org/static/planet4-test-titan-stateless-develop/gravity_forms/8-23c5dc88bb5af48eb293c4c780a5ed0a/2022/09/e26f3fe9-2022_08_gravity_forms_3-1b36ac6eddacf20087d29746b297b384_2022_08_99ef18e1-predator.jpg
			// By updating a part[/year/month/] of file path('/gravity_forms/' => '/2022/09/gravity_forms/') fix the issue.

			// Extract year and month from file path.
			$year_month = array_slice( explode( '/', $file_path ), -3, 2 );

			// Update the gravity form file download path with year and month.
			return str_replace( '/gravity_forms/', '/' . implode( '/', $year_month ) . '/gravity_forms/', $file_path );
		}

		return $file_path;
	}

	/**
	 * Update initial settings for Gravity Forms (sub-label and description placement).
	 *
	 * @param array $form The form settings.
	 * @param bool  $is_new Whether the form is newly created or not.
	 */
	public function p4_gf_custom_initial_settings( $form, $is_new ) {
		if ( $is_new ) {
			// Update sub-label and description placement initial settings.
			$form['subLabelPlacement']    = 'above';
			$form['descriptionPlacement'] = 'above';

			// Make form active immediately.
			$form['is_active'] = '1';

			GFAPI::update_form( $form );
		}
	}

	/**
	 * Add confirmation settings to Gravity Forms: the ability to add share buttons.
	 *
	 * @param array $fields The general confirmation settings fields.
	 * @param array $confirmation The current form's confirmation settings.
	 * @param array $form The current form's settings.
	 *
	 * @return array The new fields array.
	 */
	public function p4_gf_confirmation_settings( $fields, $confirmation, $form ) {
		echo '
			<style>
				.hidden {
					display: none !important;
				}
			</style>
		';

		// This bit of code is to hide the confirmation settings if the P4 default message option is selected in the form's settings.
		if ( self::DEFAULT_GF_CONFIRMATION === $form['p4_gf_confirmation'] ) {
			echo '
				<script>
					addEventListener("DOMContentLoaded", () => {
						const saveButton = document.querySelector(".gform-settings-save-container");
						const settingsSection = document.querySelector(".gform-settings__content");

						saveButton.classList.add("hidden");

						const handbookLink = document.createElement("a");
						handbookLink.target = "_blank";
						handbookLink.innerText = "P4 handbook";
						handbookLink.href = "https://planet4.greenpeace.org/manage/integrate/form-builder/build-a-form-in-gravity-forms/#confirmation-settings--thank-you-page-";

						const explanation = document.createElement("div");
						explanation.innerText = "You cannot edit confirmation messages here since this form uses our P4 default message. If you want to change this, you can do so in the form\'s general settings. You can find more information about this in the ";
						explanation.appendChild(handbookLink);

						settingsSection.appendChild(explanation);
					});
				</script>
			';
			return [];
		}

		// This bit of code is to hide the "Share Buttons" section if editors select "Page" or "Redirect" as confirmation message.
		echo '
			<script>
				addEventListener("DOMContentLoaded", () => {
					const confirmationTypeCheckboxes = [...document.querySelectorAll("input[name=\"_gform_setting_type\"]")];
					const textTypeCheckbox = confirmationTypeCheckboxes.find(input => input.value === "message");
					const shareButtonsSettings = document.querySelector("#gform-settings-section-share-buttons");

					const onChange = checkbox => {
						if (checkbox.value === "message" && checkbox.checked) {
							shareButtonsSettings.classList.remove("hidden");
						} else {
							shareButtonsSettings.classList.add("hidden");
						}
					}

					confirmationTypeCheckboxes.forEach(input => input.addEventListener("change", event => onChange(event.currentTarget)));

					onChange(textTypeCheckbox);
				});
			</script>
		';

		if ( ! array_key_exists( 'p4_share_buttons', $fields ) ) {
			$share_buttons['p4_share_buttons'] = [
				'title' => __( 'Share buttons', 'planet4-master-theme-backend' ),
			];

			// Add new field to end of the $fields array.
			$fields = array_merge( $fields, $share_buttons );
		}

		$fields['p4_share_buttons']['fields'][] = [
			'type'    => 'checkbox',
			'name'    => 'p4_gf_share_platforms',
			'label'   => __( 'Show share buttons below message', 'planet4-master-theme-backend' ),
			'choices' => self::P4_SHARE_BUTTONS,
		];

		$fields['p4_share_buttons']['fields'][] = [
			'type'    => 'text',
			'name'    => 'p4_gf_share_text_override',
			'label'   => __( 'Share text', 'planet4-master-theme-backend' ),
			'tooltip' => __( 'This is the text that will be shared when a user clicks a share button (if the platform supports share text)', 'planet4-master-theme-backend' ),
		];

		$fields['p4_share_buttons']['fields'][] = [
			'type'    => 'text',
			'name'    => 'p4_gf_share_url_override',
			'label'   => __( 'Override share URL', 'planet4-master-theme-backend' ),
			'tooltip' => __( 'By default, share buttons will share the URL of the page that the form was submitted on. Use this field to override with a different URL.', 'planet4-master-theme-backend' ),
		];

		return $fields;
	}
}
