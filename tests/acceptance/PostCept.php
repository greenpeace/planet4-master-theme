<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('check Custom Taxonomy page');

// Create a new post and override the author
$slug = $I->generateRandomSlug();

$postID = $I->havePostInDatabase([
	'post_name'    => $slug,
	'post_status'  => 'publish',
	'post_title'   => 'Test title',
	'post_content' => 'This is a test post',
	'post_type'    => 'post',
	'tax_input'   => [
		'p4-page-type' => [ 'story' ],
		'category'     => [ 'people' ]
	],
	'meta_input'  => [
		'p4_author_override' => 'FooBarAuthor'
	]
]);

// Navigate to the newly created post
$I->amOnPage('/' . $slug);

// Check content
$I->see('FooBarAuthor', 'address');
$I->see('Test title', 'h1.page-header-title');
$I->see('people', 'a.tag-item');
