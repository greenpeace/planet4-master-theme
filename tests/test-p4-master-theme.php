<?php

/**
 * Class P4MasterThemeTest
 */
class P4MasterThemeTest extends P4_TestCase {

	function setUp() {
		parent::setUp();
	}

	function tearDown() {
		switch_theme( 'planet4-master-theme' );
	}

	public function testFunctionsPHP() {
		$context = Timber::get_context();
		$this->assertEquals( 'P4_Master_Site', get_class( $context['site'] ) );
		$this->assertTrue( current_theme_supports( 'post-thumbnails' ) );
		$this->assertEquals( 'bar', $context['foo'] );
	}

	public function testLoading() {
		$str = Timber::compile( 'tease.twig' );
		$this->assertStringStartsWith( '<article class="tease tease-" id="tease-">', $str );
		$this->assertStringEndsWith( '</article>', $str );
	}
}
