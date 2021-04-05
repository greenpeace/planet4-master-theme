<?php
use \Codeception\Util\Locator;

$I = new AcceptanceTester($scenario);

$I->wantTo('create and check columns block tasks style');

$slug = $I->generateRandomSlug();

$column1 = [
	'title'       => 'Column 1',
	'description' => 'Column 1 description',
	'attachment'  => 90,
	'cta_link'    => '/act/',
	'cta_text'    => 'Act',
];

$column2 = [
	'title'       => 'Column 2',
	'description' => 'Column 2 description',
	'attachment'  => 92,
	'cta_link'    => '/explore/',
	'cta_text'    => 'Explore',
];

$I->havePageInDatabase([
	'post_name'    => $slug,
	'post_status'  => 'publish',
	'post_content' => $I->generateGutenberg('wp:planet4-blocks/columns', [
		'columns_block_style' => 'tasks',
		'columns_title'       => 'Tasks Columns',
		'columns_description' => 'Columns Block description',
		'columns'             => [$column1, $column2],
	]),
]);

// Navigate to the newly created page.
$I->amOnPage( '/' . $slug );

// Check the Tasks style
$I->see( 'Tasks Columns', 'h2' );
$I->see( 'Columns Block description', 'div' );
$I->see( 'Column 1', '.step-info h5' );
$I->see( 'Column 1 description', '.step-info p' );
$I->seeElement( '.steps-action img, .step-info img' );
$I->see( 'Explore', '.steps-action a.btn-secondary, .step-info a.btn-secondary' );
