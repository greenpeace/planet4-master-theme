<?php
/**
 * Fields class
 *
 * @package P4EN
 */

namespace P4GEN\Controllers\Api;

use P4GEN\Model\Fields_Model;

/**
 * WP REST API Fields Controller.
 */
class Fields_Controller {

	/**
	 * WordPress option name for storing fields.
	 *
	 * @access private
	 * @var string
	 */
	private $fields_option = 'planet4-en-fields';

	/**
	 * Fields model for storing/retrieving fields from db.
	 *
	 * @access private
	 * @var Fields_Model
	 */
	private $model;

	/**
	 * Default constructor.
	 */
	public function __construct() {
		$this->model = new Fields_Model();
	}

	/**
	 * Validate field's attributes.
	 *
	 * @param array $field The field attributes to be validated.
	 *
	 * @return array|bool
	 */
	private function validate_field( $field ) {
		if ( ! is_array( $field ) || empty( $field ) ) {
			return [ 'No data' ];
		}

		$messages = [];
		if ( ! isset( $field['name'] ) ) {
			$messages[] = 'Name is not set';
		} elseif ( 1 !== preg_match( '/[A-Za-z0-9_\-\.]+$/', $field['name'] ) ) {
			$messages[] = 'Name should contain alphanumeric characters';
		}

		if ( ! isset( $field['mandatory'] ) ) {
			$messages[] = 'Mandatory is not set';
		} elseif ( ! rest_is_boolean( $field['mandatory'] ) ) {
			$messages[] = 'Mandatory should be boolean';
		}

		if ( ! isset( $field['type'] ) ) {
			$messages[] = 'Type is not set';
		} elseif ( ! in_array( $field['type'], [ 'text', 'country', 'question', 'number' ], true ) ) {
			$messages[] = 'Type should be one of these values: text, country, question';
		}

		if ( empty( $messages ) ) {
			return true;
		}

		return $messages;
	}

	/**
	 * Callback for add field api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function add_field( \WP_REST_Request $request ) {

		// Get field data.
		$field_data = $request->get_json_params();

		// Validate field data.
		$validation = $this->validate_field( $field_data );
		if ( true !== $validation ) {
			$response_data = [
				'messages' => $validation,
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 400 );

			return $response;
		}

		// Add field to en WordPress option.
		$updated = $this->model->add_field( $field_data );
		if ( ! $updated ) {
			$response_data = [
				'messages' => [ 'Field could not be added' ],
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 500 );

			return $response;
		}

		$field = $this->model->get_field( $field_data['id'] );

		$response_data = [
			'messages' => [ 'Field created successfully' ],
			'field'    => $field,
		];
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Callback for get field api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_Error| \WP_REST_Response
	 */
	public function get_field( \WP_REST_Request $request ) {

		// Get field id.
		$id            = $request['id'];
		$field         = $this->model->get_field( $id );
		$response_data = $field;
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Callback for get fields api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_Error| \WP_REST_Response
	 */
	public function get_fields( \WP_REST_Request $request ) {
		$fields        = $this->model->get_fields();
		$response_data = $fields;
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Callback for delete field api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete_field( \WP_REST_Request $request ) {

		// Get field id.
		$id = $request['id'];

		// Add field to en WordPress option.
		$updated = $this->model->delete_field( $id );
		if ( ! $updated ) {
			$response_data = [
				'messages' => [ 'Field could not be added' ],
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 500 );

			return $response;
		}

		$response_data = [
			'messages' => [],
		];
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 200 );

		return $response;
	}


	/**
	 * Callback for update field api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function update_field( \WP_REST_Request $request ) {

		// Get field data.
		$field_data = $request->get_json_params();

		// Validate field data.
		$validation = $this->validate_field( $field_data );
		if ( true !== $validation ) {
			$response_data = [
				'messages' => $validation,
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 400 );

			return $response;
		}

		// Add field to en WordPress option.
		$updated = $this->model->update_field( $field_data );
		if ( ! $updated ) {
			$response_data = [
				'messages' => [ 'Field could not be added' ],
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 500 );

			return $response;
		}

		$field         = $this->model->get_field( $field_data['id'] );
		$response_data = [
			'messages' => [],
			'field'    => $field,
		];
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 200 );

		return $response;
	}
}
