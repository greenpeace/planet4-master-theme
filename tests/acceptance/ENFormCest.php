<?php

use Page\ENFormCreate as ENForm;
use Page\ENBlock as ENBlock;

/**
 * Class ENFormCest
 *
 * @group enform
 */
class ENFormCest {

	protected $form_fields;
	protected $form_fields_attributes;
	protected $enform_id;

	/**
	 * Setup engaging networks fields and questions that will be used to create a new enform post.
	 */
	public function _before( AcceptanceTester $I ) {
		$this->form_fields = [
			'fields'    => [
				[
					'id'       => 28121,
					'name'     => 'Email',
					'tag'      => 'Email Address',
					'property' => 'emailAddress',
				],
				[
					'id'       => 28116,
					'name'     => 'First name',
					'tag'      => 'First Name',
					'property' => 'firstName',
				],
				[
					'id'       => 28117,
					'name'     => 'Last name',
					'tag'      => 'Last Name',
					'property' => 'lastName',
				],
				[
					'id'       => 28122,
					'name'     => 'Country',
					'tag'      => 'Country',
					'property' => 'country',
				],
				[
					'id'       => 67127,
					'name'     => 'AwakenMe',
					'tag'      => 'Not Tagged',
					'property' => 'NOT_TAGGED_1',
				],
			],
			'questions' => [
				[
					'id'         => 3887,
					'questionId' => 3665,
					'name'       => 'Opt-in',
					'type'       => 'OPT',
				],
				[
					'id'         => 236734,
					'questionId' => 25781,
					'name'       => 'test question 1',
					'type'       => 'GEN',
				],
				[
					'id'         => 220954,
					'questionId' => 25511,
					'name'       => 'nro_data_ok',
					'type'       => 'OPT',
				]
			]
		];

		$this->form_fields_attributes = [
			'First name'      => [
				'default_value' => '',
				'label'         => 'First Name',
				'required'      => false,
				'type'          => 'Text',
			],
			'Last name'       => [
				'default_value' => '',
				'label'         => 'Last Name',
				'required'      => false,
				'type'          => 'Text',
			],
			'Email'           => [
				'default_value' => '',
				'label'         => 'Email',
				'required'      => true,
				'type'          => 'Email',
			],
			'Country'         => [
				'default_value' => '',
				'label'         => 'Country',
				'required'      => false,
				'type'          => 'Country',
			],
			'AwakenMe'        => [
				'default_value' => 'hidden field ασφ (0287#$%^ 日本語',
				'label'         => '',
				'required'      => false,
				'type'          => 'Hidden',
			],
			'Opt-in'          => [
				'default_value' => '',
				'label'         => 'Opt in',
				'required'      => false,
				'type'          => 'Checkbox',
				'dependency'    => 'nro_data_ok',
			],
			'test question 1' => [
				'default_value' => '',
				'label'         => 'What\'s the question?',
				'required'      => false,
				'type'          => 'Text',
			],
			'nro_data_ok'          => [
				'default_value' => '',
				'label'         => 'I am happy for my data to be shared with my local Greenpeace office.',
				'required'      => false,
				'type'          => 'Checkbox',
			],
		];

		// Set dummy engaging networks api keys.
		codecept_debug( 'Setting engaging networks api dummy keys' );
		$I->cli( [ 'option', 'update', '--allow-root', 'p4en_main_settings', escapeshellarg('{"p4en_private_api": "11119999"}'), '--format=json' ] );
	}

	/**
	 * Creates a new enform and adds various fields in it.
	 *
	 * @group enform
	 * @group engaging-networks
	 *
	 * @param AcceptanceTester $I
	 *
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function createAnEnForm( AcceptanceTester $I ) {

		$I->wantTo( 'Create a new engaging networks form' );

		// Read sample ENS API responses and cache them to avoid trying to do the actual calls during testing.
		$jsonf          = file_get_contents( __DIR__ . '/../_support/plugins/engagingnetworks/ensapi_sample_fields_response.json' );
		$jsonq          = file_get_contents( __DIR__ . '/../_support/plugins/engagingnetworks/ensapi_sample_questions_response.json' );
		$json_question  = file_get_contents( __DIR__ . '/../_support/plugins/engagingnetworks/ensapi_sample_question_236734_response.json' );
		$json_optin     = file_get_contents( __DIR__ . '/../_support/plugins/engagingnetworks/ensapi_sample_question_3877_response.json' );
		$json_dep_optin = file_get_contents( __DIR__ . '/../_support/plugins/engagingnetworks/ensapi_sample_question_220954_response.json' );

		$fields_data    = json_decode( $jsonf, true );
		$questions_data = json_decode( $jsonq, true );
		$question_data  = json_decode( $json_question, true );
		$optin_data     = json_decode( $json_optin, true );
		$dep_optin_data = json_decode( $json_dep_optin, true );

		// Set fields transient.
		$cache_key = 'ens_supporter_fields_response';
		$supporter = escapeshellarg( json_encode( $fields_data['supporter'] ) );
		$I->cli( [ 'cache', 'set', '--allow-root', $cache_key, $supporter, 'transient', '600' ] );

		// Set questions transient.
		$cache_key = 'ens_supporter_questions_response';
		$questions = escapeshellarg( json_encode( $questions_data['questions'] ) );
		$I->cli( [ 'cache', 'set', '--allow-root', $cache_key, $questions, 'transient', '600' ] );

		// Set single question transient.
		$cache_key = 'ens_supporter_question_by_id_response_236734';
		$question  = escapeshellarg( json_encode( $question_data['question.236734'] ) );
		$I->cli( [ 'cache', 'set', '--allow-root', $cache_key, $question, 'transient', '600' ] );

		// Set single optin transient.
		$cache_key = 'ens_supporter_question_by_id_response_3887';
		$optin     = escapeshellarg( json_encode( $optin_data['question.3887'] ) );
		$I->cli( [ 'cache', 'set', '--allow-root', $cache_key, $optin, 'transient', '600' ] );

		// Set dependancy field optin transient.
		$cache_key = 'ens_supporter_question_by_id_response_220954';
		$optin     = escapeshellarg( json_encode( $dep_optin_data['question.220954'] ) );
		$I->cli( [ 'cache', 'set', '--allow-root', $cache_key, $optin, 'transient', '600' ] );

		// Start testing.
		$I->loginAsAdminCached();

		$I->amOnPage( ENForm::$URL );

		$I->see( 'Add New EN Form', 'h1.wp-heading-inline' );

		$I->fillField( 'post_title', 'Acceptance Test - ENForm' );

		// Assert that the metaboxes exist in the page.
		$I->see( 'Form preview', ENForm::$metaboxHeading );
		$I->see( 'Selected Components', ENForm::$metaboxHeading );
		$I->see( 'Available Fields', ENForm::$metaboxHeading );
		$I->see( 'Available Questions', ENForm::$metaboxHeading );
		$I->see( 'Available Opt-ins', ENForm::$metaboxHeading );

		// Add fields to the form.
		$form_fields = array_merge( $this->form_fields['fields'], $this->form_fields['questions'] );
		foreach ( $form_fields as $form_field ) {
			$form_field_name = $form_field['name'];
			$I->click( "button[data-name='$form_field_name']" );
			$I->waitForJqueryAjax();
		}

		// Set fields attributes.
		$index = 1;
		foreach ( $form_fields as $form_field ) {

			// Select field type.
			$field_name   = $form_field['name'];
			$field_entype = $form_field['type'] ?? '';
			$I->selectOption( "tr:nth-child($index) .field-type-select", $this->form_fields_attributes[ $field_name ] );

			// Set the required checkbox.
			if ( $this->form_fields_attributes[ $field_name ]['required'] ) {
				$I->clickWithLeftButton( "tr:nth-child($index) input[type='checkbox']" );
			}

			// Set field label.
			if ( ! empty( $this->form_fields_attributes[ $field_name ]['label'] ) && 'OPT' !== $field_entype ) {
				$I->fillField( "tr:nth-child($index) input[type='text']", $this->form_fields_attributes[ $field_name ]['label'] );
			}

			// Set field default value.
			if ( ! empty( $this->form_fields_attributes[ $field_name ]['default_value'] ) ) {
				$I->click( "tr:nth-child($index) .dashicons-edit" );

				$field_en_id   = $form_field['id'];
				$default_value = $this->form_fields_attributes[ $field_name ]['default_value'];

				$I->fillField( ".dialog-$field_en_id  input[data-attribute='default_value']", $default_value );
				$I->click( ".dialog-$field_en_id button[title='Close']" );
			}
			$index ++;
		}

		// Set dependency field value.
		$index = 1;
		foreach ( $form_fields as $form_field ) {
			$field_name   = $form_field['name'];
			if ( isset( $this->form_fields_attributes[ $field_name ]['dependency'] ) && ! empty( $this->form_fields_attributes[ $field_name ]['dependency'] ) ) {
				$I->click( "tr:nth-child($index) .dashicons-edit" );

				$field_en_id   = $form_field['id'];
				$depends_on = $this->form_fields_attributes[ $field_name ]['dependency'];
				$I->selectOption( ".dialog-$field_en_id .dependency-select", $depends_on );
				$I->click( ".dialog-$field_en_id button[title='Close']" );
			}
			$index ++;
		}

		// Reorder last field with the second to last field to assert that ordering fields works.
		$I->dragAndDrop( 'tr:last-child span.dashicons-sort', 'tr:nth-last-child(2) span.dashicons-sort' );

		// Reflect the previous reordering to form fields array to match the assertions further down in the test.
		$temp                                     = $form_fields[ count( $form_fields ) - 1 ];
		$form_fields[ count( $form_fields ) - 1 ] = $form_fields[ count( $form_fields ) - 2 ];
		$form_fields[ count( $form_fields ) - 2 ] = $temp;

		// Publish post
		$I->click( 'Publish' );

		$I->see( 'Edit EN Form', 'h1' );

		// Assert that the fields were saved with the expected attributes/values.
		$index = 1;
		foreach ( $form_fields as $form_field ) {

			// Select field type.
			$field_name   = $form_field['name'];
			$field_entype = $form_field['type'] ?? '';

			$I->seeElement(
				'tr.field-item',
				[
					'data-en-name' => $field_name,
				]
			);

			// Check if 'required' checkbox is checked.
			if ( $this->form_fields_attributes[ $field_name ]['required'] ) {
				$I->seeCheckboxIsChecked( "tr:nth-child($index) input[type='checkbox']" );
			}

			// Check the field label.
			if ( ! empty( $this->form_fields_attributes[ $field_name ]['label'] ) && 'OPT' !== $field_entype ) {
				$I->seeInField( "tr:nth-child($index) input[type='text']", $this->form_fields_attributes[ $field_name ]['label'] );
			}

			// Check the field default value.
			if ( ! empty( $this->form_fields_attributes[ $field_name ]['default_value'] ) ) {
				$I->click( "tr:nth-child($index) .dashicons-edit" );

				$field_en_id   = $form_field['id'];
				$default_value = $this->form_fields_attributes[ $field_name ]['default_value'];

				$I->seeInField( ".dialog-$field_en_id  input[data-attribute='default_value']", $default_value );
				$I->click( ".dialog-$field_en_id button[title='Close']" );
			}
			$index ++;
		}

		// Save enform post id for next text.
		$this->enform_id = $I->executeJS( 'return $("#post_ID").val()' );
	}

	/**
	 * Create a WordPress page and add an enblock(full-width) in it and check the enform on frontend page.
	 *
	 * @depends createAnEnForm
	 * @group enform
	 * @group engaging-networks
	 *
	 * @param AcceptanceTester $I
	 *
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function createAndTestEnBlockFullWidth( AcceptanceTester $I ) {

		$I->wantTo( 'create a new page that contains an EN form with "full-width" style and fill the form' );

		$blocks_attribute = [];

		$blocks_attribute[ ENBlock::$enPageSelect ] = 20002;

		// Select goal.
		$blocks_attribute[ ENBlock::$goalSelect ] = 'Contact Form';

		// Select form style.
		$blocks_attribute[ ENBlock::$formStyleInputName ] = 'full-width';
		$blocks_attribute[ ENBlock::$descriptionField ]   = 'enform block description';
		$blocks_attribute[ ENBlock::$buttonTextField ]    = 'Call to Action';

		// Select enform post.
		$blocks_attribute[ ENBlock::$enFormSelect ] = $this->enform_id;


		// Generate an enblock shortcode.
		$generated_enformblock = $I->generateGutenberg( ENBlock::$shortcodeName, $blocks_attribute );

		$slug = $I->generateRandomSlug();

		$page_id = $I->havePageInDatabase([
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_content' => $generated_enformblock
		]);

		// Navigate to the newly created page
		$I->amOnPage('/' . $slug);

		// Create an array with the engaging networks form values to assert the en api request payload later in the test.
		$user_form_data = [
			'standardFieldNames' => true,
		];

		// Check dependancy field.
		foreach ( $this->form_fields['questions'] as $form_field ) {
			$field_name = $form_field['name'];
			$field_type = $this->form_fields_attributes[ $field_name ]['type'];
			$depends_on = $this->form_fields_attributes[ $field_name ]['dependency'] ?? '';

			if ( 'Checkbox' === $field_type && $depends_on ) {
				// Tested the dependancy field is disabled
				$is_disabled = $I->grabAttributeFrom( '.dependency-' . $depends_on, 'disabled' );
				$I->assertEquals( "true", $is_disabled );
			}
		}

		// Fill the form's questions/checkboxes.
		foreach ( $this->form_fields['questions'] as $form_field ) {
			$field_name = $form_field['name'];
			$field_type = $this->form_fields_attributes[ $field_name ]['type'];

			if ( in_array( $field_type, [ 'Email', 'Text' ] ) ) {
				$I->fillField( 'supporter.questions.' . $form_field['id'], 'Lorem ipsum' );
				$user_form_data['supporter']['questions'][ 'question.' . $form_field['id'] ] = 'Lorem ipsum';
			} else if ( 'Checkbox' === $field_type ) {
				$user_form_data['supporter']['questions'][ 'question.' . $form_field['id'] ] = 'Y';
				$I->click('.en__field--' . $form_field['id'] . ' .custom-control-description');
			}
		}

		// Fill the form's fields
		foreach ( $this->form_fields['fields'] as $form_field ) {
			$field_name  = $form_field['name'];
			$field_type  = $this->form_fields_attributes[ $field_name ]['type'];
			$field_value = $this->form_fields_attributes[ $field_name ]['default_value'];
			if ( in_array( $field_type, [ 'Email', 'Text' ] ) ) {

				$I->fillField( 'supporter.' . $form_field['property'], 'Lorem ipsum' );
				$user_form_data['supporter'][ $form_field['property'] ] = 'Lorem ipsum';
			} else if ( 'Hidden' === $field_type ) {

				$I->seeElementInDOM( 'input',
					[
						'name'  => 'supporter.' . $form_field['property'],
						'value' => $field_value,
					]
				);
				$user_form_data['supporter'][ $form_field['property'] ] = $field_value;
			}
		}

		$I->click( '#p4en_form_save_button' );

		// Check email validation.
		$I->see( 'Please enter a valid e-mail address.' );

		// Fill a correct email to continue with the form submit.
		$I->fillField( 'supporter.emailAddress', 'test@example.com' );
		$user_form_data['supporter']['emailAddress'] = 'test@example.com';

		$json_body = $I->executeJS( "return document.getElementById('form-data').dataset.postdata;" );

		// Assert that the generated json payload contains all fields.
		$expected = $user_form_data;
		$actual = json_decode( $json_body, true );
		$this->recursive_ksort($expected);
		$this->recursive_ksort($actual);
		$I->assertEquals( $expected, $actual );
	}

	/**
	 * Create a WordPress page and add an enblock(full-width-bg) in it and check the enform on frontend page.
	 *
	 * @depends createAnEnForm
	 * @group enform
	 * @group engaging-networks
	 *
	 * @param AcceptanceTester $I
	 *
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function createAndTestEnBlockFullWidthBG( AcceptanceTester $I ) {

		$I->wantTo( 'create a new page that contains an EN form with "full-width-bg" style and fill the form' );

		$blocks_attribute = [];

		$blocks_attribute[ ENBlock::$enPageSelect ] = 20002;

		// Select goal.
		$blocks_attribute[ ENBlock::$goalSelect ] = 'Contact Form';

		// Select form style.
		$blocks_attribute[ ENBlock::$formStyleInputName ] = 'full-width';
		$blocks_attribute[ ENBlock::$descriptionField ]   = 'enform block description';
		$blocks_attribute[ ENBlock::$buttonTextField ]    = 'Call to Action';

		// Select enform post.
		$blocks_attribute[ ENBlock::$enFormSelect ] = $this->enform_id;


		// Generate an enblock shortcode.
		$generated_enformblock = $I->generateGutenberg( ENBlock::$shortcodeName, $blocks_attribute );

		$slug = $I->generateRandomSlug();

		$page_id = $I->havePageInDatabase([
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_content' => $generated_enformblock
		]);

		// Navigate to the newly created page
		$I->amOnPage('/' . $slug);

		// Create an array with the engaging networks form values to assert the en api request payload later in the test.
		$user_form_data = [
			'standardFieldNames' => true,
		];

		// Check dependancy field.
		foreach ( $this->form_fields['questions'] as $form_field ) {
			$field_name = $form_field['name'];
			$field_type = $this->form_fields_attributes[ $field_name ]['type'];
			$depends_on = $this->form_fields_attributes[ $field_name ]['dependency'] ?? '';

			if ( 'Checkbox' === $field_type && $depends_on ) {
				// Tested the dependancy field is disabled
				$is_disabled = $I->grabAttributeFrom( '.dependency-' . $depends_on, 'disabled' );
				$I->assertEquals( "true", $is_disabled );
			}
		}

		// Fill the form's questions/checkboxes.
		foreach ( $this->form_fields['questions'] as $form_field ) {
			$field_name = $form_field['name'];
			$field_type = $this->form_fields_attributes[ $field_name ]['type'];

			if ( in_array( $field_type, [ 'Email', 'Text' ] ) ) {
				$I->fillField( 'supporter.questions.' . $form_field['id'], 'Lorem ipsum' );
				$user_form_data['supporter']['questions'][ 'question.' . $form_field['id'] ] = 'Lorem ipsum';
			} else if ( 'Checkbox' === $field_type ) {
				$user_form_data['supporter']['questions'][ 'question.' . $form_field['id'] ] = 'Y';
				$I->click('.en__field--' . $form_field['id'] . ' .custom-control-description');
			}
		}

		// Fill the form's fields
		foreach ( $this->form_fields['fields'] as $form_field ) {
			$field_name  = $form_field['name'];
			$field_type  = $this->form_fields_attributes[ $field_name ]['type'];
			$field_value = $this->form_fields_attributes[ $field_name ]['default_value'];
			if ( in_array( $field_type, [ 'Email', 'Text' ] ) ) {

				$I->fillField( 'supporter.' . $form_field['property'], 'Lorem ipsum' );
				$user_form_data['supporter'][ $form_field['property'] ] = 'Lorem ipsum';
			} else if ( 'Hidden' === $field_type ) {

				$I->seeElementInDOM( 'input',
					[
						'name'  => 'supporter.' . $form_field['property'],
						'value' => $field_value,
					]
				);
				$user_form_data['supporter'][ $form_field['property'] ] = $field_value;
			}
		}

		$I->click( '#p4en_form_save_button' );

		// Check email validation.
		$I->see( 'Please enter a valid e-mail address.' );

		// Fill a correct email to continue with the form submit.
		$I->fillField( 'supporter.emailAddress', 'test@example.com' );
		$user_form_data['supporter']['emailAddress'] = 'test@example.com';

		$json_body = $I->executeJS( "return document.getElementById('form-data').dataset.postdata;" );

		// Assert that the generated json payload contains all fields.
		$expected = $user_form_data;
		$actual = json_decode( $json_body, true );
		$this->recursive_ksort($expected);
		$this->recursive_ksort($actual);
		$I->assertEquals( $expected, $actual );
	}

	/**
	 * Create a WordPress page and add an enblock(side-style) in it and check the enform on frontend page.
	 *
	 * @depends createAnEnForm
	 * @group enform
	 * @group engaging-networks
	 *
	 * @param AcceptanceTester $I
	 *
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function createAndTestEnBlockSideStyle( AcceptanceTester $I ) {

		$I->wantTo( 'create a new page that contains an EN form with "side-style", fill the form and test it' );

		$blocks_attribute = [];

		$blocks_attribute[ ENBlock::$enPageSelect ] = 20002;

		// Select goal.
		$blocks_attribute[ ENBlock::$goalSelect ] = 'Contact Form';

		// Select form style.
		$blocks_attribute[ ENBlock::$formStyleInputName ] = 'side-style';
		$blocks_attribute[ ENBlock::$descriptionField ]   = 'enform block description';
		$blocks_attribute[ ENBlock::$buttonTextField ]    = 'Call to Action';

		// Select enform post.
		$blocks_attribute[ ENBlock::$enFormSelect ] = $this->enform_id;

		// Generate an enblock shortcode.
		$generated_enformblock = $I->generateGutenberg( ENBlock::$shortcodeName, $blocks_attribute );

		$slug = $I->generateRandomSlug();

		$page_id = $I->havePageInDatabase([
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_content' => $generated_enformblock
		]);

		// Skip cookies box
		$I->setCookie('active_consent_choice', '1');
		$I->setCookie('greenpeace', '1');
		// Navigate to the newly created page
		$I->amOnPage('/' . $slug);

		// Create an array with the engaging networks form values to assert the en api request payload later in the test.
		$user_form_data = [
			'standardFieldNames' => true,
		];

		// Check dependancy field.
		foreach ( $this->form_fields['questions'] as $form_field ) {
			$field_name = $form_field['name'];
			$field_type = $this->form_fields_attributes[ $field_name ]['type'];
			$depends_on = $this->form_fields_attributes[ $field_name ]['dependency'] ?? '';

			if ( 'Checkbox' === $field_type && $depends_on ) {
				// Tested the dependancy field is disabled
				$is_disabled = $I->grabAttributeFrom( '.dependency-' . $depends_on, 'disabled' );
				$I->assertEquals( "true", $is_disabled );
			}
		}

		// Fill the form's questions/checkboxes.
		foreach ( $this->form_fields['questions'] as $form_field ) {
			$field_name = $form_field['name'];
			$field_type = $this->form_fields_attributes[ $field_name ]['type'];

			if ( in_array( $field_type, [ 'Email', 'Text' ] ) ) {
				$I->fillField( 'supporter.questions.' . $form_field['id'], 'Lorem ipsum' );
				$user_form_data['supporter']['questions'][ 'question.' . $form_field['id'] ] = 'Lorem ipsum';
			} else if ( 'Checkbox' === $field_type ) {
				$user_form_data['supporter']['questions'][ 'question.' . $form_field['id'] ] = 'Y';
				$I->scrollTo('.en__field--' . $form_field['id']);
				$I->click('.en__field--' . $form_field['id'] . ' label');
			}
		}

		// Fill the form's fields
		foreach ( $this->form_fields['fields'] as $form_field ) {
			$field_name  = $form_field['name'];
			$field_type  = $this->form_fields_attributes[ $field_name ]['type'];
			$field_value = $this->form_fields_attributes[ $field_name ]['default_value'];
			if ( in_array( $field_type, [ 'Email', 'Text' ] ) ) {

				$I->fillField( 'supporter.' . $form_field['property'], 'Lorem ipsum' );
				$user_form_data['supporter'][ $form_field['property'] ] = 'Lorem ipsum';
			} else if ( 'Hidden' === $field_type ) {

				$I->seeElementInDOM( 'input',
					[
						'name'  => 'supporter.' . $form_field['property'],
						'value' => $field_value,
					]
				);
				$user_form_data['supporter'][ $form_field['property'] ] = $field_value;
			}
		}

		$I->scrollTo( '#p4en_form_save_button' );
		$I->click( '#p4en_form_save_button' );

		// Check email validation.
		$I->see( 'Please enter a valid e-mail address.' );

		// Fill a correct email to continue with the form submit.
		$I->fillField( 'supporter.emailAddress', 'test@example.com' );
		$user_form_data['supporter']['emailAddress'] = 'test@example.com';

		$json_body = $I->executeJS( "return document.getElementById('form-data').dataset.postdata;" );

		// Assert that the generated json payload contains all fields.
		$expected = $user_form_data;
		$actual = json_decode( $json_body, true );
		$this->recursive_ksort($expected);
		$this->recursive_ksort($actual);
		$I->assertEquals( $expected, $actual );
	}

	/**
	 * Recursive sort on array key.
	 *
	 * @param array $data The data.
	 */
	private function recursive_ksort(array &$data): void {
		ksort($data);
		foreach ($data as &$value) {
			is_array($value) && $this->recursive_ksort($value);
		}
	}
}
