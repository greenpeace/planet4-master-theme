<?php
/**
 * Test p4_page_type custom taxonomy.
 *
 * @package P4MT
 */

/**
 * Class CustomTaxonomyTest
 */
class CustomTaxonomyTest extends P4_TestCase {

	/**
	 * Test that a post has always a p4-page-type term assigned to it.
	 *
	 * @covers P4_Custom_Taxonomy::save_taxonomy_page_type
	 */
	public function test_post_has_a_taxonomy_term_assigned() {

		// Get editor user.
		$user = get_user_by( 'login', 'p4_editor' );
		wp_set_current_user( $user->ID );

		// Create a sample post without assigning a p4-page-type story term to it.
		$post_id = $this->factory->post->create(
			[
				'post_type'    => 'post',
				'post_title'   => 'The name of the place is Babylon',
				'post_name'    => 'test-taxonomy-url',
				'post_content' => 'test content',
			]
		);

		$terms = wp_get_object_terms( $post_id, 'p4-page-type' );

		// Assert that the post has been assigned with a p4-page-type term.
		$this->assertEquals( 1, count( $terms ) );
		$this->assertInstanceOf( 'WP_Term', $terms[0] );

	}

	/**
	 * Test that a post has always a single p4-page-type term assigned to it.
	 *
	 * @covers P4_Custom_Taxonomy::save_taxonomy_page_type
	 */
	public function test_post_has_a_single_taxonomy_term_assigned() {

		// Get editor user.
		$user = get_user_by( 'login', 'p4_editor' );
		wp_set_current_user( $user->ID );

		// Create a sample post and assing p4-page-type story term to it.
		$post_id = $this->factory->post->create(
			[
				'post_type'    => 'post',
				'post_title'   => 'The name of the place is Babylon.',
				'post_name'    => 'test-taxonomy-url',
				'post_content' => 'test content',
				'tax_input'    => array(
					'p4-page-type' => [ 'story', 'publication' ],
				),
			]
		);

		$terms = wp_get_object_terms( $post_id, 'p4-page-type' );
		// Assert that the post has been assigned with a single p4-page-type term.
		$this->assertEquals( 1, count( $terms ) );
		$this->assertEquals( 'publication', $terms[0]->slug );
	}
}
