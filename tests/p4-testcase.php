<?php
/**
 * P4 Test Case Class
 *
 * @package P4MT
 */

/**
 * Class P4_TestCase.
 */
class P4_TestCase extends WP_UnitTestCase {

	/**
	 * Setup test
	 */
	public function setUp() {
		parent::setUp();
		$this->initialize_planet4_data();
		require_once get_template_directory() . '/functions.php';
	}


	/**
	 * Use wp unit testcase factories to create data in database for the tests.
	 */
	private function initialize_planet4_data() {

		// Create a user with editor role.
		$this->factory->user->create(
			[
				'role'       => 'editor',
				'user_login' => 'p4_editor',
			]
		);

		// Create a user with editor role.
		$this->factory->user->create(
			[
				'role'       => 'author',
				'user_login' => 'p4_author',
			]
		);

		// Get admin user.
		$user = get_user_by( 'login', 'admin' );
		wp_set_current_user( $user->ID );

		// Create Act & Explore pages
		// Accepts the same arguments as wp_insert_post.
		$act_page_id = $this->factory->post->create(
			[
				'post_type'  => 'page',
				'post_title' => 'ACT',
				'post_name'  => 'act',
			]
		);

		$explore_page_id = $this->factory->post->create(
			[
				'post_type'  => 'page',
				'post_title' => 'EXPLORE',
				'post_name'  => 'explore',
			]
		);

		$this->factory->post->create(
			[
				'post_type'   => 'page',
				'post_title'  => 'Take action page',
				'post_name'   => 'take-action-page',
				'post_parent' => $act_page_id,
			]
		);

		$issue_cat_id = $this->factory->category->create(
			[
				'name'        => 'Issues',
				'slug'        => 'issues',
				'description' => 'Issues we work on',
			]
		);

		$issues_cat = get_category_by_slug( 'issues' );
		$this->factory->category->create(
			[
				'name'        => 'Nature',
				'slug'        => 'nature',
				'parent'      => $issues_cat->term_id,
				'description' => 'Focusing on great global forests and oceans we aim to preserve,
								  protect and restore the most valuable
								  ecosystems for the climate and for biodiversity.',
			]
		);

		// Create tag.
		$this->factory->tag->create(
			[
				'name' => 'ArcticSunrise',
				'slug' => 'arcticsunrise',
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

		wp_set_current_user( 0 );
	}
}
