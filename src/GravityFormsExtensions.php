<?php

namespace P4\MasterTheme;

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
	}

	/**
	 * Add settings to Gravity Forms: one to set the type of form, one to choose the confirmation message.
	 *
	 * @param array  $fields The form settings fields.
	 * @param object $form The Gravity form Object.
	 *
	 * @return array The new fields array.
	 */
	public function p4_gf_settings( $fields, $form ) {

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
}
