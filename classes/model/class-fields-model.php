<?php
/**
 * Fields model
 *
 * @package P4EN
 */

namespace P4GEN\Model;

if ( ! class_exists( 'Fields_Model' ) ) {

	/**
	 * Class Fields_Model
	 */
	class Fields_Model extends Model {

		/**
		 * Fields option
		 *
		 * @var string
		 */
		private $fields_option = 'planet4-en-fields';

		/**
		 * Retrieve a field by id.
		 *
		 * @param mixed $id Field id.
		 *
		 * @return array
		 */
		public function get_field( $id ) {
			$options = get_option( $this->fields_option );

			if ( isset( $options['fields'] ) && ! empty( $options['fields'] ) ) {
				$fields = $options['fields'];
				foreach ( $fields as $field ) {
					if ( (int) $field['id'] === (int) $id ) {
						return $field;
					}
				}
			}

			return [];
		}

		/**
		 * Retrieve all the fields.
		 *
		 * @return array
		 */
		public function get_fields() : array {
			$options = get_option( $this->fields_option );
			$fields  = $options ? array_values( $options ) : [];
			return $fields;
		}

		/**
		 * Add field.
		 *
		 * @param array $field Field attributes.
		 *
		 * @return bool
		 */
		public function add_field( $field ) {

			$options = get_option( $this->fields_option );      // Added default value for the first time.
			if ( is_array( $options ) || false === $options ) {
				$fields   = array_values( $options );
				$fields[] = $field;
				$updated  = update_option( $this->fields_option, $fields );

				return $updated;
			}

			return false;
		}

		/**
		 * Update field.
		 *
		 * @param array $field Field attributes.
		 *
		 * @return bool
		 */
		public function update_field( $field ) {
			$options = get_option( $this->fields_option );

			if ( is_array( $options ) ) {
				$fields        = array_values( $options );
				$index         = false;
				$fields_length = count( $fields );
				for ( $i = 0; $i < $fields_length; $i ++ ) {
					if ( (int) $fields[ $i ]['id'] === (int) $field['id'] ) {
						$index = $i;
						break;
					}
				}
				if ( $index >= 0 ) {
					$fields[ $index ] = $field;
					$updated          = update_option( $this->fields_option, $fields );

					return $updated;
				}
			}

			return false;
		}

		/**
		 * Delete field.
		 *
		 * @param mixed $id Field id.
		 *
		 * @return bool
		 */
		public function delete_field( $id ) {
			$options = get_option( $this->fields_option );
			if ( is_array( $options ) ) {
				$fields  = $options;
				$fields  =
					array_filter(
						$fields,
						function ( $e ) use ( $id ) {
							return (int) $e['id'] !== (int) $id;
						}
					);
				$updated = update_option( $this->fields_option, $fields );

				return $updated;
			}

			return false;
		}
	}
}
