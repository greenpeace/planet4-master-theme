<?php
/**
 * Test p4_page_type terms custom permalinks.
 *
 * @package P4MT
 */

/**
 * Class CustomPermalinksTest
 */
class CustomPermalinksTest extends P4_TestCase {

	/**
	 * Test permalink exists
	 */
	public function test_p4_page_type_permalink_exists() {
		$available_tags = [
			// translators: %s = year of the post in four digits.
			'year' => __( '%s (The year of the post, four digits, for example 2004.)', 'planet4-master-theme-backend' ),
		];
		$available_tags = apply_filters( 'available_permalink_structure_tags', $available_tags );
		$this->assertArrayHasKey( 'p4_page_type', $available_tags );
	}

	/**
	 * Test setting permalink structure to /%p4_page_type%/%post_id%/%postname%/
	 */
	public function test_p4_page_type_post_id_postname_permalink_structure() {

		// Set permalink structure.
		$this->set_permalink_structure( '/%p4_page_type%/%post_id%/%postname%/' );

		// Get ACT page.
		$page = get_page_by_path( 'act' );

		// Create a take action page.
		$nature_page = $this->factory->post->create_and_get(
			[
				'post_type'    => 'page',
				'post_title'   => 'Nature page',
				'post_name'    => 'nature-page',
				'post_content' => 'test content',
				'post_parent'  => $page->ID,
			]
		);

		// Create a sample post and assing p4-page-type story term to it.
		$post_id = $this->factory->post->create(
			[
				'post_type'    => 'post',
				'post_title'   => 'The name of the place is Babylon 10.',
				'post_name'    => 'test-taxonomy-url',
				'post_content' => 'test content',
			]
		);
		wp_set_object_terms( $post_id, 'story', 'p4-page-type' );

		$permalink = get_permalink( $nature_page->ID );
		$this->assertStringEndsWith( 'act/nature-page/', $permalink );

		// Test permalink for new post.
		$permalink = get_permalink( $post_id );
		$this->assertStringEndsWith( 'story/' . $post_id . '/test-taxonomy-url/', $permalink );

		// Should return 404 page.
		$this->go_to( '/foo' );
		$this->assertTrue( is_404() );
		$this->assertFalse( is_singular() );

		// Test using p4-page-type in request url.
		$this->go_to( 'story/' . $post_id . '/test-taxonomy-url/' );
		$this->assertFalse( is_404() );
		$this->assertTrue( is_singular() );
		$this->assertTrue( is_single() );

		// Test act page.
		$this->go_to( 'act/' );
		$global_post = get_post();
		$this->assertFalse( is_404() );
		$this->assertEquals( 'ACT', $global_post->post_title );
		$this->assertTrue( is_singular() );
		$this->assertTrue( is_page() );

		// Test take action page.
		$this->go_to( 'act/nature-page/' );
		$global_post = get_post();
		$this->assertEquals( $nature_page->post_title, $global_post->post_title );
		$this->assertFalse( is_404() );
		$this->assertTrue( is_singular() );
		$this->assertTrue( is_page() );

		// Test homepage.
		$this->go_to( '/' );
		$this->assertFalse( is_404() );
		$this->assertTrue( is_home() );
	}

	/**
	 * Test setting permalink structure to /%p4_page_type%/%post_id%/
	 */
	public function test_p4_page_type_post_id_permalink_structure() {

		$this->set_permalink_structure( '/%p4_page_type%/%post_id%/' );

		// Create example_post.
		$post = $this->factory->post->create_and_get(
			[
				'post_type'    => 'post',
				'post_title'   => 'Test p4 page type taxonomy terms in permalinks',
				'post_name'    => 'test-taxonomy-url',
				'post_content' => 'test content',
			]
		);
		wp_set_object_terms( $post->ID, 'story', 'p4-page-type' );

		$permalink = get_permalink( $post->ID );
		$this->assertStringEndsWith( 'story/' . $post->ID . '/', $permalink );

		$this->go_to( 'story/' . $post->ID . '/' );
		$this->assertFalse( is_404() );
		$this->assertTrue( is_singular() );
		$this->assertTrue( is_single() );

		$this->go_to( 'act/' );
		$global_post = get_post();
		$this->assertFalse( is_404() );
		$this->assertEquals( 'ACT', $global_post->post_title );
		$this->assertTrue( is_singular() );
		$this->assertTrue( is_page() );
	}


	/**
	 * Test setting permalink structure to /%p4_page_type%/%postname%/
	 */
	public function test_p4_page_type_postname_permalink_structure() {

		$this->set_permalink_structure( '/%p4_page_type%/%postname%/' );

		// Create example_post.
		$post_id = $this->factory->post->create(
			[
				'post_type'    => 'post',
				'post_title'   => 'Test /%p4_page_type%/%postname%/ permastruct',
				'post_name'    => 'test-taxonomy-url',
				'post_content' => 'test content',
			]
		);
		wp_set_object_terms( $post_id, 'story', 'p4-page-type' );

		$permalink = get_permalink( $post_id );
		$this->assertStringEndsWith( 'story/test-taxonomy-url/', $permalink );

		$this->go_to( 'story/test-taxonomy-url/' );
		$global_post = get_post();
		$this->assertFalse( is_404() );
		$this->assertTrue( is_singular() );
		$this->assertTrue( is_single() );
		$this->assertEquals( 'Test /%p4_page_type%/%postname%/ permastruct', $global_post->post_title );
	}
}
