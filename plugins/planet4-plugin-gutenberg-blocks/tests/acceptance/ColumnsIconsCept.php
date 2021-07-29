<?php
use \Codeception\Util\Locator;

$I = new AcceptanceTester($scenario);

$I->wantTo('create and check columns block icons style');

$slug = $I->generateRandomSlug();

$column1 = [
	'title'             => 'Column 1',
	'description'       => 'Column 1 description',
	'attachment'        => 328,
	'cta_link'              => '/act/',
	'cta_text'          => 'Act',
];

$column2 = [
	'title'             => 'Column 2',
	'description'       => 'Column 2 description',
	'attachment'        => 318,
	'cta_link'              => '/explore/',
	'cta_text'          => 'Explore',
];

$I->havePageInDatabase([
	'post_name'    => $slug,
	'post_status'  => 'publish',
	'post_content' => $I->generateGutenberg('wp:planet4-blocks/columns', [
		'columns_block_style' => 'icons',
		'columns_title'       => 'Icons Columns',
		'columns_description' => 'Columns Block description',
		'columns'             => [$column1, $column2]
	])
]);

// Navigate to the newly created page
$I->amOnPage('/' . $slug);

// Check the Tasks style
$I->see('Icons Columns', 'h2');
$I->see('Columns Block description', 'div');
$I->see('Column 1', 'h3 > a');
$I->see('Column 1 description', '.column-wrap p');
$I->seeElement('.attachment-container a img');
$I->see('Explore', '.column-wrap a.call-to-action-link');
