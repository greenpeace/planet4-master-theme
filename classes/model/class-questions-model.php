<?php
/**
 * Questions model class
 *
 * @package P4EN
 */

namespace P4GEN\Model;

if ( ! class_exists( 'Questions_Model' ) ) {

	/**
	 * Class Questions_Model
	 */
	class Questions_Model extends Model {

		/**
		 * Questions option
		 *
		 * @var string
		 */
		private $questions_option = 'planet4-en-questions';


		/**
		 * Retrieve a question by id.
		 *
		 * @param mixed $id Field id.
		 *
		 * @return array
		 */
		public function get_question( $id ) {
			$options = get_option( $this->questions_option );

			if ( isset( $options['questions'] ) && ! empty( $options['questions'] ) ) {
				$questions = $options['questions'];
				foreach ( $questions as $question ) {
					if ( (int) $question['id'] === (int) $id ) {
						return $question;
					}
				}
			}

			return [];
		}

		/**
		 * Retrieve all the questions.
		 *
		 * @return array
		 */
		public function get_questions(): array {
			$options   = get_option( $this->questions_option );
			$questions = $options ? array_values( $options ) : [];

			return $questions;
		}

		/**
		 * Add question.
		 *
		 * @param array $question Field attributes.
		 *
		 * @return bool
		 */
		public function add_question( $question ) {

			$options = get_option( $this->questions_option );      // Added default value for the first time.
			if ( is_array( $options ) || false === $options ) {
				$questions   = array_values( $options );
				$questions[] = $question;
				$updated     = update_option( $this->questions_option, $questions );

				return $updated;
			}

			return false;
		}

		/**
		 * Update question.
		 *
		 * @param array $question Field attributes.
		 *
		 * @return bool
		 */
		public function update_question( $question ) {
			$options = get_option( $this->questions_option );

			if ( is_array( $options ) ) {
				$questions        = array_values( $options );
				$index            = false;
				$questions_length = count( $questions );
				for ( $i = 0; $i < $questions_length; $i ++ ) {
					if ( (int) $questions[ $i ]['id'] === (int) $question['id'] ) {
						$index = $i;
						break;
					}
				}
				if ( $index >= 0 ) {
					$questions[ $index ] = $question;
					$updated             = update_option( $this->questions_option, $questions );

					return $updated;
				}
			}

			return false;
		}

		/**
		 * Delete question.
		 *
		 * @param mixed $id Field id.
		 *
		 * @return bool
		 */
		public function delete_question( $id ) {
			$options = get_option( $this->questions_option );
			if ( is_array( $options ) ) {
				$questions = $options;
				$questions =
					array_filter(
						$questions,
						function ( $e ) use ( $id ) {
							return (int) $e['id'] !== (int) $id;
						}
					);
				$updated   = update_option( $this->questions_option, $questions );

				return $updated;
			}

			return false;
		}
	}
}
