<?php
/**
 * @group frontend
 */

$I = new AcceptanceTester( $scenario );

$I->wantTo( 'create and check carousel header(full-width-classic) block' );

$slug = $I->generateRandomSlug();

$slide1 = [
	'image'            => 64,
	'header'           => 'Header 1',
	'description'      => 'Image 1 description',
	'link_text'        => 'Act',
	'link_url'         => '/act/',
	'link_url_new_tab' => 'true',
	'header_size'      => 'h1',
];

$slide2 = [
	'image'            => 65,
	'header'           => 'Header 2',
	'description'      => 'Image 2 description',
	'link_text'        => 'Explore',
	'link_url'         => '/explore/',
	'link_url_new_tab' => 'false',
	'header_size'      => 'h2',
];

$I->havePageInDatabase(
	[
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_content' => $I->generateGutenberg(
			'wp:planet4-blocks/carousel-header',
			[
				'block_style' => 'full-width-classic',
				'slides'      => [ $slide1, $slide2 ],
			]
		),
	]
);

// Navigate to the newly created page.
$I->amOnPage( '/' . $slug );

// Check the Carousel header block.
$I->see( 'Header 1', '.carousel-captions-wrapper > h1' );
$I->see( 'Image 1 description', '.carousel-captions-wrapper > p' );
$I->see( 'Act', 'a.btn-primary' );
// Check open in new tab setting.
$I->canSeeElement( 'a.btn-primary', [ 'target' => '_blank' ] );
$I->scrollTo( '.carousel-header_full-width-classic' );
$I->seeElement( '.carousel-control-prev-icon' );
$I->seeElement( '.carousel-control-next-icon' );
$I->seeNumberOfElements( '.carousel-indicators > li', 2 );

// Click next button.
$I->click( '.carousel-control-next' );
$I->waitForElementVisible( '//div[@class="carousel-inner"]/div[contains(@class, "carousel-item") and position()=2]', 10 ); // secs.
$I->see( 'Header 2', '.carousel-captions-wrapper > h1' );

// Click first indicator.
$I->click( '.carousel-indicators > li:first-child' );
$I->waitForElementVisible( '//div[@class="carousel-inner"]/div[contains(@class, "carousel-item") and position()=1]', 10 ); // secs.
$I->see( 'Header 1', '.carousel-captions-wrapper > h1' );
$I->scrollTo( '.page-template' );
