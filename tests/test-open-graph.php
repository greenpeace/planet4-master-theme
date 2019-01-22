<?php
/**
 * Test custom open graph meta attributes.
 *
 * @package P4MT
 */

use SteveGrunwell\PHPUnit_Markup_Assertions\MarkupAssertionsTrait;

/**
 * Class OpenGraphTest.
 */
class OpenGraphTest extends P4_TestCase {

	use MarkupAssertionsTrait;

	/**
	 * Test that custom open graph meta are rendered on the fronend.
	 *
	 * @param array  $post_data  Post data array.
	 * @param string $template   Php template used to render the page.
	 *
	 * @dataProvider posts_with_custom_og_provider
	 */
	public function test_post_custom_open_graph_data( $post_data, $template ) {

		$attachment_id                             = $this->factory->attachment->create_upload_object( dirname( __DIR__ ) . '/tests/data/images/pressmedia.jpg', 0 );
		$post_data['meta_input']['p4_og_image_id'] = $attachment_id;
		$post_id                                   = $this->factory->post->create( $post_data );

		$permalink = get_permalink( $post_id );
		$this->go_to( $permalink );

		$output = TimberHelper::ob_function(
			function () use ( $template ) {
					include get_template_directory() . '/' . $template;
			}
		);

		$this->assertHasElementWithAttributes(
			[
				'property' => 'og:title',
				'content'  => $post_data['meta_input']['p4_og_title'],
			],
			$output,
			'Did not find og:title meta.'
		);

		$this->assertHasElementWithAttributes(
			[
				'property' => 'og:description',
				'content'  => $post_data['meta_input']['p4_og_description'],
			],
			$output,
			'Did not find og:description meta.'
		);

		$this->assertHasElementWithAttributes(
			[
				'property' => 'og:image',
				'content'  => wp_get_attachment_url( $attachment_id ),
			],
			$output,
			'Did not find og:image meta.'
		);

		$this->assertHasElementWithAttributes(
			[
				'property' => 'og:image:width',
			],
			$output,
			'Did not find og:image:width meta.'
		);
	}

	/**
	 * Test that open graph meta are rendered on the frontend.
	 *
	 * @param array  $post_data  Post data array.
	 * @param string $template   Php template used to render the page.
	 *
	 * @dataProvider posts_provider
	 */
	public function test_post_open_graph_data( $post_data, $template ) {

		// Create a sample post.
		$post_id = $this->factory->post->create( $post_data );

		$permalink = get_permalink( $post_id );
		$this->go_to( $permalink );

		$output = TimberHelper::ob_function(
			function () use ( $template ) {
					include get_template_directory() . '/' . $template;
			}
		);

		$this->assertHasElementWithAttributes(
			[
				'property' => 'og:title',
				'content'  => $post_data['post_title'] . ' - ' . get_bloginfo( 'name' ),
			],
			$output,
			'Did not find og:title meta.'
		);

		$this->assertHasElementWithAttributes(
			[
				'property' => 'og:description',
				'content'  => $post_data['post_excerpt'],
			],
			$output,
			'Did not find og:description meta.'
		);
	}

	/**
	 * Provide test cases for custom open graph values.
	 * Each row contains:
	 * Post data, Php template, Attachment id
	 *
	 * @return array
	 */
	public function posts_with_custom_og_provider() {

		return [
			[
				[
					'post_type'    => 'post',
					'post_title'   => 'The name of the place is Babylon',
					'post_name'    => 'test-social-url',
					'post_content' => 'test content',
					'meta_input'   => [
						'p4_og_title'       => 'Custom open graph title',
						'p4_og_description' => 'Custom open graph description',
					],
				],
				'single.php',
			],
			[
				[
					'post_type'    => 'page',
					'post_title'   => 'The name of the place is Babylon',
					'post_name'    => 'test-social-url',
					'post_content' => 'test content',
					'meta_input'   => [
						'p4_og_title'       => 'Custom open graph title',
						'p4_og_description' => 'Custom open graph description',
					],
				],
				'page.php',
			],

		];
	}

	/**
	 * Provide test cases for open graph values.
	 * Each row contains:
	 * Post data, Php template
	 *
	 * @return array
	 */
	public function posts_provider() {
		return [
			[
				[
					'post_type'    => 'post',
					'post_title'   => 'The name of the place is Babylon',
					'post_name'    => 'test-social-url',
					'post_content' => 'test content',
					'post_excerpt' => 'post excerpt',
				],
				'single.php',
			],
			[
				[
					'post_type'    => 'page',
					'post_title'   => 'The name of the place is Babylon',
					'post_name'    => 'test-social-url',
					'post_content' => 'test content',
					'post_excerpt' => 'post excerpt',
				],
				'page.php',
			],
		];
	}
}
