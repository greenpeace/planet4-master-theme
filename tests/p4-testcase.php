<?php

/**
 * Class P4_TestCase.
 */
class P4_TestCase extends WP_UnitTestCase {

	function setUp() {

		parent::setUp();
		self::_setupStarterTheme();
		$this->initialize_planet4_data();
		require_once( get_template_directory() . '/functions.php' );
	}


	static function _setupStarterTheme() {
		$dest = WP_CONTENT_DIR . '/themes/planet4-master-theme/';
		$src  = __DIR__ . '/../../planet4-master-theme/';
		if ( is_dir( $src ) ) {
			self::_copyDirectory( $src, $dest );
			switch_theme( 'planet4-master-theme' );
		} else {
			echo 'no its not';
		}
	}

	static function _copyDirectory( $src, $dst ) {
		$dir = opendir( $src );
		@mkdir( $dst );
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					self::_copyDirectory( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	}

	/**
	 * Use wp unit testcase factories to create data in database for the tests.
	 */
	function initialize_planet4_data() {

		// Create Act & Explore pages
		// Accepts the same arguments as wp_insert_post.
		$act_page_id = $this->factory->post->create( [
			'post_type'  => 'page',
			'post_title' => 'ACT',
			'post_name'  => 'act',
		] );

		$explore_page_id = $this->factory->post->create( [
			'post_type'  => 'page',
			'post_title' => 'EXPLORE',
			'post_name'  => 'explore',
		] );

		$this->factory->post->create( [
			'post_type'   => 'page',
			'post_title'  => 'Take action page',
			'post_name'   => 'take-action-page',
			'post_parent' => $act_page_id,
		] );

		$issue_cat_id = $this->factory->category->create( [
			'name'        => 'Issues',
			'slug'        => 'issues',
			'description' => 'Issues we work on',
		] );

		$issues_cat = get_category_by_slug( 'issues' );
		$this->factory->category->create( [
			'name'        => 'Nature',
			'slug'        => 'nature',
			'parent'      => $issues_cat->term_id,
			'description' => 'Focusing on great global forests and oceans we aim to preserve, 
		                             protect and restore the most valuable 
		                             ecosystems for the climate and for biodiversity.',
		] );

		// Create p4-page-type terms.
		$term_id = $this->factory->term->create( [
			'name'     => 'Story',
			'taxonomy' => 'p4-page-type',
			'slug'     => 'story',
		] );
		$term_id = $this->factory->term->create( [
			'name'     => 'Publication',
			'taxonomy' => 'p4-page-type',
			'slug'     => 'publication',
		] );
		$term_id = $this->factory->term->create( [
			'name'     => 'Press Release',
			'taxonomy' => 'p4-page-type',
			'slug'     => 'press-release',
		] );
	}

}
