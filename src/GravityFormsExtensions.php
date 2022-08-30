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
	 * The constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Class hooks.
	 */
	private function hooks() {
		add_filter( 'gform_form_settings_fields', [ $this, 'p4_gf_type_setting' ], 5, 2 );
	}

	/**
	 * Add a setting to gravity Forms to set the type of form.
	 *
	 * @param array  $fields The form settings fields.
	 * @param object $form The Gravity form Object.
	 *
	 * @return array The new fields array.
	 */
	public function p4_gf_type_setting( $fields, $form ) {

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

		return $fields;
	}
}
