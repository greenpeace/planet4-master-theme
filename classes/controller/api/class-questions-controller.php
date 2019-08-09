<?php
/**
 * Questions controller
 *
 * @package P4EN
 */

namespace P4GEN\Controllers\Api;

use P4GEN\Model\Questions_Model;
use P4GEN\Controllers\Ensapi_Controller as Ensapi;

/**
 * WP REST API Questions Controller.
 */
class Questions_Controller {

	/**
	 * Questions model for storing/retrieving questions from db.
	 *
	 * @access private
	 * @var Questions_Model
	 */
	private $model;

	/**
	 * Default constructor.
	 */
	public function __construct() {
		$this->model = new Questions_Model();
	}

	/**
	 * Validate question's attributes.
	 *
	 * @param array $question The question attributes to be validated.
	 *
	 * @return array|bool
	 */
	private function validate_question( $question ) {
		if ( ! is_array( $question ) || empty( $question ) ) {
			return [ 'No data' ];
		}

		$messages = [];
		if ( ! isset( $question['name'] ) ) {
			$messages[] = 'Name is not set';
		} elseif ( 1 !== preg_match( '/[A-Za-z0-9_\-\.]+$/', $question['name'] ) ) {
			$messages[] = 'Name should contain alphanumeric characters';
		}

		if ( ! isset( $question['label'] ) ) {
			$messages[] = 'Label is not set';
		} elseif ( '' === $question['label'] ) {
			$messages[] = 'Mandatory should be boolean';
		}

		if ( ! isset( $question['type'] ) ) {
			$messages[] = 'Type is not set';
		} elseif ( ! in_array( $question['type'], [ 'GEN', 'OPT', 'question', 'number' ], true ) ) {
			$messages[] = 'Type should be one of these values: text, country, question';
		}

		if ( empty( $messages ) ) {
			return true;
		}

		return $messages;
	}

	/**
	 * Callback for add question api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function add_question( \WP_REST_Request $request ) {

		// Get question data.
		$question_data = $request->get_json_params();

		// Validate question data.
		$validation = $this->validate_question( $question_data );
		if ( true !== $validation ) {
			$response_data = [
				'messages' => $validation,
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 400 );

			return $response;
		}

		// Add question to en WordPress option.
		$updated = $this->model->add_question( $question_data );
		if ( ! $updated ) {
			$response_data = [
				'messages' => [ 'Question could not be added' ],
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 500 );

			return $response;
		}

		$question = $this->model->get_question( $question_data['id'] );

		$response_data = [
			'messages' => [ 'Question created successfully' ],
			'question' => $question,
		];
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Callback for get question api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_Error| \WP_REST_Response
	 */
	public function get_question( \WP_REST_Request $request ) {

		// Get question id.
		$id            = $request['id'];
		$question      = $this->model->get_question( $id );
		$response_data = $question;
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Callback for get questions api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_Error| \WP_REST_Response
	 */
	public function get_available_questions( \WP_REST_Request $request ) {
		$main_settings = get_option( 'p4en_main_settings' );

		if ( isset( $main_settings['p4en_private_api'] ) ) {

			$ens_private_token   = $main_settings['p4en_private_api'];
			$ens_api             = new Ensapi( $ens_private_token );
			$supporter_questions = $ens_api->get_supporter_questions();
		} else {
			$supporter_questions = [];
		}

		$response = new \WP_REST_Response( $supporter_questions );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Get questions from the model.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_questions( \WP_REST_Request $request ) {
		$questions = $this->model->get_questions();
		$response  = new \WP_REST_Response( $questions );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Callback for delete question api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete_question( \WP_REST_Request $request ) {

		// Get question id.
		$id = $request['id'];

		// Add question to en WordPress option.
		$updated = $this->model->delete_question( $id );
		if ( ! $updated ) {
			$response_data = [
				'messages' => [ 'Question could not be added' ],
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
	 * Callback for update question api route.
	 *
	 * @param \WP_REST_Request $request Rest request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function update_question( \WP_REST_Request $request ) {

		// Get question data.
		$question_data = $request->get_json_params();

		// Validate question data.
		$validation = $this->validate_question( $question_data );
		if ( true !== $validation ) {
			$response_data = [
				'messages' => $validation,
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 400 );

			return $response;
		}

		// Add question to en WordPress option.
		$updated = $this->model->update_question( $question_data );
		if ( ! $updated ) {
			$response_data = [
				'messages' => [ 'Question could not be added' ],
			];
			$response      = new \WP_REST_Response( $response_data );
			$response->set_status( 500 );

			return $response;
		}

		$question      = $this->model->get_question( $question_data['id'] );
		$response_data = [
			'messages' => [],
			'question' => $question,
		];
		$response      = new \WP_REST_Response( $response_data );
		$response->set_status( 200 );

		return $response;
	}
}
