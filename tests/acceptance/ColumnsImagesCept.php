<?php
$I = new AcceptanceTester( $scenario );

$I->wantTo( 'create and check columns block images style' );

$slug = $I->generateRandomSlug();

$column1 = [
	'title'        => 'Column 1',
	'description'  => 'Column 1 description',
	'attachment'   => 64,
	'cta_link'     => '/act/',
	'cta_text'     => 'Act',
	'link_new_tab' => true,
];

$column2 = [
	'title'       => 'Column 2',
	'description' => 'Column 2 description',
	'attachment'  => 65,
	'cta_link'    => '/explore/',
	'cta_text'    => 'Explore',
];

$I->havePageInDatabase(
	[
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_content' => $I->generateGutenberg(
			'wp:planet4-blocks/columns',
			[
				'columns_block_style' => 'image',
				'columns_title'       => 'Images Columns',
				'columns_description' => 'Images Column Block description',
				'columns'             => [ $column1, $column2 ],
			]
		),
	]
);

// Navigate to the newly created page.
$I->amOnPage( '/' . $slug );

// Check the Tasks style
$I->see( 'Images Columns', '.block-style-image > header > h2' );
$I->see( 'Images Column Block description', '.block-style-image > div.page-section-description' );

// Column 1.
$I->see( 'Column 1', 'h3 > a' );
$I->see( 'Column 1 description', '.column-wrap p' );
$I->seeElement( '.attachment-container > a > img' );
$I->see( 'Act', 'div:nth-child(1) > a.standalone-link' );
// Open in new tab setting.
$I->canSeeElement( 'div:nth-child(1) > a.standalone-link', [ 'target' => '_blank' ] );

// Column 2.
$I->see( 'Column 2', 'h3 > a' );
$I->see( 'Column 2 description', '.column-wrap p' );
$I->seeElement( '.attachment-container > a > img' );
$I->see( 'Explore', 'a.standalone-link' );
