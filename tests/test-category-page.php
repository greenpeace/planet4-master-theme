<?php
/**
 * Category Page Test Class
 *
 * @package P4MT
 */

use SteveGrunwell\PHPUnit_Markup_Assertions\MarkupAssertionsTrait;

/**
 * Class CategoryPageTest.
 * Test category php template and category twig template.
 */
class CategoryPageTest extends P4_TestCase {

	use MarkupAssertionsTrait;

	/**
	 * Test tease taxonomy post template with a post having category, tag and page_type.
	 */
	public function test_tease_taxonomy_post_template_with_category_tag_and_page_type() {

		// Get editor user.
		$user = get_user_by( 'login', 'p4_author' );
		wp_set_current_user( $user->ID );

		$post_data     = $this->get_posts()['post_with_category_tag_custom_term'];
		$post          = $this->factory->post->create_and_get( $post_data );
		$attachment_id = $this->factory->attachment->create_upload_object( dirname( __DIR__ ) . '/tests/data/images/pressmedia.jpg', 0 );
		set_post_thumbnail( $post, $attachment_id );

		// Wrap WP_Post around P4_Post.
		$post   = new P4_Post( $post->ID );
		$output = TimberHelper::ob_function(
			function () use ( $post ) {
					Timber::render( 'tease-taxonomy-post.twig', [ 'post' => $post ] );
			}
		);

		// Test image markup.
		$this->assertHasElementWithAttributes(
			[
				'src' => get_the_post_thumbnail_url( $post->ID, 'thumbnail' ),
				'alt' => $post->post_title,
			],
			$output,
			'Did not find post thumbnail.'
		);

		// Test tag markup.
		$this->assertElementContains(
			'ArcticSunrise',
			'a.search-result-item-tag',
			$output,
			'The template does not contain tag markup'
		);

		// Test page type markup.
		$this->assertElementContains(
			'Story',
			'a.page-type',
			$output,
			'The template does not contain page type markup'
		);
	}

	/**
	 * Test tease taxonomy post template with a post having category, tag but not page_type term.
	 */
	public function test_tease_taxonomy_post_template_with_category_tag() {

		// Get editor user.
		$user = get_user_by( 'login', 'p4_author' );
		wp_set_current_user( $user->ID );

		$post_data = $this->get_posts()['post_with_category_tag'];
		$post      = $this->factory->post->create_and_get( $post_data );

		// Wrap WP_Post around P4_Post.
		$post   = new P4_Post( $post->ID );
		$output = TimberHelper::ob_function(
			function () use ( $post ) {
					Timber::render( 'tease-taxonomy-post.twig', [ 'post' => $post ] );
			}
		);

		// Test tag markup.
		$this->assertElementContains(
			'ArcticSunrise',
			'a.search-result-item-tag',
			$output,
			'The template does not contain tag markup'
		);

		// Test page type markup.
		// Should assert true, every post gets a p4 page type term assigned to it.
		$this->assertContainsSelector( 'a.page-type', $output, 'Did not find an image in the page body.' );
	}

	/**
	 * Test category php template and category twig template.
	 */
	public function test_category_page_results() {

		$user = get_user_by( 'login', 'p4_author' );
		wp_set_current_user( $user->ID );

		$post_data = $this->get_posts()['post_with_category_tag_custom_term'];
		$posts     = $this->factory->post->create_many( 10, $post_data );

		// Get the ID of the nature category.
		$category_id = get_cat_ID( 'Nature' );

		$permalink = get_category_link( $category_id );
		$this->go_to( $permalink );

		$this->assertFalse( is_404() );
		$this->assertTrue( is_category() );

		$output = TimberHelper::ob_function(
			function () {
					include get_template_directory() . '/category.php';
			}
		);

		// Test that contains 10 posts in the markup.
		$this->assertSelectorCount( 10, 'li.search-result-list-item', $output );
	}


	/**
	 * Provide posts data to be used by wp factories.
	 *
	 * @return array
	 */
	public function get_posts() {

		return [

			'post_with_category_tag_custom_term' => [
				'post_type'     => 'post',
				'post_title'    => 'The name of the place is Babylon',
				'post_name'     => 'test-social-url',
				'post_content'  => 'test content',
				'post_category' => [
					get_category_by_slug( 'nature' )->term_id,
				],
				'tags_input'    => [
					'arcticsunrise',
				],
				'tax_input'     => [
					'p4-page-type' => [ 'story' ],
				],
			],
			'post_with_category_tag'             => [
				'post_type'     => 'post',
				'post_title'    => 'The name of the place is Babylon',
				'post_name'     => 'test-social-url',
				'post_content'  => 'test content',
				'post_category' => [
					get_category_by_slug( 'nature' )->term_id,
				],
				'tags_input'    => [
					'arcticsunrise',
				],
			],
			'post_with_category'                 => [
				'post_type'     => 'post',
				'post_title'    => 'The name of the place is Babylon',
				'post_name'     => 'test-social-url',
				'post_content'  => 'test content',
				'post_category' => [
					get_category_by_slug( 'nature' )->term_id,
				],
			],
		];
	}
}
