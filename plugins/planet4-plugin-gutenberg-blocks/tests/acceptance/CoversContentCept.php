<?php
use \Codeception\Util\Locator;

$I = new AcceptanceTester($scenario);

$I->wantTo('create and check covers block content style');

$slug = $I->generateRandomSlug();

$I->havePageInDatabase([
	'post_name'    => $slug,
	'post_status'  => 'publish',
	'post_content' => $I->generateGutenberg('wp:planet4-blocks/covers', [
		'cover_type'  => '3',
		'title'       => 'Content',
		'tags'        => [7],
		'description' => 'Description text',
		'covers_view' => '1'
	])
]);

// Navigate to the newly created page
$I->amOnPage('/' . $slug);

// Check the Covers block
$I->see('Content', 'h2.page-section-header');
$I->see('Description text', 'div.page-section-description');
$I->see('Duis posuere', 'h5 > a');
$I->seeElement('.publication-date');
$I->seeElement('.content-covers-block-symbol img');

