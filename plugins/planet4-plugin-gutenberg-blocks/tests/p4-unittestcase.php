<?php
/**
 * Contains P4 Unit test case to be used as base test class for p4 blocks tests
 *
 * @package P4BKS
 */

/**
 * Class P4_UnitTestCase
 */
abstract class P4_UnitTestCase extends WP_UnitTestCase {

	/**
	 * Sets up a P4 Unit Test.
	 */
	public function setUp() {

		// Create custom taxonomy p4-page-type.
		register_taxonomy( 'p4-page-type', 'P4_CustomTaxonomyTest' );

		// Create a user with editor role.
		$this->factory->user->create(
			[
				'role'       => 'editor',
				'user_login' => 'p4_editor',
			]
		);

		// Create p4-page-type terms.
		$this->factory->term->create(
			[
				'name'     => 'Story',
				'taxonomy' => 'p4-page-type',
				'slug'     => 'story',
			]
		);
		$this->factory->term->create(
			[
				'name'     => 'Publication',
				'taxonomy' => 'p4-page-type',
				'slug'     => 'publication',
			]
		);
		$this->factory->term->create(
			[
				'name'     => 'Press Release',
				'taxonomy' => 'p4-page-type',
				'slug'     => 'press-release',
			]
		);

		$this->factory->term->create(
			[
				'name'     => 'ArcticSunrise',
				'taxonomy' => 'post_tag',
				'slug'     => 'arcticsunrise',
			]
		);
	}

	/**
	 * Helper method to test private methods.
	 *
	 * @param Object $object Object which will be used to invoke it's function.
	 * @param string $method_name Method name of the object.
	 * @param array  $parameters Parameters array for the method.
	 *
	 * @return mixed
	 */
	protected function invokeMethod( &$object, $method_name, array $parameters = [] ) {
		try {
			$reflection = new \ReflectionClass( get_class( $object ) );
			$method     = $reflection->getMethod( $method_name );
			$method->setAccessible( true );

			return $method->invokeArgs( $object, $parameters );

		} catch ( \ReflectionException $e ) {
			return null;
		}
	}

	/**
	 * Get p4 page type term id by providing the term's slug
	 *
	 * @param string $slug Term's slug.
	 *
	 * @return int  Term id
	 */
	protected function get_custom_term_id( $slug ) {
		$term = get_term_by( 'slug', $slug, 'p4-page-type' );
		if ( $term instanceof \WP_Term ) {
			return $term->term_id;
		}

		return 0;
	}

	/**
	 * Get tag's term id by providing the tag's slug
	 *
	 * @param string $slug Tag's slug.
	 *
	 * @return int  Term id
	 */
	protected function get_tag_id( $slug ) {
		$term = get_term_by( 'slug', $slug, 'post_tag' );
		if ( $term instanceof \WP_Term ) {
			return $term->term_id;
		}

		return 0;
	}
}
