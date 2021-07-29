<?php
use \Codeception\Util\Locator;

$I = new AcceptanceTester($scenario);

$I->wantTo('create and check covers block campaign style');

$slug = $I->generateRandomSlug();

$I->havePageInDatabase([
	'post_name'    => $slug,
	'post_status'  => 'publish',
	'post_content' => $I->generateGutenberg('wp:planet4-blocks/covers', [
		'cover_type'  => '2',
		'title'       => 'Campaign',
		'tags'        => [6, 20],
		'description' => 'Description text',
		'covers_view' => '1'
	])
]);

// Navigate to the newly created page
$I->amOnPage('/' . $slug);

// Check the Covers block
$I->see('Campaign', 'h2.page-section-header');
$I->see('Description text', 'div.page-section-description');
$I->seeNumberOfElements('.campaign-card-column', 2);
$I->see('#Oceans', 'span.yellow-cta');
$I->seeElement('.thumbnail-large > img');

